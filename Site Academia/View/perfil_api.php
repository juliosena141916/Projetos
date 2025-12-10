<?php
/**
 * API para gerenciamento de perfil do usuário
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

// Obter action
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'update':
            $usuario_id = $_SESSION['usuario_id'];
            $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $senha_atual = isset($_POST['senha_atual']) ? $_POST['senha_atual'] : '';
            $senha_nova = isset($_POST['senha_nova']) ? $_POST['senha_nova'] : '';
            
            // Validação
            $erros = [];
            if (empty($nome)) {
                $erros[] = 'Nome é obrigatório';
            } elseif (strlen($nome) < 3) {
                $erros[] = 'Nome deve ter pelo menos 3 caracteres';
            }
            
            if (empty($email)) {
                $erros[] = 'E-mail é obrigatório';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erros[] = 'E-mail inválido';
            }
            
            if (!empty($erros)) {
                sendJson(['success' => false, 'message' => 'Dados inválidos: ' . implode(', ', $erros)], 400);
            }
            
            // Verificar se o email já está em uso por outro usuário
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$email, $usuario_id]);
            if ($stmt->fetch()) {
                sendJson(['success' => false, 'message' => 'Este e-mail já está em uso por outro usuário'], 400);
            }
            
            // Se forneceu senha nova, validar senha atual
            if (!empty($senha_nova)) {
                if (empty($senha_atual)) {
                    sendJson(['success' => false, 'message' => 'Para alterar a senha, é necessário informar a senha atual'], 400);
                }
                
                // Buscar senha atual do usuário
                $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
                $stmt->execute([$usuario_id]);
                $usuario = $stmt->fetch();
                
                if (!$usuario || !password_verify($senha_atual, $usuario['senha'])) {
                    sendJson(['success' => false, 'message' => 'Senha atual incorreta'], 400);
                }
                
                // Validar nova senha
                if (strlen($senha_nova) < 8) {
                    sendJson(['success' => false, 'message' => 'A nova senha deve ter pelo menos 8 caracteres'], 400);
                }
                
                // Atualizar com nova senha
                $senha_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, senha = ? WHERE id = ?");
                $result = $stmt->execute([$nome, $email, $senha_hash, $usuario_id]);
            } else {
                // Atualizar sem alterar senha
                $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ?");
                $result = $stmt->execute([$nome, $email, $usuario_id]);
            }
            
            if ($result) {
                // Atualizar dados da sessão
                $_SESSION['usuario_nome'] = $nome;
                $_SESSION['usuario_email'] = $email;
                
                sendJson([
                    'success' => true, 
                    'message' => 'Perfil atualizado com sucesso!',
                    'data' => [
                        'nome' => $nome,
                        'email' => $email
                    ]
                ]);
            } else {
                sendJson(['success' => false, 'message' => 'Erro ao atualizar perfil'], 500);
            }
            break;
            
        case 'get':
            $usuario_id = $_SESSION['usuario_id'];
            $stmt = $pdo->prepare("SELECT id, nome, email, data_cadastro, data_atualizacao FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario_id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                sendJson(['success' => false, 'message' => 'Usuário não encontrado'], 404);
            }
            
            sendJson(['success' => true, 'data' => $usuario]);
            break;
            
        default:
            sendJson(['success' => false, 'message' => 'Ação não reconhecida: ' . $action], 400);
    }
    
} catch (PDOException $e) {
    error_log("Erro PDO na API de perfil: " . $e->getMessage());
    sendJson(['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    error_log("Erro na API de perfil: " . $e->getMessage());
    sendJson(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
}
