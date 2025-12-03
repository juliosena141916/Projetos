<?php
/**
 * API para gerenciamento de mensagens de suporte (Admin)
 */

// Desabilitar exibição de erros no output
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Iniciar sessão se necessário
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Headers JSON
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Função para enviar resposta JSON
function sendJson($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar se é admin
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    sendJson(['success' => false, 'message' => 'Acesso negado. Faça login como administrador.'], 403);
}

// Incluir conexão
$conexao_path = dirname(__DIR__) . '/includes/conexao.php';
if (!file_exists($conexao_path)) {
    sendJson(['success' => false, 'message' => 'Arquivo de conexão não encontrado'], 500);
}
require_once $conexao_path;

try {
    $pdo = getConexao();
} catch (Exception $e) {
    sendJson(['success' => false, 'message' => 'Erro de conexão: ' . $e->getMessage()], 500);
}

// Obter action
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            $stmt = $pdo->query("
                SELECT m.*, u.nome as usuario_nome, u.email as usuario_email,
                       r.nome as respondido_por_nome
                FROM mensagens_suporte m
                JOIN usuarios u ON m.usuario_id = u.id
                LEFT JOIN usuarios r ON m.respondido_por = r.id
                ORDER BY m.data_criacao DESC
            ");
            $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendJson(['success' => true, 'data' => $mensagens]);
            break;
            
        case 'responder':
            $mensagem_id = isset($_POST['mensagem_id']) ? intval($_POST['mensagem_id']) : 0;
            $resposta = isset($_POST['resposta']) ? trim($_POST['resposta']) : '';
            $status = isset($_POST['status']) ? trim($_POST['status']) : 'aberta';
            $admin_id = $_SESSION['usuario_id'];
            
            // Validação
            if (!$mensagem_id) {
                sendJson(['success' => false, 'message' => 'ID da mensagem não fornecido'], 400);
            }
            
            if (empty($resposta)) {
                sendJson(['success' => false, 'message' => 'Resposta é obrigatória'], 400);
            }
            
            if (!in_array($status, ['aberta', 'em_atendimento', 'resolvida', 'fechada'])) {
                sendJson(['success' => false, 'message' => 'Status inválido'], 400);
            }
            
            // Atualizar mensagem
            $stmt = $pdo->prepare("
                UPDATE mensagens_suporte 
                SET resposta = ?, status = ?, respondido_por = ?, data_resposta = NOW()
                WHERE id = ?
            ");
            $result = $stmt->execute([$resposta, $status, $admin_id, $mensagem_id]);
            
            if ($result) {
                sendJson([
                    'success' => true,
                    'message' => 'Resposta salva com sucesso!'
                ]);
            } else {
                sendJson(['success' => false, 'message' => 'Erro ao salvar resposta'], 500);
            }
            break;
            
        case 'get':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if (!$id) {
                sendJson(['success' => false, 'message' => 'ID não fornecido'], 400);
            }
            
            $stmt = $pdo->prepare("
                SELECT m.*, u.nome as usuario_nome, u.email as usuario_email
                FROM mensagens_suporte m
                JOIN usuarios u ON m.usuario_id = u.id
                WHERE m.id = ?
            ");
            $stmt->execute([$id]);
            $mensagem = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$mensagem) {
                sendJson(['success' => false, 'message' => 'Mensagem não encontrada'], 404);
            }
            
            sendJson(['success' => true, 'data' => $mensagem]);
            break;
            
        default:
            sendJson(['success' => false, 'message' => 'Ação não reconhecida: ' . $action], 400);
    }
    
} catch (PDOException $e) {
    error_log("Erro PDO na API de suporte admin: " . $e->getMessage());
    sendJson(['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    error_log("Erro na API de suporte admin: " . $e->getMessage());
    sendJson(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
}

