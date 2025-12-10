<?php
/**
 * API para envio de mensagens de suporte
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

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    sendJson(['success' => false, 'message' => 'Usuário não autenticado'], 401);
}

// Incluir conexão
require_once 'includes/conexao.php';

try {
    $pdo = getConexao();
} catch (Exception $e) {
    sendJson(['success' => false, 'message' => 'Erro de conexão: ' . $e->getMessage()], 500);
}

// Obter método
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'POST') {
        // Enviar nova mensagem
        $usuario_id = $_SESSION['usuario_id'];
        $tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : 'outro';
        $assunto = isset($_POST['assunto']) ? trim($_POST['assunto']) : '';
        $mensagem = isset($_POST['mensagem']) ? trim($_POST['mensagem']) : '';
        
        // Validação
        $erros = [];
        if (empty($tipo)) {
            $erros[] = 'Tipo de solicitação é obrigatório';
        } elseif (!in_array($tipo, ['duvida', 'problema', 'sugestao', 'outro'])) {
            $erros[] = 'Tipo de solicitação inválido';
        }
        
        if (empty($assunto)) {
            $erros[] = 'Assunto é obrigatório';
        } elseif (strlen($assunto) < 5) {
            $erros[] = 'Assunto deve ter pelo menos 5 caracteres';
        }
        
        if (empty($mensagem)) {
            $erros[] = 'Mensagem é obrigatória';
        } elseif (strlen($mensagem) < 10) {
            $erros[] = 'Mensagem deve ter pelo menos 10 caracteres';
        }
        
        if (!empty($erros)) {
            sendJson(['success' => false, 'message' => 'Dados inválidos: ' . implode(', ', $erros)], 400);
        }
        
        // Inserir mensagem (tipo já está no formato correto do banco: 'duvida', 'problema', 'sugestao', 'outro')
        $stmt = $pdo->prepare("INSERT INTO mensagens_suporte (usuario_id, tipo, assunto, mensagem, status) VALUES (?, ?, ?, ?, 'aberta')");
        $stmt->execute([$usuario_id, $tipo, $assunto, $mensagem]);
        
        sendJson([
            'success' => true,
            'message' => 'Mensagem enviada com sucesso! Nossa equipe entrará em contato em breve.',
            'id' => $pdo->lastInsertId()
        ]);
        
    } elseif ($method === 'GET') {
        // Buscar mensagens do usuário
        $usuario_id = $_SESSION['usuario_id'];
        $action = isset($_GET['action']) ? $_GET['action'] : '';
        
        if ($action === 'list') {
            $stmt = $pdo->prepare("
                SELECT id, tipo, assunto, mensagem, status, resposta, data_criacao, data_resposta
                FROM mensagens_suporte
                WHERE usuario_id = ?
                ORDER BY data_criacao DESC
            ");
            $stmt->execute([$usuario_id]);
            $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendJson(['success' => true, 'data' => $mensagens]);
        } else {
            sendJson(['success' => false, 'message' => 'Ação não especificada'], 400);
        }
    } else {
        sendJson(['success' => false, 'message' => 'Método não permitido'], 405);
    }
    
} catch (PDOException $e) {
    error_log("Erro PDO na API de suporte: " . $e->getMessage());
    sendJson(['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    error_log("Erro na API de suporte: " . $e->getMessage());
    sendJson(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
}

