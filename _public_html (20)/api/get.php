<?php
/**
 * GET.PHP - Sistema de Branding NomaTV v4.5 (VERSÃO SIMPLES)
 * 
 * FUNÇÃO: Carregar logo do revendedor baseado no sessionStorage
 * 
 * LÓGICA:
 * 1. Recebe revendedor_id do frontend (sessionStorage)
 * 2. Busca logo em: /uploads/logos/{revendedor_id}.png
 * 3. Retorna status da logo (existe/não existe)
 * 
 * LOCALIZAÇÃO: /api/branding/get.php
 */

// Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ✅ CONFIGURAÇÃO SIMPLES
define('UPLOAD_DIR', __DIR__ . '/../../uploads/logos/');

// ✅ CONEXÃO COM BANCO
try {
    $db = new PDO("sqlite:" . __DIR__ . '/../../db.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro de conexão com banco']);
    exit();
}

// ✅ PROCESSAR REQUISIÇÃO
try {
    
    // Obter revendedor_id (sempre vem do frontend via sessionStorage)
    $revendedorId = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $revendedorId = $input['revendedor_id'] ?? null;
    } else {
        $revendedorId = $_GET['revendedor_id'] ?? null;
    }
    
    if (!$revendedorId) {
        throw new Exception('ID do revendedor é obrigatório');
    }
    
    // Verificar se revendedor existe
    $stmt = $db->prepare("SELECT nome FROM revendedores WHERE id_revendedor = ?");
    $stmt->execute([$revendedorId]);
    $revendedor = $stmt->fetch();
    
    if (!$revendedor) {
        throw new Exception('Revendedor não encontrado');
    }
    
    // ✅ BUSCAR LOGO (CAMINHO ÚNICO)
    $logoFile = UPLOAD_DIR . $revendedorId . '.png';
    $logoExists = file_exists($logoFile) && filesize($logoFile) > 0;
    
    if ($logoExists) {
        // Logo existe - mostrar e bloquear upload
        echo json_encode([
            'success' => true,
            'data' => [
                'revendedor_id' => $revendedorId,
                'revendedor_nome' => $revendedor['nome'],
                'logo_exists' => true,
                'logo_url' => '/uploads/logos/' . $revendedorId . '.png',
                'logo_filename' => $revendedorId . '.png',
                'logo_size' => filesize($logoFile),
                'upload_blocked' => true,
                'status' => 'Logo ativa'
            ]
        ]);
    } else {
        // Logo não existe - permitir upload
        echo json_encode([
            'success' => true,
            'data' => [
                'revendedor_id' => $revendedorId,
                'revendedor_nome' => $revendedor['nome'],
                'logo_exists' => false,
                'logo_url' => null,
                'logo_filename' => $revendedorId . '.png',
                'logo_size' => 0,
                'upload_blocked' => false,
                'status' => 'Nenhuma logo configurada'
            ]
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$db = null;
exit();
?>

