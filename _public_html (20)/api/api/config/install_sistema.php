<?php
/**
 * =================================================================
 * SCRIPT DE INSTALAÇÃO E VERIFICAÇÃO COMPLETA - NomaTV v4.4
 * =================================================================
 * ARQUIVO: /install.php (colocar na raiz do projeto)
 * FUNÇÃO: Verifica e instala/corrige toda a estrutura do sistema
 * =================================================================
 */

header('Content-Type: text/html; charset=utf-8');

// Configurações
$dbPath = __DIR__ . '/api/db.db';
$apiPath = __DIR__ . '/api/';

echo '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NomaTV v4.4 - Instalação e Verificação</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        .section { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .log { max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>';

echo '<h1>🚀 NomaTV v4.4 - Instalação e Verificação</h1>';

$resultados = [];

// 1. Verificar estrutura de diretórios
echo '<div class="section"><h2>📁 Verificação de Diretórios</h2>';
$diretorios = [
    '/api/',
    '/api/config/',
    '/api/helpers/',
    '/api/uploads/',
    '/api/uploads/logos/',
    '/logs/'
];

foreach ($diretorios as $dir) {
    $path = __DIR__ . $dir;
    if (is_dir($path)) {
        echo "<p class='status-ok'>✅ $dir - OK</p>";
        $resultados['diretorios'][$dir] = 'OK';
    } else {
        if (mkdir($path, 0755, true)) {
            echo "<p class='status-warning'>⚠️ $dir - CRIADO</p>";
            $resultados['diretorios'][$dir] = 'CRIADO';
        } else {
            echo "<p class='status-error'>❌ $dir - ERRO AO CRIAR</p>";
            $resultados['diretorios'][$dir] = 'ERRO';
        }
    }
}
echo '</div>';

// 2. Verificar arquivos essenciais
echo '<div class="section"><h2>📄 Verificação de Arquivos Essenciais</h2>';
$arquivos = [
    '/api/config/database_sqlite.php',
    '/api/helpers/auth_helper.php',
    '/api/revendedores.php',
    '/api/auth.php',
    '/admin.html',
    '/api.js'
];

foreach ($arquivos as $arquivo) {
    $path = __DIR__ . $arquivo;
    if (file_exists($path)) {
        echo "<p class='status-ok'>✅ $arquivo - OK</p>";
        $resultados['arquivos'][$arquivo] = 'OK';
    } else {
        echo "<p class='status-error'>❌ $arquivo - NÃO ENCONTRADO</p>";
        $resultados['arquivos'][$arquivo] = 'FALTANDO';
    }
}
echo '</div>';

// 3. Verificar e criar banco de dados
echo '<div class="section"><h2>🗄️ Verificação do Banco de Dados</h2>';
try {
    $db = new PDO("sqlite:$dbPath");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='status-ok'>✅ Conexão SQLite - OK</p>";
    
    // Verificar estrutura da tabela revendedores
    $estruturaCorrigida = verificarECorrigirTabelaRevendedores($db);
    if ($estruturaCorrigida['modificado']) {
        echo "<p class='status-warning'>⚠️ Tabela revendedores - CORRIGIDA</p>";
        echo "<pre class='log'>" . implode("\n", $estruturaCorrigida['log']) . "</pre>";
    } else {
        echo "<p class='status-ok'>✅ Tabela revendedores - OK</p>";
    }
    
    $resultados['banco'] = 'OK';
    
} catch (Exception $e) {
    echo "<p class='status-error'>❌ Erro no banco: " . $e->getMessage() . "</p>";
    $resultados['banco'] = 'ERRO: ' . $e->getMessage();
}
echo '</div>';

// 4. Testar endpoints críticos
echo '<div class="section"><h2>🌐 Teste de Endpoints</h2>';
$endpoints = [
    '/api/auth.php' => 'POST',
    '/api/revendedores.php' => 'GET',
    '/api/stats.php' => 'GET'
];

