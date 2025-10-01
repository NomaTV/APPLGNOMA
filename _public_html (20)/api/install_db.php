<?php
/**
 * =================================================================
 * DB_INSTALLER.PHP - Instalador e Reset do Banco de Dados NomaTV v4.4
 * =================================================================
 *
 * FUNÇÃO: Cria todas as tabelas do sistema NomaTV v4.4 (conforme o Documento da Verdade)
 * e popula-as com dados de exemplo, incluindo a nova lógica de IDs hierárquicos
 * e as tabelas financeiras (faturas e pagamentos).
 * Remove a lógica de criação de tabelas de outros arquivos PHP.
 *
 * USO: Acesse este arquivo via navegador ou execute via linha de comando.
 * Ex: php db_installer.php
 *
 * =================================================================
 */

// Configuração de erro reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Headers para garantir que a saída seja HTML/texto simples no navegador
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html lang='pt-BR'><head><title>NomaTV DB Installer</title><style>body{font-family: monospace; background:#f8fafc; color:#2d3748; padding:20px;} pre{background:#fff; padding:15px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.05); overflow-x:auto;} .success{color:#38a169;} .error{color:#e53e3e;} h2{color:#1a202c;}</style></head><body><h1>NomaTV DB Installer</h1>";
}

echo "<h2>Iniciando instalação/reset do banco de dados...</h2>";

