<?php
/**
 * API para gerenciamento de cursos
 */

// Desabilitar exibição de erros no output (vai para log)
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
            $stmt = $pdo->query("SELECT id, nome, categoria, descricao, duracao, valor_total, ativo, data_criacao FROM cursos ORDER BY nome");
            $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendJson(['success' => true, 'data' => $cursos]);
            break;
            
        case 'get':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if (!$id) {
                sendJson(['success' => false, 'message' => 'ID não fornecido'], 400);
            }
            
            $stmt = $pdo->prepare("SELECT id, nome, categoria, descricao, duracao, valor_total, ativo, data_criacao FROM cursos WHERE id = ?");
            $stmt->execute([$id]);
            $curso = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$curso) {
                sendJson(['success' => false, 'message' => 'Curso não encontrado'], 404);
            }
            
            sendJson(['success' => true, 'data' => $curso]);
            break;
            
        case 'update':
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
            $categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : '';
            $descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
            $duracao = isset($_POST['duracao']) ? intval($_POST['duracao']) : 0;
            $valor_total = isset($_POST['valor_total']) ? floatval(str_replace(',', '.', $_POST['valor_total'])) : 0;
            $ativo = isset($_POST['ativo']) ? intval($_POST['ativo']) : 1;
            
            // Validação
            $erros = [];
            if (!$id) $erros[] = 'ID inválido';
            if (empty($nome)) $erros[] = 'Nome é obrigatório';
            if (empty($categoria)) $erros[] = 'Categoria é obrigatória';
            if (empty($descricao)) $erros[] = 'Descrição é obrigatória';
            if ($duracao <= 0) $erros[] = 'Duração deve ser maior que zero';
            if ($valor_total <= 0) $erros[] = 'Valor deve ser maior que zero';
            
            if (!empty($erros)) {
                sendJson([
                    'success' => false, 
                    'message' => 'Dados inválidos: ' . implode(', ', $erros),
                    'dados_recebidos' => [
                        'id' => $id,
                        'nome' => $nome,
                        'categoria' => $categoria,
                        'descricao_preenchida' => !empty($descricao),
                        'duracao' => $duracao,
                        'valor_total' => $valor_total
                    ]
                ], 400);
            }
            
            $stmt = $pdo->prepare("UPDATE cursos SET nome = ?, categoria = ?, descricao = ?, duracao = ?, valor_total = ?, ativo = ? WHERE id = ?");
            $result = $stmt->execute([$nome, $categoria, $descricao, $duracao, $valor_total, $ativo, $id]);
            
            if ($result) {
                sendJson([
                    'success' => true, 
                    'message' => 'Curso atualizado com sucesso!',
                    'linhas_afetadas' => $stmt->rowCount()
                ]);
            } else {
                sendJson(['success' => false, 'message' => 'Erro ao atualizar curso'], 500);
            }
            break;
            
        case 'delete':
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            
            if (!$id) {
                sendJson(['success' => false, 'message' => 'ID não fornecido'], 400);
            }
            
            // Verificar se há turmas usando este curso
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM turmas_cursos WHERE curso_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                sendJson(['success' => false, 'message' => 'Não é possível deletar: existem turmas usando este curso'], 400);
            }
            
            $stmt = $pdo->prepare("DELETE FROM cursos WHERE id = ?");
            $stmt->execute([$id]);
            
            sendJson(['success' => true, 'message' => 'Curso deletado com sucesso']);
            break;
            
        case 'create':
            $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
            $categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : '';
            $descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
            $duracao = isset($_POST['duracao']) ? intval($_POST['duracao']) : 0;
            $valor_total = isset($_POST['valor_total']) ? floatval(str_replace(',', '.', $_POST['valor_total'])) : 0;
            
            // Validação
            $erros = [];
            if (empty($nome)) $erros[] = 'Nome é obrigatório';
            if (empty($categoria)) $erros[] = 'Categoria é obrigatória';
            if (empty($descricao)) $erros[] = 'Descrição é obrigatória';
            if ($duracao <= 0) $erros[] = 'Duração deve ser maior que zero';
            if ($valor_total <= 0) $erros[] = 'Valor deve ser maior que zero';
            
            if (!empty($erros)) {
                sendJson(['success' => false, 'message' => 'Dados inválidos: ' . implode(', ', $erros)], 400);
            }
            
            $stmt = $pdo->prepare("INSERT INTO cursos (nome, categoria, descricao, duracao, valor_total, ativo) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute([$nome, $categoria, $descricao, $duracao, $valor_total]);
            
            sendJson([
                'success' => true, 
                'message' => 'Curso criado com sucesso!',
                'id' => $pdo->lastInsertId()
            ]);
            break;
            
        case 'categorias':
            $stmt = $pdo->query("SELECT DISTINCT categoria FROM cursos ORDER BY categoria");
            $categorias = $stmt->fetchAll(PDO::FETCH_COLUMN);
            sendJson(['success' => true, 'data' => $categorias]);
            break;
            
        default:
            sendJson(['success' => false, 'message' => 'Ação não reconhecida: ' . $action], 400);
    }
    
} catch (PDOException $e) {
    error_log("Erro PDO na API de cursos: " . $e->getMessage());
    sendJson(['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    error_log("Erro na API de cursos: " . $e->getMessage());
    sendJson(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
}
