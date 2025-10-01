<?php
/**
 * NomaTV v4.5 - Instalador Web Completo
 * Interface visual para instalação via navegador
 * * Versão: 4.5 Final - Lógica de parent_id
 * Data: 01/08/2025
 * Correção: Adição da coluna parent_id e dados de teste hierárquicos
 */

// Se for uma requisição AJAX (instalação)
if (isset($_POST['action']) && $_POST['action'] === 'install') {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    
    // Configuração do banco
    $dbFile = __DIR__ . '/nomatv_v45.db';
    
    try {
        $db = new PDO("sqlite:$dbFile");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->exec('PRAGMA foreign_keys = ON');
        
    } catch (PDOException $e) {
        http_response_code(500);
        exit(json_encode(['status' => 'error', 'message' => 'Falha na conexão: ' . $e->getMessage()]));
    }
    
    // Verificar se já foi instalado
    function verificarInstalacao($db) {
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM revendedores");
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    if (verificarInstalacao($db)) {
        exit(json_encode(['status' => 'warning', 'message' => 'Sistema já instalado! Para reinstalar, delete o arquivo nomatv_v45.db']));
    }
    
    // SQLs das 10 tabelas (CONSTRAINT CORRIGIDA E parent_id ADICIONADO)
    $tabelas = [
        'revendedores' => "CREATE TABLE revendedores (
            id_revendedor INTEGER PRIMARY KEY,
            usuario VARCHAR(50) UNIQUE NOT NULL,
            senha VARCHAR(255) NOT NULL,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(150),
            master VARCHAR(10) NOT NULL CHECK (master IN ('admin', 'sim', 'nao')),
            parent_id INTEGER DEFAULT NULL,
            plano VARCHAR(50) DEFAULT 'Básico',
            valor_ativo DECIMAL(10,2),
            valor_mensal DECIMAL(10,2),
            limite_ativos INTEGER DEFAULT 100,
            ativo BOOLEAN DEFAULT 1,
            data_vencimento DATE,
            data_bloqueio DATE,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (parent_id) REFERENCES revendedores(id_revendedor),
            CHECK (
                master = 'admin' OR 
                (valor_ativo IS NOT NULL AND valor_mensal IS NULL) OR 
                (valor_ativo IS NULL AND valor_mensal IS NOT NULL)
            )
        );",
        'provedores' => "CREATE TABLE provedores (
            id_provedor INTEGER PRIMARY KEY AUTOINCREMENT,
            nome VARCHAR(100) NOT NULL,
            dns VARCHAR(255) UNIQUE NOT NULL,
            tipo VARCHAR(20) DEFAULT 'xtream',
            usuario VARCHAR(100) NOT NULL,
            senha VARCHAR(255) NOT NULL,
            id_revendedor INTEGER NOT NULL,
            ativo BOOLEAN DEFAULT 1,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor) ON DELETE CASCADE
        );",
        'client_ids' => "CREATE TABLE client_ids (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            client_id VARCHAR(50) UNIQUE NOT NULL,
            usuario VARCHAR(100) NOT NULL,
            senha VARCHAR(100) NOT NULL,
            ip VARCHAR(45),
            data_expiracao DATE,
            id_revendedor INTEGER NOT NULL,
            provedor_id INTEGER NOT NULL,
            ativo BOOLEAN DEFAULT 1,
            bloqueado BOOLEAN DEFAULT 0,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor),
            FOREIGN KEY (provedor_id) REFERENCES provedores(id_provedor)
        );",
        'planos' => "CREATE TABLE planos (
            id_plano INTEGER PRIMARY KEY AUTOINCREMENT,
            nome VARCHAR(100) UNIQUE NOT NULL,
            descricao TEXT,
            limite_ativos INTEGER DEFAULT 100,
            valor_base DECIMAL(10,2),
            ativo BOOLEAN DEFAULT 1,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );",
        'faturas' => "CREATE TABLE faturas (
            id_fatura INTEGER PRIMARY KEY AUTOINCREMENT,
            id_revendedor INTEGER NOT NULL,
            valor_total DECIMAL(10,2) NOT NULL,
            mes_referencia VARCHAR(7) NOT NULL,
            data_vencimento DATE NOT NULL,
            status VARCHAR(20) DEFAULT 'pendente',
            observacoes TEXT,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor)
        );",
        'pagamentos' => "CREATE TABLE pagamentos (
            id_pagamento INTEGER PRIMARY KEY AUTOINCREMENT,
            id_fatura INTEGER NOT NULL,
            id_revendedor INTEGER NOT NULL,
            valor_pago DECIMAL(10,2) NOT NULL,
            data_pagamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            metodo_pagamento VARCHAR(50),
            comprovante TEXT,
            observacoes TEXT,
            FOREIGN KEY (id_fatura) REFERENCES faturas(id_fatura),
            FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor)
        );",
        'branding' => "CREATE TABLE branding (
            id_branding INTEGER PRIMARY KEY AUTOINCREMENT,
            id_revendedor INTEGER UNIQUE NOT NULL,
            logo_filename VARCHAR(255),
            logo_size INTEGER,
            logo_width INTEGER,
            logo_height INTEGER,
            ativo BOOLEAN DEFAULT 1,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor)
        );",
        'sessoes_ativas' => "CREATE TABLE sessoes_ativas (
            id_sessao INTEGER PRIMARY KEY AUTOINCREMENT,
            session_id VARCHAR(100) UNIQUE NOT NULL,
            id_revendedor INTEGER NOT NULL,
            dados_sessao TEXT,
            expires_at TIMESTAMP NOT NULL,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor)
        );",
        'logs_auditoria' => "CREATE TABLE logs_auditoria (
            id_log INTEGER PRIMARY KEY AUTOINCREMENT,
            id_revendedor INTEGER,
            acao VARCHAR(100) NOT NULL,
            detalhes TEXT,
            ip_origem VARCHAR(45),
            user_agent TEXT,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor)
        );",
        'configuracoes_sistema' => "CREATE TABLE configuracoes_sistema (
            id_config INTEGER PRIMARY KEY AUTOINCREMENT,
            chave VARCHAR(100) UNIQUE NOT NULL,
            valor TEXT,
            descricao TEXT,
            tipo VARCHAR(20) DEFAULT 'string',
            atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );"
    ];
    
    // Adicionar índice para a coluna parent_id
    $tabelas['index_parent_id'] = "CREATE INDEX idx_parent_id ON revendedores(parent_id);";

    try {
        $db->beginTransaction();
        
        // Criar tabelas e índice
        foreach ($tabelas as $nome => $sql) {
            $db->exec($sql);
        }
        
        // Inserir dados padrão (AGORA COM parent_id)
        $stmt = $db->prepare("INSERT INTO revendedores (id_revendedor, usuario, senha, nome, email, master, parent_id, plano, valor_ativo, valor_mensal, limite_ativos, ativo, data_vencimento, data_bloqueio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $usuarios = [
            // ADMIN: master='admin' (parent_id = NULL)
            [10000000, 'admin', password_hash('admin123', PASSWORD_DEFAULT), 'Super Admin', 'admin@nomatv.com', 'admin', null, 'Sistema', null, null, 999, 1, null, null],
            
            // REVENDEDOR MASTER: (parent_id = 10000000)
            [12345678, 'joao', password_hash('123456', PASSWORD_DEFAULT), 'João Silva', 'joao@teste.com', 'sim', 10000000, 'Premium', 2.50, null, 200, 1, '2025-12-31', null],
            
            // SUB-REVENDEDORES: (parent_id = 12345678)
            [56781001, 'pedro', password_hash('123456', PASSWORD_DEFAULT), 'Pedro Santos', 'pedro@teste.com', 'nao', 12345678, 'Básico', null, 50.00, 100, 1, '2025-12-31', null],
            [56781002, 'ana', password_hash('123456', PASSWORD_DEFAULT), 'Ana Costa', 'ana@teste.com', 'nao', 12345678, 'Básico', null, 45.00, 80, 1, '2025-12-31', null],

            // NOVO REVENDEDOR MASTER: (parent_id = 10000000)
            [87654321, 'maria', password_hash('123456', PASSWORD_DEFAULT), 'Maria Oliveira', 'maria@teste.com', 'sim', 10000000, 'Premium', 3.00, null, 150, 1, '2025-12-31', null]
        ];
        
        foreach ($usuarios as $user) {
            $stmt->execute($user);
        }
        
        // Inserir planos
        $stmt = $db->prepare("INSERT INTO planos (nome, descricao, limite_ativos, valor_base, ativo) VALUES (?, ?, ?, ?, ?)");
        $planos = [
            ['Básico', 'Plano básico para iniciantes', 100, 50.00, 1],
            ['Premium', 'Plano premium com mais recursos', 200, 2.50, 1],
            ['Enterprise', 'Plano empresarial completo', 500, 2.00, 1]
        ];
        foreach ($planos as $plano) {
            $stmt->execute($plano);
        }
        
        // Inserir provedores
        $stmt = $db->prepare("INSERT INTO provedores (nome, dns, tipo, usuario, senha, id_revendedor, ativo) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $provedores = [
            ['Provedor A', 'http://provedora.com', 'xtream', 'user_a', 'pass_a', 12345678, 1],
            ['Provedor B', 'http://provedorb.com', 'xtream', 'user_b', 'pass_b', 12345678, 1],
            ['Provedor C', 'http://provedorc.com', 'm3u', 'user_c', 'pass_c', 56781001, 1],
            ['Provedor D', 'http://provedord.com', 'outro', 'user_d', 'pass_d', 87654321, 1]
        ];
        foreach ($provedores as $provedor) {
            $stmt->execute($provedor);
        }

        // Inserir client_ids
        $stmt = $db->prepare("INSERT INTO client_ids (client_id, usuario, senha, ip, id_revendedor, provedor_id, ativo) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $client_ids = [
            // Ativos do João
            ['abcde-12345-fghij', 'cliente_joao_1', 'senha1', '192.168.1.1', 12345678, 1, 1],
            ['fghij-67890-klmno', 'cliente_joao_2', 'senha2', '192.168.1.2', 12345678, 2, 1],
            // Ativos do Pedro
            ['pqrst-11223-uvwxy', 'cliente_pedro_1', 'senha3', '192.168.1.3', 56781001, 3, 1],
            // Ativos da Ana
            ['hijkl-98765-mnopi', 'cliente_ana_1', 'senha4', '192.168.1.4', 56781002, 3, 1]
        ];
        foreach ($client_ids as $cliente) {
            $stmt->execute($cliente);
        }
        
        // Criar diretório para branding (se não existir)
        $brandingDir = __DIR__ . '/branding';
        if (!is_dir($brandingDir)) {
            mkdir($brandingDir, 0755, true);
        }
        
        // Log de instalação
        $stmt = $db->prepare("INSERT INTO logs_auditoria (id_revendedor, acao, detalhes, ip_origem) VALUES (?, ?, ?, ?)");
        $stmt->execute([10000000, 'sistema_instalado', 'NomaTV v4.5 instalado via interface web', $_SERVER['REMOTE_ADDR'] ?? 'localhost']);
        
        $db->commit();
        
        // Verificação final
        $verificacao = $db->query("SELECT 'OK' as status FROM revendedores WHERE parent_id = 10000000 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $statusVerificacao = $verificacao ? 'success' : 'error';
        $verificacaoMessage = $verificacao ? 'A coluna parent_id foi criada e os dados de teste migrados com sucesso.' : 'Falha na migração da coluna parent_id.';
        
        echo json_encode([
            'status' => $statusVerificacao,
            'message' => 'NomaTV v4.5 instalado com sucesso!',
            'detalhes' => [
                'tabelas_criadas' => 10,
                'usuarios_criados' => count($usuarios),
                'planos_criados' => count($planos),
                'provedores_criados' => count($provedores),
                'client_ids_criados' => count($client_ids),
                'diretorio_branding' => 'Criado: /api/branding/'
            ],
            'migracao_status' => $verificacaoMessage,
            'credenciais' => [
                'admin' => ['usuario' => 'admin', 'senha' => 'admin123', 'painel' => 'admin.html', 'tipo' => 'Super Admin'],
                'revendedor' => ['usuario' => 'joao', 'senha' => '123456', 'painel' => 'revendedor.html', 'tipo' => 'Revendedor Master'],
                'sub' => ['usuario' => 'pedro', 'senha' => '123456', 'painel' => 'revendedor.html', 'tipo' => 'Sub-revendedor']
            ],
            'proximos_passos' => [
                '1. Delete este arquivo install.php AGORA por segurança',
                '2. Acesse revendedor.html com joao/123456 para testar a nova hierarquia',
                '3. Teste o modal de "Ver Rede" para ver a cascata completa',
            ]
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Erro na instalação: ' . $e->getMessage()]);
    }
    exit;
}

// Interface HTML
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NomaTV v4.5 - Instalador Corrigido</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 700px;
            width: 100%;
            text-align: center;
        }
        
        .logo {
            font-size: 3em;
            margin-bottom: 10px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        
        .version {
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1em;
        }
        
        .fix-badge {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            margin-left: 10px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        .description {
            color: #555;
            margin-bottom: 40px;
            line-height: 1.6;
            font-size: 1.1em;
        }
        
        .install-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .install-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }
        
        .install-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .progress {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            display: none;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin: 10px 0;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(45deg, #28a745, #20c997);
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .result {
            margin-top: 30px;
            padding: 20px;
            border-radius: 10px;
            display: none;
        }
        
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        
        .credentials {
            margin-top: 20px;
            text-align: left;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }
        
        .credentials h3 {
            margin-bottom: 15px;
            color: #333;
            text-align: center;
        }
        
        .cred-item {
            margin-bottom: 10px;
            padding: 10px;
            background: white;
            border-radius: 5px;
            font-family: monospace;
            border-left: 4px solid #28a745;
        }
        
        .next-steps {
            margin-top: 20px;
            text-align: left;
            background: #e8f4f8;
            padding: 20px;
            border-radius: 10px;
        }
        
        .next-steps h3 {
            margin-bottom: 15px;
            color: #333;
            text-align: center;
        }
        
        .next-steps ol {
            padding-left: 20px;
        }
        
        .next-steps li {
            margin-bottom: 8px;
            color: #495057;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .feature {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: left;
        }
        
        .feature-icon {
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .feature h3 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .feature p {
            color: #666;
            line-height: 1.5;
        }
        
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .fix-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">📺</div>
        <h1>NomaTV <span class="fix-badge">v4.5</span></h1>
        <div class="version">Instalador com Hierarquia por parent_id</div>
        
        <div class="fix-info">
            <strong>🔧 MIGRAÇÃO APLICADA:</strong> Adicionando coluna `parent_id` e criando estrutura hierárquica.
        </div>
        
        <div class="description">
            Sistema completo de gestão IPTV com hierarquia de revendas infinita e faturamento em cascata.
        </div>
        
        <div class="features">
            <div class="feature">
                <div class="feature-icon">🗃️</div>
                <h3>10 Tabelas</h3>
                <p>Estrutura completa e otimizada para a nova lógica.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">🌳</div>
                <h3>Hierarquia Infinita</h3>
                <p>Nova coluna `parent_id` para uma árvore de revendas completa.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">📊</div>
                <h3>Faturamento em Cascata</h3>
                <p>Cálculo de receita considerando toda a rede de revendas.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">🎨</div>
                <h3>Branding Setup</h3>
                <p>Diretório `/branding/` criado e pronto para logos.</p>
            </div>
        </div>
        
        <div class="warning-box">
            ⚠️ <strong>Atenção:</strong> Esta instalação irá sobrescrever a base de dados se ela já existir. Faça backup antes de continuar.
        </div>
        
        <button class="install-btn" onclick="iniciarInstalacao()">
            🚀 Instalar NomaTV v4.5
        </button>
        
        <div class="progress" id="progress">
            <div>Instalando sistema...</div>
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <div id="progressText">Preparando instalação...</div>
        </div>
        
        <div class="result" id="result"></div>
    </div>

    <script>
        async function iniciarInstalacao() {
            const btn = document.querySelector('.install-btn');
            const progress = document.getElementById('progress');
            const result = document.getElementById('result');
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            
            btn.disabled = true;
            btn.textContent = 'Instalando...';
            progress.style.display = 'block';
            result.style.display = 'none';
            
            // Simular progresso
            const etapas = [
                'Verificando sistema...',
                'Criando banco SQLite...',
                'Criando tabelas com a nova lógica...',
                'Adicionando índices para performance...',
                'Inserindo dados padrão hierárquicos...',
                'Configurando provedores e clientes...',
                'Criando diretório branding...',
                'Finalizando instalação...'
            ];
            
            for (let i = 0; i < etapas.length; i++) {
                progressText.textContent = etapas[i];
                progressFill.style.width = ((i + 1) / etapas.length * 100) + '%';
                await new Promise(resolve => setTimeout(resolve, 600));
            }
            
            try {
                const response = await fetch('install.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=install'
                });
                
                const data = await response.json();
                
                progress.style.display = 'none';
                result.style.display = 'block';
                
                if (data.status === 'success') {
                    result.className = 'result success';
                    result.innerHTML = `
                        <h3>✅ ${data.message}</h3>
                        <p><strong>Detalhes da Instalação:</strong></p>
                        <ul>
                            <li>📊 Tabelas criadas: ${data.detalhes.tabelas_criadas}</li>
                            <li>👥 Usuários criados: ${data.detalhes.usuarios_criados}</li>
                            <li>📦 Planos criados: ${data.detalhes.planos_criados}</li>
                            <li>🔗 Provedores criados: ${data.detalhes.provedores_criados}</li>
                            <li>📱 Clientes criados: ${data.detalhes.client_ids_criados}</li>
                            <li>🎨 ${data.detalhes.diretorio_branding}</li>
                        </ul>
                        <div class="credentials">
                            <h3>🔑 Credenciais de Acesso</h3>
                            ${Object.entries(data.credenciais).map(([key, cred]) => `
                                <div class="cred-item">
                                    <strong>${cred.tipo}:</strong><br>
                                    Usuário: ${cred.usuario} | Senha: ${cred.senha}<br>
                                    Painel: ${cred.painel}
                                </div>
                            `).join('')}
                        </div>
                        <div class="next-steps">
                            <h3>📋 Próximos Passos</h3>
                            <p style="font-weight: bold; margin-bottom: 10px;">
                                ✅ Status da Migração: <span style="color: green;"><strong>${data.migracao_status}</strong></span>
                            </p>
                            <ol>
                                ${data.proximos_passos.map(step => `<li>${step}</li>`).join('')}
                            </ol>
                        </div>
                        <p style="margin-top: 20px; color: #d63384; font-weight: bold;">
                            🗑️ IMPORTANTE: Delete este arquivo install.php AGORA por segurança!
                        </p>
                    `;
                } else if (data.status === 'warning') {
                    result.className = 'result warning';
                    result.innerHTML = `<h3>⚠️ ${data.message}</h3>`;
                    btn.disabled = false;
                    btn.textContent = 'Tentar Novamente';
                } else {
                    result.className = 'result error';
                    result.innerHTML = `<h3>❌ ${data.message}</h3>`;
                    btn.disabled = false;
                    btn.textContent = 'Tentar Novamente';
                }
                
            } catch (error) {
                progress.style.display = 'none';
                result.style.display = 'block';
                result.className = 'result error';
                result.innerHTML = `<h3>❌ Erro de conexão: ${error.message}</h3>`;
                btn.disabled = false;
                btn.textContent = 'Tentar Novamente';
            }
        }
    </script>
</body>
</html>
