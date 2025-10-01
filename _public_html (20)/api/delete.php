<?php
/**
 * ENDPOINT DELETE BRANDING - NomaTV API v4.5
 * RESPONSABILIDADES:
 * ✅ Remove logo personalizada do revendedor logado
 * ✅ Remove arquivo físico e registro da tabela branding
 * ✅ Lida com arquivos órfãos no sistema de arquivos
 * ✅ Suporte para DELETE e POST (compatibilidade fetch API)
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);

// Headers CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../config/database_sqlite.php';

// ✅ FUNÇÃO RESPOSTA PADRONIZADA
function standardResponse(bool $success, $data = null, $message = null, $extraData = null): void
{
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'extraData' => $extraData
    ]);
    exit();
}

// ✅ AUTENTICAÇÃO PADRÃO (OBRIGATÓRIA)
session_start();
if (empty($_SESSION['id_revendedor']) || empty($_SESSION['master'])) {
    http_response_code(401);
    exit('{"success":false,"message":"Usuário não autenticado"}');
}
$loggedInRevendedorId = $_SESSION['id_revendedor'];
$loggedInUserType = $_SESSION['master'];

// ✅ ROTEAMENTO PRINCIPAL
$method = $_SERVER['REQUEST_METHOD'];
if (!in_array($method, ['POST', 'DELETE'])) {
    http_response_code(405);
    standardResponse(false, null, 'Método não permitido. Use POST ou DELETE.');
}

try {
    handleDeleteLogo($db, $loggedInRevendedorId);
} catch (Exception $e) {
    error_log("NomaTV v4.5 [BRANDING-DELETE] Erro: " . $e->getMessage());
    http_response_code(500);
    standardResponse(false, null, 'Erro interno do servidor.');
}

/**
 * =================================================================
 * HANDLER PRINCIPAL
 * =================================================================
 */

/**
 * Handler para remoção de logo do usuário logado
 */
function handleDeleteLogo(PDO $db, string $loggedInRevendedorId): void
{
    try {
        // ✅ BUSCAR LOGO NA TABELA BRANDING
        $stmt = $db->prepare("
            SELECT 
                b.nome_arquivo_logo, 
                r.nome as revendedor_nome, 
                r.ativo as revendedor_ativo
            FROM branding b
            JOIN revendedores r ON b.id_revendedor = r.id_revendedor
            WHERE b.id_revendedor = ?
        ");
        $stmt->execute([$loggedInRevendedorId]);
        $brandingData = $stmt->fetch();

        if (!$brandingData) {
            // ✅ VERIFICAR ARQUIVOS ÓRFÃOS
            $arquivosOrfaos = removerArquivosFisicos($loggedInRevendedorId);

            if (empty($arquivosOrfaos)) {
                // ✅ VERIFICAR SE REVENDEDOR EXISTE
                $stmt = $db->prepare("SELECT nome FROM revendedores WHERE id_revendedor = ?");
                $stmt->execute([$loggedInRevendedorId]);
                $revendedor = $stmt->fetch();
                
                if (!$revendedor) {
                    http_response_code(404);
                    standardResponse(false, null, 'Revendedor não encontrado.');
                } else {
                    http_response_code(404);
                    standardResponse(false, null, 'Este revendedor não possui logo para remover.');
                }
            } else {
                // ✅ ARQUIVOS ÓRFÃOS REMOVIDOS
                standardResponse(true, [
                    'removed_files' => $arquivosOrfaos,
                    'id_revendedor' => $loggedInRevendedorId
                ], 'Arquivos órfãos removidos com sucesso.');
            }
        }

        $logoAtual = $brandingData['nome_arquivo_logo'];
        $revendedorNome = $brandingData['revendedor_nome'];
        $revendedorAtivo = (bool)$brandingData['revendedor_ativo'];

        // ✅ VERIFICAR SE REVENDEDOR ESTÁ ATIVO
        if (!$revendedorAtivo) {
            http_response_code(400);
            standardResponse(false, null, 'Revendedor inativo. Não é possível remover logo.');
        }

        // ✅ INICIAR TRANSAÇÃO PARA REMOÇÃO
        $db->beginTransaction();

        try {
            // ✅ REMOVER ARQUIVOS FÍSICOS
            $arquivosRemovidos = removerArquivosFisicos($loggedInRevendedorId);

            // ✅ REMOVER REGISTRO DA TABELA BRANDING
            $stmt = $db->prepare("DELETE FROM branding WHERE id_revendedor = ?");
            $stmt->execute([$loggedInRevendedorId]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Falha ao remover registro da tabela branding.');
            }

            $db->commit();

            standardResponse(true, [
                'removed_file' => $logoAtual,
                'removed_files' => $arquivosRemovidos,
                'id_revendedor' => $loggedInRevendedorId,
                'revendedor_nome' => $revendedorNome
            ], 'Logo removida com sucesso.');

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

    } catch (Exception $e) {
        error_log("NomaTV v4.5 [BRANDING-DELETE] Erro em handleDeleteLogo: " . $e->getMessage());
        http_response_code(400);
        standardResponse(false, null, $e->getMessage());
    }
}

/**
 * =================================================================
 * FUNÇÕES DE MANIPULAÇÃO DE ARQUIVOS
 * =================================================================
 */

/**
 * Remove arquivos físicos do revendedor no diretório de logos
 */
function removerArquivosFisicos(string $revendedorId): array
{
    $baseDir = __DIR__ . '/../../logos/';
    $pattern = $baseDir . $revendedorId . '.*';
    $arquivos = glob($pattern);

    $removidos = [];

    foreach ($arquivos as $caminhoCompleto) {
        $nomeArquivo = basename($caminhoCompleto);

        if (file_exists($caminhoCompleto) && is_file($caminhoCompleto)) {
            if (unlink($caminhoCompleto)) {
                $removidos[] = $nomeArquivo;
            } else {
                error_log("NomaTV v4.5 [BRANDING-DELETE] Falha ao remover arquivo físico: {$nomeArquivo}");
            }
        }
    }

    return $removidos;
}
?>