foreach ($endpoints as $endpoint => $method) {
    $url = "http://" . $_SERVER['HTTP_HOST'] . $endpoint;
    echo "<p>🔍 Testando: $method $endpoint</p>";
    
    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => "Content-type: application/json\r\n",
            'content' => $method === 'POST' ? '{"action":"test"}' : '',
            'timeout' => 5
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    if ($response !== false) {
        $json = @json_decode($response, true);
        if ($json !== null) {
            echo "<p class='status-ok'>✅ $endpoint - RESPONDE JSON</p>";
            $resultados['endpoints'][$endpoint] = 'OK';
        } else {
            echo "<p class='status-warning'>⚠️ $endpoint - RESPONDE (não JSON)</p>";
            $resultados['endpoints'][$endpoint] = 'PARCIAL';
        }
    } else {
        echo "<p class='status-error'>❌ $endpoint - NÃO RESPONDE</p>";
        $resultados['endpoints'][$endpoint] = 'ERRO';
    }
}
echo '</div>';

// 5. Verificar permissões
echo '<div class="section"><h2>🔐 Verificação de Permissões</h2>';
$paths = [
    __DIR__ . '/api/uploads/logos/',
    __DIR__ . '/logs/',
    $dbPath
];

foreach ($paths as $path) {
    if (is_writable($path)) {
        echo "<p class='status-ok'>✅ " . basename($path) . " - GRAVÁVEL</p>";
        $resultados['permissoes'][basename($path)] = 'OK';
    } else {
        echo "<p class='status-error'>❌ " . basename($path) . " - SEM PERMISSÃO DE ESCRITA</p>";
        $resultados['permissoes'][basename($path)] = 'ERRO';
    }
}
echo '</div>';

// 6. Relatório final
echo '<div class="section"><h2>📊 Relatório Final</h2>';
$totalProblemas = 0;
foreach ($resultados as $categoria => $itens) {
    echo "<h3>" . ucfirst($categoria) . ":</h3><ul>";
    foreach ($itens as $item => $status) {
        $icon = (strpos($status, 'ERRO') !== false) ? '❌' : 
                (strpos($status, 'CRIADO') !== false || strpos($status, 'CORRIGIDA') !== false ? '⚠️' : '✅');
        echo "<li>$icon $item: $status</li>";
        if (strpos($status, 'ERRO') !== false) $totalProblemas++;
    }
    echo "</ul>";
}

if ($totalProblemas == 0) {
    echo "<h3 class='status-ok'>🎉 SISTEMA PRONTO PARA USO!</h3>";
    echo "<p>Credenciais padrão:</p>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> usuário 'admin', senha 'admin123'</li>";
    echo "<li><strong>Revendedor:</strong> usuário 'joao', senha '123456'</li>";
    echo "<li><strong>Sub-Revendedor:</strong> usuário 'pedro', senha '123456'</li>";
    echo "</ul>";
    echo "<p><a href='/admin.html'>🚀 Acessar Painel Admin</a></p>";
} else {
    echo "<h3 class='status-error'>⚠️ ENCONTRADOS $totalProblemas PROBLEMA(S)</h3>";
    echo "<p>Corrija os problemas acima antes de usar o sistema.</p>";
}
echo '</div>';

echo '</body></html>';

/**
 * Função para verificar e corrigir a tabela revendedores
 */
