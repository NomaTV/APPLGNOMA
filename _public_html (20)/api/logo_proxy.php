<?php
/**
 * VALIDAR_LOGIN.PHP - NomaTV v4.2 VINCULADOR - CORRIGIDO E ALINHADO
 * 
 * FUNÇÃO: "O Vinculador" - Valida provedor e vincula client_id
 * 
 * CHAMADO POR: login.html (sessão de login)
 * 
 * RESPONSABILIDADE ÚNICA:
 * 1. ✅ Recebe 4 dados (provedor, username, password, client_id)
 * 2. ✅ Valida se provedor existe e está ativo
 * 3. ✅ Vincula client_id ao provedor/revendedor
 * 4. ✅ Retorna 5 variáveis para sessionStorage
 * 
 * INPUT: {"provedor": "zeus", "username": "teste", "password": "123", "client_id": "abc..."}
 * OUTPUT: {"success": true, "data": {"provedor", "username", "password", "dns", "revendedor_id"}}
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ✅ CONEXÃO DIRETA COM BANCO SQLite
try {
    $dbPath = __DIR__ . '/nomatv.db';
    $db = new PDO("sqlite:$dbPath");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->exec("PRAGMA foreign_keys = ON");
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro: falha na conexão com banco de dados',
        'details' => $e->getMessage()
    ]);
    exit();
}

// ✅ VALIDAÇÃO DE MÉTODO HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método não permitido'
    ]);
    exit();
}

// ✅ DECODIFICAR ENTRADA JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'JSON inválido'
    ]);
    exit();
}

// ✅ VALIDAR 4 VARIÁVEIS DE ENTRADA
$provedor = trim($input['provedor'] ?? '');
$username = trim($input['username'] ?? ''); 
$password = trim($input['password'] ?? '');
$clientId = trim($input['client_id'] ?? '');

if (empty($provedor) || empty($username) || empty($password) || empty($clientId)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Campos obrigatórios: provedor, username, password, client_id'
    ]);
    exit();
}

try {
    // ✅ CONSULTA SQL v4.2 CORRETA - Estrutura unificada
    $stmt = $db->prepare("
        SELECT p.id, p.nome, p.dns, p.id_revendedor 
        FROM provedores p 
        JOIN revendedores r ON p.id_revendedor = r.id_revendedor
        WHERE LOWER(p.nome) = LOWER(?) AND p.ativo = 1 AND r.ativo = 1
    ");
    
    $stmt->execute([$provedor]);
    $provedorData = $stmt->fetch();
    
    if (!$provedorData) {
        echo json_encode([
            'success' => false,
            'error' => 'Provedor não encontrado ou inativo'
        ]);
        exit();
    }
    
    // ✅ LÓGICA DE VINCULAÇÃO CLIENT_ID
    $stmtCheck = $db->prepare("SELECT id FROM client_ids WHERE client_id = ?");
    $stmtCheck->execute([$clientId]);
    $existingClient = $stmtCheck->fetch();
    
    if ($existingClient) {
        // ✅ CLIENT_ID JÁ EXISTS - ATUALIZAR VINCULAÇÃO
        $stmtUpdate = $db->prepare("
            UPDATE client_ids 
            SET provedor_id = ?, id_revendedor = ?, usuario = ?, ultima_atividade = CURRENT_TIMESTAMP, ativo = 1
            WHERE client_id = ?
        ");
        $stmtUpdate->execute([
            $provedorData['id'],
            $provedorData['id_revendedor'], 
            $username,
            $clientId
        ]);
    } else {
        // ✅ CLIENT_ID NÃO EXISTE - CRIAR NOVA VINCULAÇÃO
        $stmtInsert = $db->prepare("
            INSERT INTO client_ids (client_id, provedor_id, id_revendedor, usuario, ativo) 
            VALUES (?, ?, ?, ?, 1)
        ");
        $stmtInsert->execute([
            $clientId,
            $provedorData['id'],
            $provedorData['id_revendedor'],
            $username
        ]);
    }
    
    // ✅ RETORNAR 5 VARIÁVEIS PARA SESSIONSTORAGE
    echo json_encode([
        'success' => true,
        'data' => [
            'provedor' => $provedorData['nome'],
            'username' => $username,
            'password' => $password,
            'dns' => $provedorData['dns'],
            'revendedor_id' => $provedorData['id_revendedor']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor',
        'details' => $e->getMessage()
    ]);
}
?>