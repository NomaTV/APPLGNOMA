<?php
/**
 * =================================================================
 * HELPER DE AUTENTICAÇÃO - NomaTV API v4.5
 * =================================================================
 *
 * ARQUIVO: /api/helpers/auth_helper.php
 * VERSÃO: 4.5 - Padronização da Verificação de Sessão
 *
 * RESPONSABILIDADES:
 * ✅ Fornecer uma verificação de autenticação padrão para todos os endpoints.
 * ✅ Inicia a sessão e verifica se os dados essenciais do usuário estão presentes.
 *
 * ATENÇÃO: Este arquivo NÃO FAZ LOGIN. Ele apenas verifica o estado de uma sessão já iniciada.
 *
 * =================================================================
 */

// Inicia a sessão. Se já estiver iniciada, ela é retomada.
// Se não, uma nova sessão é criada.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Funções auxiliares para padronizar as respostas.
 * Se precisar de mais, inclua o arquivo standard_response.php aqui.
 */
function standardResponse(bool $success, $data = null, $message = null, $extraData = null): void
{
    // ✅ CORREÇÃO: Garante que a resposta seja formatada em JSON e o script seja encerrado.
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'extraData' => $extraData
    ]);
    exit();
}

// =============================================
// 🔐 VERIFICAÇÃO DE AUTENTICAÇÃO
// =============================================

// Verifica se os dados essenciais da sessão existem.
// A ausência de um deles significa que o usuário não está autenticado.
if (empty($_SESSION['id_revendedor']) || empty($_SESSION['master']) || empty($_SESSION['usuario'])) {
    http_response_code(401);
    standardResponse(false, null, 'Usuário não autenticado.');
}

// ✅ SE CHEGOU ATÉ AQUI, O USUÁRIO ESTÁ AUTENTICADO.
// Os dados do usuário logado podem ser acessados em qualquer arquivo que inclua este helper.
$loggedInRevendedorId = $_SESSION['id_revendedor'];
$loggedInUserType = $_SESSION['master'];
$loggedInUsuario = $_SESSION['usuario'];

// ✅ CORREÇÃO: Limpa o buffer de saída que pode ter sido iniciado por outros arquivos
if (ob_get_level() > 0) {
    ob_end_clean();
}

// O script continua a partir daqui, sem a necessidade de mais verificações de autenticação.