try {
    // Tenta diferentes nomes de banco para desenvolvimento/teste
    $dbFiles = ['db.db', 'db (7).db', 'nomatv.db'];
    $db = null;
    $dbPath = __DIR__ . '/db.db'; // Padrão para criação se não encontrar

    foreach ($dbFiles as $file) {
        if (file_exists(__DIR__ . '/' . $file)) {
            $dbPath = __DIR__ . '/' . $file;
            break;
        }
    }

    // Se o arquivo db.db já existe, podemos tentar removê-lo para um reset limpo
    if (file_exists($dbPath) && php_sapi_name() === 'cli') {
        echo "<p>Removendo banco de dados existente: {$dbPath}</p>";
        unlink($dbPath);
    } elseif (file_exists($dbPath) && php_sapi_name() !== 'cli') {
        echo "<p>Banco de dados existente: {$dbPath}. Para um reset completo, remova o arquivo manualmente ou execute via CLI.</p>";
    }
    
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    echo "<p class='success'>Conexão com o banco de dados estabelecida: {$dbPath}</p>";

    // =============================================
    // 🏗️ CRIAÇÃO DE TODAS AS TABELAS (CONFORME DOCUMENTO DA VERDADE)
    // =============================================
    echo "<h2>Criando tabelas...</h2><pre>";

    $db->exec("
        CREATE TABLE IF NOT EXISTS revendedores (
            id_revendedor VARCHAR(16) PRIMARY KEY, -- Aumentado para 16 para Masters N2+ e Subs
            usuario VARCHAR(50) UNIQUE NOT NULL,
            senha VARCHAR(255) NOT NULL,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(150),
            master VARCHAR(10) NOT NULL DEFAULT 'nao' CHECK (master IN ('admin', 'sim', 'nao')),
            ultra_master_id VARCHAR(16) NULL, -- ID do criador direto (Admin ID ou Master ID)
            plano VARCHAR(50) DEFAULT 'Básico',
            valor_ativo DECIMAL(10,2),
            valor_mensal DECIMAL(10,2),
            limite_ativos INTEGER DEFAULT 100,
            ativo BOOLEAN DEFAULT 1,
            data_vencimento DATE,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_bloqueio TIMESTAMP NULL
            -- FOREIGN KEY (ultra_master_id) REFERENCES revendedores(id_revendedor) -- Adicionar após a criação se não for 'ADMIN'
        )
    ");
    echo "Tabela 'revendedores' criada/verificada.<br>";

    $db->exec("
        CREATE TABLE IF NOT EXISTS provedores (
            id_provedor INTEGER PRIMARY KEY AUTOINCREMENT,
            nome VARCHAR(100) UNIQUE NOT NULL,
            dns VARCHAR(255) NOT NULL,
            tipo VARCHAR(20) DEFAULT 'xtream',
            id_revendedor VARCHAR(16) NOT NULL, -- FK para revendedores.id_revendedor
            ativo BOOLEAN DEFAULT 1,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor) ON DELETE CASCADE
        )
    ");
    echo "Tabela 'provedores' criada/verificada.<br>";

    $db->exec("
        CREATE TABLE IF NOT EXISTS client_ids (
            client_id VARCHAR(36) PRIMARY KEY NOT NULL, -- client_id como PK
            provedor_id INTEGER NOT NULL,
            id_revendedor VARCHAR(16) NOT NULL, -- FK para revendedores.id_revendedor
            usuario VARCHAR(100),
            primeira_conexao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ultima_atividade TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ativo BOOLEAN DEFAULT 1,
            bloqueado BOOLEAN DEFAULT 0,
            ip VARCHAR(45),
            user_agent TEXT,
            
            FOREIGN KEY (provedor_id) REFERENCES provedores(id_provedor) ON DELETE CASCADE,
            FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor) ON DELETE CASCADE
        )
    ");
    echo "Tabela 'client_ids' criada/verificada.<br>";

    $db->exec("
        CREATE TABLE IF NOT EXISTS auditoria (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_revendedor VARCHAR(16), -- FK para revendedores.id_revendedor
            acao VARCHAR(100) NOT NULL,
            detalhes TEXT,
            ip VARCHAR(45),
            user_agent TEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor) ON DELETE SET NULL
        )
    ");
    echo "Tabela 'auditoria' criada/verificada.<br>";

    $db->exec("
        CREATE TABLE IF NOT EXISTS planos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome VARCHAR(100) UNIQUE NOT NULL,
            descricao TEXT,
            tipo_cobranca VARCHAR(10) NOT NULL CHECK (tipo_cobranca IN ('ativo', 'mensal')),
            valor DECIMAL(10,2) NOT NULL,
            limite_ativos INTEGER DEFAULT 100,
            limite_provedores INTEGER DEFAULT 10,
            recursos TEXT,
            ativo BOOLEAN DEFAULT 1,
            ordem INTEGER DEFAULT 0,
            cor VARCHAR(7) DEFAULT '#007bff',
            icone VARCHAR(50) DEFAULT '📦',
            id_revendedor_criador VARCHAR(16) NOT NULL, -- FK para revendedores.id_revendedor
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (id_revendedor_criador) REFERENCES revendedores(id_revendedor)
        )
    ");
    echo "Tabela 'planos' criada/verificada.<br>";

    $db->exec("
        CREATE TABLE IF NOT EXISTS ips_bloqueados (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip VARCHAR(45) UNIQUE NOT NULL,
            status VARCHAR(20) DEFAULT 'permitido' CHECK (status IN ('permitido', 'bloqueado', 'suspeito', 'monitorado')),
            observacoes TEXT,
            pais VARCHAR(2),
            cidade VARCHAR(100),
            provedor VARCHAR(200),
            tentativas_falhas INTEGER DEFAULT 0,
            score_risco INTEGER DEFAULT 0,
            bloqueado_automaticamente BOOLEAN DEFAULT 0,
            id_revendedor_bloqueador VARCHAR(16), -- FK para revendedores.id_revendedor
            motivo_bloqueio VARCHAR(255),
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (id_revendedor_bloqueador) REFERENCES revendedores(id_revendedor)
        )
    ");
    echo "Tabela 'ips_bloqueados' criada/verificada.<br>";

    $db->exec("
        CREATE TABLE IF NOT EXISTS permissoes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            funcionalidade VARCHAR(100) UNIQUE NOT NULL,
            descricao TEXT NOT NULL,
            categoria VARCHAR(50) NOT NULL,
            admin BOOLEAN DEFAULT 1,
            master BOOLEAN DEFAULT 0,
            sub BOOLEAN DEFAULT 0,
            ativo BOOLEAN DEFAULT 1,
            id_revendedor_configurador VARCHAR(16), -- FK para revendedores.id_revendedor
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (id_revendedor_configurador) REFERENCES revendedores(id_revendedor)
        )
    ");
    echo "Tabela 'permissoes' criada/verificada.<br>";

    $db->exec("
        CREATE TABLE IF NOT EXISTS branding (
            id_revendedor VARCHAR(16) PRIMARY KEY, -- id_revendedor como PK (id_branding)
            nome_arquivo_logo VARCHAR(255) NOT NULL,
            caminho_arquivo VARCHAR(255) NOT NULL,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor) ON DELETE CASCADE
        )
    ");
    echo "Tabela 'branding' criada/verificada.<br>";

    $db->exec("
        CREATE TABLE IF NOT EXISTS faturas (
            id_fatura INTEGER PRIMARY KEY AUTOINCREMENT,
            id_revendedor VARCHAR(16) NOT NULL, -- FK para revendedores.id_revendedor
            data_emissao DATE DEFAULT CURRENT_DATE,
            data_vencimento DATE NOT NULL,
            valor_total DECIMAL(10,2) NOT NULL,
            tipo_cobranca VARCHAR(20) NOT NULL CHECK (tipo_cobranca IN ('por_ativo', 'mensal', 'manual')),
            status VARCHAR(20) NOT NULL CHECK (status IN ('pendente', 'paga', 'vencida', 'parcialmente_paga', 'cancelada')),
            ativos_no_periodo INTEGER DEFAULT 0,
            observacoes TEXT,
            id_revendedor_criador_fatura VARCHAR(16), -- FK para revendedores.id_revendedor
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor) ON DELETE CASCADE,
            FOREIGN KEY (id_revendedor_criador_fatura) REFERENCES revendedores(id_revendedor)
        )
    ");
    echo "Tabela 'faturas' criada/verificada.<br>";

    $db->exec("
        CREATE TABLE IF NOT EXISTS pagamentos (
            id_pagamento INTEGER PRIMARY KEY AUTOINCREMENT,
            id_fatura INTEGER, -- FK para faturas.id_fatura (pode ser NULL para pagamentos avulsos)
            id_revendedor VARCHAR(16) NOT NULL, -- FK para revendedores.id_revendedor
            valor_pago DECIMAL(10,2) NOT NULL,
            data_pagamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            metodo_pagamento VARCHAR(50),
            observacoes TEXT,
            id_revendedor_registrador VARCHAR(16), -- FK para revendedores.id_revendedor
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (id_fatura) REFERENCES faturas(id_fatura) ON DELETE SET NULL,
            FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor) ON DELETE CASCADE,
            FOREIGN KEY (id_revendedor_registrador) REFERENCES revendedores(id_revendedor)
        )
    ");
    echo "Tabela 'pagamentos' criada/verificada.<br>";

    echo "</pre><h2>Populando tabelas com dados de exemplo...</h2><pre>";

    // =============================================
    // 🎯 INSERIR DADOS DE EXEMPLO (CONFORME NOVA LÓGICA DE IDS)
    // =============================================

    // Admin (Raiz da Rede)
    $adminId = '10000000';
    $db->exec("INSERT OR IGNORE INTO revendedores (id_revendedor, usuario, senha, nome, email, master, ultra_master_id, ativo)
               VALUES ('{$adminId}', 'admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'Super Admin Global', 'admin@nomatv.com', 'admin', 'ADMIN', 1)");
    echo "Admin criado: {$adminId}<br>";

    // Master Nível 1 (Criado por Admin)
    $master1Id = $adminId . '123'; // Exemplo: 10000000123
    $db->exec("INSERT OR IGNORE INTO revendedores (id_revendedor, usuario, senha, nome, email, master, ultra_master_id, plano, valor_ativo, limite_ativos, ativo, data_vencimento)
               VALUES ('{$master1Id}', 'master_alpha', '" . password_hash('123456', PASSWORD_DEFAULT) . "', 'Master Alpha', 'master.alpha@nomatv.com', 'sim', '{$adminId}', 'Premium', 2.50, 500, 1, DATE('now', '+30 days'))");
    echo "Master N1 criado: {$master1Id}<br>";

    // Master Nível 2 (Criado por Master N1)
    $master2Id = $master1Id . '456'; // Exemplo: 10000000123456
    $db->exec("INSERT OR IGNORE INTO revendedores (id_revendedor, usuario, senha, nome, email, master, ultra_master_id, plano, valor_mensal, limite_ativos, ativo, data_vencimento)
               VALUES ('{$master2Id}', 'master_beta', '" . password_hash('123456', PASSWORD_DEFAULT) . "', 'Master Beta', 'master.beta@nomatv.com', 'sim', '{$master1Id}', 'Gold', 150.00, 200, 1, DATE('now', '+15 days'))");
    echo "Master N2 criado: {$master2Id}<br>";
    
    // Sub-Revendedor (Criado por Master N1)
    $sub1Id = $master1Id . '78'; // Exemplo: 1000000012378
    $db->exec("INSERT OR IGNORE INTO revendedores (id_revendedor, usuario, senha, nome, email, master, ultra_master_id, plano, valor_ativo, limite_ativos, ativo, data_vencimento)
               VALUES ('{$sub1Id}', 'sub_gama', '" . password_hash('123456', PASSWORD_DEFAULT) . "', 'Sub Gama', 'sub.gama@nomatv.com', 'nao', '{$master1Id}', 'Basico', 3.00, 50, 1, DATE('now', '-5 days'))"); // Vencido
    echo "Sub-Revendedor (vencido) criado: {$sub1Id}<br>";

    // Sub-Revendedor (Criado por Master N2)
    $sub2Id = $master2Id . '90'; // Exemplo: 1000000012345690
    $db->exec("INSERT OR IGNORE INTO revendedores (id_revendedor, usuario, senha, nome, email, master, ultra_master_id, plano, valor_mensal, limite_ativos, ativo, data_vencimento)
               VALUES ('{$sub2Id}', 'sub_delta', '" . password_hash('123456', PASSWORD_DEFAULT) . "', 'Sub Delta', 'sub.delta@nomatv.com', 'nao', '{$master2Id}', 'Basico', 30.00, 20, 1, DATE('now', '+10 days'))");
    echo "Sub-Revendedor criado: {$sub2Id}<br>";


    // Provedores de Exemplo
    $db->exec("INSERT OR IGNORE INTO provedores (id_provedor, nome, dns, tipo, id_revendedor, ativo)
               VALUES (1, 'Provedor Global', 'http://global.server.com:8080', 'xtream', '{$adminId}', 1)");
    $db->exec("INSERT OR IGNORE INTO provedores (id_provedor, nome, dns, tipo, id_revendedor, ativo)
               VALUES (2, 'Provedor Alpha', 'http://alpha.server.com:8080', 'xtream', '{$master1Id}', 1)");
    $db->exec("INSERT OR IGNORE INTO provedores (id_provedor, nome, dns, tipo, id_revendedor, ativo)
               VALUES (3, 'Provedor Beta', 'http://beta.server.com:8080', 'm3u', '{$master2Id}', 1)");
    $db->exec("INSERT OR IGNORE INTO provedores (id_provedor, nome, dns, tipo, id_revendedor, ativo)
               VALUES (4, 'Provedor Gama', 'http://gama.server.com:8080', 'xtream', '{$sub1Id}', 1)");
    echo "Provedores de exemplo criados.<br>";

    // Client IDs de Exemplo
    $db->exec("INSERT OR IGNORE INTO client_ids (client_id, provedor_id, id_revendedor, usuario, ip, user_agent, ativo, bloqueado, primeira_conexao, ultima_atividade)
               VALUES ('client-master1-001', 2, '{$master1Id}', 'cliente_m1_001', '192.168.1.10', 'Android', 1, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
    $db->exec("INSERT OR IGNORE INTO client_ids (client_id, provedor_id, id_revendedor, usuario, ip, user_agent, ativo, bloqueado, primeira_conexao, ultima_atividade)
               VALUES ('client-master1-002', 2, '{$master1Id}', 'cliente_m1_002', '192.168.1.11', 'SmartTV', 0, 0, CURRENT_TIMESTAMP, DATE('now', '-35 days'))");
    $db->exec("INSERT OR IGNORE INTO client_ids (client_id, provedor_id, id_revendedor, usuario, ip, user_agent, ativo, bloqueado, primeira_conexao, ultima_atividade)
               VALUES ('client-sub1-001', 4, '{$sub1Id}', 'cliente_s1_001', '192.168.1.20', 'iOS', 1, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
    $db->exec("INSERT OR IGNORE INTO client_ids (client_id, provedor_id, id_revendedor, usuario, ip, user_agent, ativo, bloqueado, primeira_conexao, ultima_atividade)
               VALUES ('client-sub1-002', 4, '{$sub1Id}', 'cliente_s1_002', '192.168.1.21', 'Windows', 0, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)"); // Bloqueado
    $db->exec("INSERT OR IGNORE INTO client_ids (client_id, provedor_id, id_revendedor, usuario, ip, user_agent, ativo, bloqueado, primeira_conexao, ultima_atividade)
               VALUES ('client-master2-001', 3, '{$master2Id}', 'cliente_m2_001', '192.168.1.30', 'AndroidTV', 1, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
    echo "Client IDs de exemplo criados.<br>";

    // Faturas de Exemplo
    $db->exec("INSERT OR IGNORE INTO faturas (id_fatura, id_revendedor, data_emissao, data_vencimento, valor_total, tipo_cobranca, status, ativos_no_periodo, id_revendedor_criador_fatura)
               VALUES (1, '{$master1Id}', DATE('now', '-40 days'), DATE('now', '-10 days'), 100.00, 'mensal', 'vencida', 0, '{$adminId}')"); // Fatura vencida
    $db->exec("INSERT OR IGNORE INTO faturas (id_fatura, id_revendedor, data_emissao, data_vencimento, valor_total, tipo_cobranca, status, ativos_no_periodo, id_revendedor_criador_fatura)
               VALUES (2, '{$sub1Id}', DATE('now', '-10 days'), DATE('now', '+20 days'), 15.00, 'por_ativo', 'pendente', 5, '{$master1Id}')"); // Fatura pendente
    $db->exec("INSERT OR IGNORE INTO faturas (id_fatura, id_revendedor, data_emissao, data_vencimento, valor_total, tipo_cobranca, status, ativos_no_periodo, id_revendedor_criador_fatura)
               VALUES (3, '{$master2Id}', DATE('now', '-60 days'), DATE('now', '-30 days'), 200.00, 'mensal', 'paga', 0, '{$adminId}')"); // Fatura paga
    echo "Faturas de exemplo criadas.<br>";

    // Pagamentos de Exemplo
    $db->exec("INSERT OR IGNORE INTO pagamentos (id_pagamento, id_fatura, id_revendedor, valor_pago, data_pagamento, metodo_pagamento, id_revendedor_registrador)
               VALUES (1, 3, '{$master2Id}', 200.00, DATE('now', '-25 days'), 'pix', '{$adminId}')");
    echo "Pagamentos de exemplo criados.<br>";

    // Permissões Padrão (se não existirem)
    $stmt = $db->query("SELECT COUNT(*) FROM permissoes");
    if ($stmt->fetchColumn() == 0) {
        $defaultPermissoes = [
            ['dashboard', 'Visualizar o dashboard principal', 'Geral', 1, 1, 1, $adminId],
            ['revendedores_listar', 'Listar revendedores', 'Gestão de Revendedores', 1, 1, 0, $adminId],
            ['revendedores_criar', 'Criar novos revendedores', 'Gestão de Revendedores', 1, 1, 0, $adminId],
            ['revendedores_editar', 'Editar revendedores existentes', 'Gestão de Revendedores', 1, 1, 0, $adminId],
            ['revendedores_deletar', 'Excluir revendedores', 'Gestão de Revendedores', 1, 0, 0, $adminId],
            ['revendedores_toggle_status', 'Ativar/Bloquear revendedores', 'Gestão de Revendedores', 1, 1, 0, $adminId],
            ['provedores_listar', 'Listar provedores', 'Gestão de Provedores', 1, 1, 0, $adminId],
            ['provedores_criar', 'Criar novos provedores', 'Gestão de Provedores', 1, 1, 0, $adminId],
            ['provedores_editar', 'Editar provedores existentes', 'Gestão de Provedores', 1, 1, 0, $adminId],
            ['provedores_deletar', 'Excluir provedores', 'Gestão de Provedores', 1, 0, 0, $adminId],
            ['ativos_listar', 'Listar ativos (Client IDs)', 'Gestão de Ativos', 1, 1, 1, $adminId],
            ['ativos_criar_editar', 'Adicionar/Editar ativos (Client IDs)', 'Gestão de Ativos', 1, 1, 1, $adminId],
            ['ativos_toggle_status', 'Ativar/Bloquear ativos (Client IDs)', 'Gestão de Ativos', 1, 1, 1, $adminId],
            ['ativos_deletar', 'Excluir ativos (Client IDs)', 'Gestão de Ativos', 1, 0, 0, $adminId],
            ['ativos_exportar', 'Exportar dados de ativos', 'Gestão de Ativos', 1, 1, 0, $adminId],
            ['logs_listar', 'Visualizar logs de atividade', 'Sistema', 1, 0, 0, $adminId],
            ['relatorios_gerar', 'Gerar relatórios do sistema', 'Relatórios', 1, 1, 0, $adminId],
            ['relatorios_exportar', 'Exportar relatórios', 'Relatórios', 1, 1, 0, $adminId],
            ['permissoes_gerenciar', 'Gerenciar permissões de acesso', 'Sistema', 1, 0, 0, $adminId], // Apenas Admin
            ['ips_gerenciar', 'Gerenciar controle de IPs', 'Segurança', 1, 0, 0, $adminId],
            ['financeiro_dashboard', 'Visualizar dashboard financeiro', 'Financeiro', 1, 0, 0, $adminId],
            ['financeiro_processar_cobranca', 'Processar cobranças', 'Financeiro', 1, 0, 0, $adminId],
            ['financeiro_marcar_pagamento', 'Marcar pagamento recebido', 'Financeiro', 1, 0, 0, $adminId],
            ['financeiro_bloquear_vencidos', 'Bloquear revendedores vencidos', 'Financeiro', 1, 0, 0, $adminId],
            ['financeiro_atualizar_vencimento', 'Atualizar vencimento de revendedor', 'Financeiro', 1, 0, 0, $adminId],
            ['planos_listar', 'Listar planos e pacotes', 'Gestão de Planos', 1, 0, 0, $adminId],
            ['planos_criar_editar', 'Criar/Editar planos e pacotes', 'Gestão de Planos', 1, 0, 0, $adminId],
            ['planos_deletar', 'Excluir planos e pacotes', 'Gestão de Planos', 1, 0, 0, $adminId],
            ['seguranca_alterar_senha_admin', 'Alterar senha de administrador', 'Segurança', 1, 0, 0, $adminId],
            ['configuracoes_gerenciar', 'Gerenciar configurações gerais', 'Sistema', 1, 0, 0, $adminId],
            ['backup_restaurar', 'Realizar backup e restauração', 'Sistema', 1, 0, 0, $adminId]
        ];

        $stmt = $db->prepare("
            INSERT INTO permissoes (funcionalidade, descricao, categoria, admin, master, sub, id_revendedor_configurador)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($defaultPermissoes as $p) {
            $stmt->execute([$p[0], $p[1], $p[2], (int)$p[3], (int)$p[4], (int)$p[5], $p[6]]);
        }
        echo "Permissões padrão criadas.<br>";
    }

    // Branding padrão (logo do Admin)
    $stmt = $db->query("SELECT COUNT(*) FROM branding WHERE id_revendedor = '{$adminId}'");
    if ($stmt->fetchColumn() == 0) {
        $db->exec("INSERT OR IGNORE INTO branding (id_revendedor, nome_arquivo_logo, caminho_arquivo)
                   VALUES ('{$adminId}', '{$adminId}.png', '/api/logos/{$adminId}.png')");
        echo "Branding padrão do Admin criado.<br>";
    }

    echo "</pre><h2 class='success'>Instalação/reset do banco de dados concluído com sucesso!</h2>";

} catch (PDOException $e) {
    echo "</pre><h2 class='error'>Erro CRÍTICO na instalação/reset do banco de dados:</h2>";
    echo "<pre class='error'>" . $e->getMessage() . "</pre>";
    error_log("NomaTV DB Installer Erro: " . $e->getMessage());
}

if (php_sapi_name() !== 'cli') {
    echo "</body></html>";
}
?>
