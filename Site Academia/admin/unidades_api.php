<?php
require_once 'check_admin.php';
header('Content-Type: application/json');

// A verificação de admin é feita pelo check_admin.php
// Se chegou aqui, o usuário é admin e a sessão está ativa.

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
            // Listar todas as unidades
            $stmt = $pdo->query("SELECT id, nome, cidade, endereco, telefone, horario_funcionamento, ativo, data_criacao FROM unidades ORDER BY nome");
            $unidades = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $unidades]);
            break;
            
        case 'get':
            // Obter uma unidade específica
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
                exit;
            }
            
            $stmt = $pdo->prepare("SELECT id, nome, cidade, endereco, telefone, horario_funcionamento, ativo, data_criacao FROM unidades WHERE id = ?");
            $stmt->execute([$id]);
            $unidade = $stmt->fetch();
            
            if (!$unidade) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Unidade não encontrada']);
                exit;
            }
            
            echo json_encode(['success' => true, 'data' => $unidade]);
            break;
            
        case 'update':
            // Atualizar uma unidade
            $id = $_POST['id'] ?? null;
            $nome = $_POST['nome'] ?? '';
            $cidade = $_POST['cidade'] ?? '';
            $endereco = $_POST['endereco'] ?? '';
            $telefone = $_POST['telefone'] ?? '';
            $horario_funcionamento = $_POST['horario_funcionamento'] ?? '';
            $ativo = $_POST['ativo'] ?? 1;
            
            if (!$id || !$nome || !$cidade || !$endereco) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
                exit;
            }
            
            $stmt = $pdo->prepare("UPDATE unidades SET nome = ?, cidade = ?, endereco = ?, telefone = ?, horario_funcionamento = ?, ativo = ? WHERE id = ?");
            $stmt->execute([$nome, $cidade, $endereco, $telefone, $horario_funcionamento, $ativo, $id]);
            
            echo json_encode(['success' => true, 'message' => 'Unidade atualizada com sucesso']);
            break;
            
        case 'delete':
            // Deletar uma unidade
            $id = $_POST['id'] ?? null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM unidades WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Unidade deletada com sucesso']);
            break;
            
        case 'create':
            // Criar uma nova unidade
            $nome = $_POST['nome'] ?? '';
            $cidade = $_POST['cidade'] ?? '';
            $endereco = $_POST['endereco'] ?? '';
            $telefone = $_POST['telefone'] ?? '';
            $horario_funcionamento = $_POST['horario_funcionamento'] ?? '';
            
            if (!$nome || !$cidade || !$endereco) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
                exit;
            }
            
            $stmt = $pdo->prepare("INSERT INTO unidades (nome, cidade, endereco, telefone, horario_funcionamento) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $cidade, $endereco, $telefone, $horario_funcionamento]);
            
            echo json_encode(['success' => true, 'message' => 'Unidade criada com sucesso']);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ação não reconhecida']);
    }
    
} catch (PDOException $e) {
    error_log("Erro na API de unidades: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao processar requisição: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Erro geral na API de unidades: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao processar requisição: ' . $e->getMessage()]);
}
?>
