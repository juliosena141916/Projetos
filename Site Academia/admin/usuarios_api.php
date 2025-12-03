<?php
// Iniciar sessão apenas se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

// Verificar se é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

// Incluir arquivo de conexão
$conexao_path = dirname(dirname(__FILE__)) . '/includes/conexao.php';
if (!file_exists($conexao_path)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Arquivo de conexão não encontrado']);
    exit;
}
require_once $conexao_path;

try {
    $pdo = getConexao();
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch ($action) {
        case 'list':
            // Listar todos os usuários
            $stmt = $pdo->query("SELECT id, nome, email, tipo_usuario, ativo, data_cadastro FROM usuarios ORDER BY data_cadastro DESC");
            $usuarios = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $usuarios]);
            break;
            
        case 'get':
            // Obter um usuário específico
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
                exit;
            }
            
            $stmt = $pdo->prepare("SELECT id, nome, email, tipo_usuario, ativo, data_cadastro FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $usuario = $stmt->fetch();
            
            if (!$usuario) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
                exit;
            }
            
            echo json_encode(['success' => true, 'data' => $usuario]);
            break;
            
        case 'update':
            // Atualizar um usuário
            $id = $_POST['id'] ?? null;
            $nome = $_POST['nome'] ?? '';
            $email = $_POST['email'] ?? '';
            $tipo_usuario = $_POST['tipo_usuario'] ?? 'usuario';
            $ativo = $_POST['ativo'] ?? 1;
            
            if (!$id || !$nome || !$email) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
                exit;
            }
            
            // Verificar se o email já existe (exceto para o usuário atual)
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Email já cadastrado']);
                exit;
            }
            
            $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, tipo_usuario = ?, ativo = ? WHERE id = ?");
            $stmt->execute([$nome, $email, $tipo_usuario, $ativo, $id]);
            
            echo json_encode(['success' => true, 'message' => 'Usuário atualizado com sucesso']);
            break;
            
        case 'delete':
            // Deletar um usuário
            $id = $_POST['id'] ?? null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
                exit;
            }
            
            // Não permitir deletar o próprio usuário
            if ($id == $_SESSION['usuario_id']) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Você não pode deletar sua própria conta']);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Usuário deletado com sucesso']);
            break;
            
        case 'create':
            // Criar um novo usuário
            $nome = $_POST['nome'] ?? '';
            $email = $_POST['email'] ?? '';
            $senha = $_POST['senha'] ?? '';
            $tipo_usuario = $_POST['tipo_usuario'] ?? 'usuario';
            
            if (!$nome || !$email || !$senha) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
                exit;
            }
            
            // Verificar se o email já existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Email já cadastrado']);
                exit;
            }
            
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo_usuario) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $senha_hash, $tipo_usuario]);
            
            echo json_encode(['success' => true, 'message' => 'Usuário criado com sucesso']);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ação não reconhecida']);
    }
    
} catch (PDOException $e) {
    error_log("Erro na API de usuários: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao processar requisição']);
}
?>
