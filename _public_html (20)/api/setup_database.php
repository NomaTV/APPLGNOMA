<?php
// =============================================
// SETUP COMPLETO DO BANCO DE DADOS NomaTV v4.2
// =============================================

header('Content-Type: application/json');

try {
    $db = new PDO('sqlite:' . __DIR__ . '/db (7).db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Tabela: revendedores
    $db->exec("CREATE TABLE IF NOT EXISTS revendedores (
        id_revendedor INTEGER PRIMARY KEY,
        usuario TEXT UNIQUE NOT NULL,
        senha TEXT NOT NULL,
        nome TEXT NOT NULL,
        email TEXT,
        master TEXT NOT NULL,
        master_de INTEGER,
        plano TEXT DEFAULT 'Básico',
        valor_ativo DECIMAL(10,2),
        valor_mensal DECIMAL(10,2),
        limite_ativos INTEGER DEFAULT 100,
        ativo BOOLEAN DEFAULT 1,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (master_de) REFERENCES revendedores(id_revendedor),
        CHECK (master IN ('admin', 'sim', 'nao')),
        CHECK ((valor_ativo IS NOT NULL AND valor_mensal IS NULL) OR (valor_ativo IS NULL AND valor_mensal IS NOT NULL))
    )");

    // 2. Tabela: provedores
    $db->exec("CREATE TABLE IF NOT EXISTS provedores (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT UNIQUE NOT NULL,
        dns TEXT NOT NULL,
        tipo TEXT DEFAULT 'xtream',
        id_revendedor INTEGER NOT NULL,
        ativo BOOLEAN DEFAULT 1,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor) ON DELETE CASCADE
    )");

    // 3. Tabela: client_ids
    $db->exec("CREATE TABLE IF NOT EXISTS client_ids (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        client_id TEXT UNIQUE NOT NULL,
        provedor_id INTEGER NOT NULL,
        id_revendedor INTEGER NOT NULL,
        usuario TEXT,
        primeira_conexao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ultima_atividade TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ativo BOOLEAN DEFAULT 1,
        ip TEXT,
        user_agent TEXT,
        FOREIGN KEY (provedor_id) REFERENCES provedores(id) ON DELETE CASCADE,
        FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor) ON DELETE CASCADE
    )");

    // 4. Tabela: auditoria
    $db->exec("CREATE TABLE IF NOT EXISTS auditoria (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        id_revendedor INTEGER,
        acao TEXT NOT NULL,
        detalhes TEXT,
        ip TEXT,
        user_agent TEXT,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor) ON DELETE SET NULL
    )");

    echo json_encode(['success' => true, 'message' => 'Tabelas criadas com sucesso.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
