<?php
/**
 * API para notificações
 */

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/conexao.php';

ob_clean();
header('Content-Type: application/json');

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$usuario_id = $_SESSION['usuario_id'];
$is_admin = isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin';

try {
    $pdo = getConexao();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro de conexão']);
    exit;
}

try {
    switch ($method) {
        case 'GET':
            $action = $_GET['action'] ?? '';
            
            if ($action === 'minhas_notificacoes') {
                // Notificações do usuário
                $stmt = $pdo->prepare("
                    SELECT * FROM notificacoes 
                    WHERE usuario_id = ? 
                    ORDER BY data_criacao DESC 
                    LIMIT 50
                ");
                $stmt->execute([$usuario_id]);
                $notificacoes = $stmt->fetchAll();
                
                // Contar não lidas
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as total FROM notificacoes 
                    WHERE usuario_id = ? AND lida = 0
                ");
                $stmt->execute([$usuario_id]);
                $nao_lidas = $stmt->fetch()['total'];
                
                echo json_encode([
                    'success' => true, 
                    'data' => $notificacoes,
                    'nao_lidas' => intval($nao_lidas)
                ]);
                break;
            }
            break;
            
        case 'PUT':
            // Marcar como lida
            $data = json_decode(file_get_contents('php://input'), true);
            $id = isset($data['id']) ? intval($data['id']) : 0;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
                break;
            }
            
            // Verificar se a notificação pertence ao usuário
            $stmt = $pdo->prepare("SELECT usuario_id FROM notificacoes WHERE id = ?");
            $stmt->execute([$id]);
            $notificacao = $stmt->fetch();
            
            if (!$notificacao || $notificacao['usuario_id'] != $usuario_id) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                break;
            }
            
            $stmt = $pdo->prepare("
                UPDATE notificacoes 
                SET lida = 1, data_leitura = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Notificação marcada como lida']);
            break;
            
        case 'DELETE':
            // Deletar notificação
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
                break;
            }
            
            // Verificar se a notificação pertence ao usuário
            $stmt = $pdo->prepare("SELECT usuario_id FROM notificacoes WHERE id = ?");
            $stmt->execute([$id]);
            $notificacao = $stmt->fetch();
            
            if (!$notificacao || $notificacao['usuario_id'] != $usuario_id) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                break;
            }
            
            $stmt = $pdo->prepare("DELETE FROM notificacoes WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Notificação removida']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    }
} catch (PDOException $e) {
    error_log("Erro PDO na API de notificações: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro no banco de dados']);
} catch (Exception $e) {
    error_log("Erro na API de notificações: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}