function verificarECorrigirTabelaRevendedores(PDO $db): array {
    $resultado = ['modificado' => false, 'log' => []];
    
    // Estrutura esperada
    $estruturaEsperada = [
        'id_revendedor' => 'INTEGER',
        'usuario' => 'VARCHAR(50)',
        'senha' => 'VARCHAR(255)',
        'nome' => 'VARCHAR(100)',
        'email' => 'VARCHAR(150)',
        'master' => 'VARCHAR(10)',
        'master_de' => 'INTEGER',
        'plano' => 'VARCHAR(50)',
        'valor_ativo' => 'DECIMAL(10,2)',
        'valor_mensal' => 'DECIMAL(10,2)',
        'limite_ativos' => 'INTEGER',
        'ativo' => 'BOOLEAN',
        'data_vencimento' => 'DATE',
        'data_bloqueio' => 'TIMESTAMP',
        'criado_em' => 'TIMESTAMP',
        'atualizado_em' => 'TIMESTAMP'
    ];
    
    try {
        // Verificar se tabela existe
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='revendedores'");
        $tabelaExiste = $stmt->fetch() !== false;
        
        if (!$tabelaExiste) {
            // Criar tabela
            criarTabelaRevendedores($db);
            $resultado['modificado'] = true;
            $resultado['log'][] = "Tabela 'revendedores' criada do zero";
            
            // Criar registros padrão
            criarRegistrosPadrao($db);
            $resultado['log'][] = "Registros padrão criados";
            
            return $resultado;
        }
        
        // Verificar colunas existentes
        $stmt = $db->query("PRAGMA table_info(revendedores)");
        $colunasExistentes = [];
        while ($coluna = $stmt->fetch()) {
            $colunasExistentes[$coluna['name']] = $coluna['type'];
        }
        
        // Verificar se faltam colunas
        $colunasFaltando = array_diff_key($estruturaEsperada, $colunasExistentes);
        
        if (!empty($colunasFaltando)) {
            // Fazer backup e recriar
            $backup = $db->query("SELECT * FROM revendedores")->fetchAll(PDO::FETCH_ASSOC);
            
            $db->exec("DROP TABLE revendedores");
            criarTabelaRevendedores($db);
            
            // Restaurar dados
            foreach ($backup as $registro) {
                restaurarRegistro($db, $registro);
            }
            
            $resultado['modificado'] = true;
            $resultado['log'][] = "Tabela recriada com " . count($backup) . " registros preservados";
            $resultado['log'][] = "Colunas adicionadas: " . implode(', ', array_keys($colunasFaltando));
        }
        
        // Garantir registros padrão
        garantirRegistrosPadrao($db);
        
    } catch (Exception $e) {
        $resultado['log'][] = "ERRO: " . $e->getMessage();
    }
    
    return $resultado;
}

function criarTabelaRevendedores(PDO $db): void {
    $sql = "CREATE TABLE revendedores (
        id_revendedor INTEGER PRIMARY KEY,
        usuario VARCHAR(50) UNIQUE NOT NULL,
        senha VARCHAR(255) NOT NULL,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(150),
        master VARCHAR(10) NOT NULL DEFAULT 'nao',
        master_de INTEGER,
        plano VARCHAR(50) DEFAULT 'Básico',
        valor_ativo DECIMAL(10,2),
        valor_mensal DECIMAL(10,2),
        limite_ativos INTEGER DEFAULT 100,
        ativo BOOLEAN DEFAULT 1,
        data_vencimento DATE,
        data_bloqueio TIMESTAMP NULL,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (master_de) REFERENCES revendedores(id_revendedor),
        CHECK (master IN ('admin', 'sim', 'nao'))
    )";
    $db->exec($sql);
}

function criarRegistrosPadrao(PDO $db): void {
    $registros = [
        [1000, 'admin', password_hash('admin123', PASSWORD_DEFAULT), 'Super Administrador', 'admin@nomatv.com', 'admin', null, 'Sistema', null, null, 999, 1, null],
        [1234, 'joao', password_hash('123456', PASSWORD_DEFAULT), 'João Silva', 'joao@teste.com', 'sim', null, 'Premium', 2.50, null, 200, 1, '2025-12-31'],
        [5678, 'pedro', password_hash('123456', PASSWORD_DEFAULT), 'Pedro Santos', 'pedro@teste.com', 'nao', 1234, 'Básico', null, 50.00, 100, 1, '2025-12-31']
    ];
    
    $sql = "INSERT OR REPLACE INTO revendedores (id_revendedor, usuario, senha, nome, email, master, master_de, plano, valor_ativo, valor_mensal, limite_ativos, ativo, data_vencimento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    
    foreach ($registros as $registro) {
        $stmt->execute($registro);
    }
}

function garantirRegistrosPadrao(PDO $db): void {
    $stmt = $db->query("SELECT COUNT(*) FROM revendedores WHERE master = 'admin'");
    if ($stmt->fetchColumn() == 0) {
        criarRegistrosPadrao($db);
    }
}

function restaurarRegistro(PDO $db, array $registro): void {
    $colunas = array_keys($registro);
    $placeholders = str_repeat('?,', count($colunas) - 1) . '?';
    $sql = "INSERT INTO revendedores (" . implode(',', $colunas) . ") VALUES ($placeholders)";
    $stmt = $db->prepare($sql);
    $stmt->execute(array_values($registro));
}
?>