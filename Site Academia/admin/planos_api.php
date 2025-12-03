<?php
/**
 * API para gerenciamento de planos
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
            $stmt = $pdo->query("SELECT * FROM planos ORDER BY valor_mensal ASC");
            $planos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendJson(['success' => true, 'data' => $planos]);
            break;
            
        case 'get':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if (!$id) {
                sendJson(['success' => false, 'message' => 'ID não fornecido'], 400);
            }
            
            $stmt = $pdo->prepare("SELECT * FROM planos WHERE id = ?");
            $stmt->execute([$id]);
            $plano = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$plano) {
                sendJson(['success' => false, 'message' => 'Plano não encontrado'], 404);
            }
            
            sendJson(['success' => true, 'data' => $plano]);
            break;
            
        case 'create':
            $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
            $descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
            $valor_mensal = isset($_POST['valor_mensal']) ? floatval(str_replace(',', '.', $_POST['valor_mensal'])) : 0;
            $acesso_academia = isset($_POST['acesso_academia']) ? 1 : 0;
            $acesso_musculacao = isset($_POST['acesso_musculacao']) ? 1 : 0;
            $acesso_todas_unidades = isset($_POST['acesso_todas_unidades']) ? 1 : 0;
            $acesso_todos_cursos = isset($_POST['acesso_todos_cursos']) ? 1 : 0;
            $quantidade_cursos = isset($_POST['quantidade_cursos']) ? intval($_POST['quantidade_cursos']) : 0;
            $aulas_grupais_ilimitadas = isset($_POST['aulas_grupais_ilimitadas']) ? 1 : 0;
            $personal_trainer = isset($_POST['personal_trainer']) ? 1 : 0;
            $nutricionista = isset($_POST['nutricionista']) ? 1 : 0;
            $avaliacao_fisica = isset($_POST['avaliacao_fisica']) ? 1 : 0;
            $app_exclusivo = isset($_POST['app_exclusivo']) ? 1 : 0;
            $desconto_loja = isset($_POST['desconto_loja']) ? 1 : 0;
            $ativo = isset($_POST['ativo']) ? intval($_POST['ativo']) : 1;
            
            // Validação
            $erros = [];
            if (empty($nome)) $erros[] = 'Nome é obrigatório';
            if ($valor_mensal <= 0) $erros[] = 'Valor mensal deve ser maior que zero';
            
            if (!empty($erros)) {
                sendJson(['success' => false, 'message' => 'Dados inválidos: ' . implode(', ', $erros)], 400);
            }
            
            $stmt = $pdo->prepare("INSERT INTO planos (nome, descricao, valor_mensal, acesso_academia, acesso_musculacao, acesso_todas_unidades, acesso_todos_cursos, quantidade_cursos, aulas_grupais_ilimitadas, personal_trainer, nutricionista, avaliacao_fisica, app_exclusivo, desconto_loja, ativo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $nome, $descricao, $valor_mensal, $acesso_academia, $acesso_musculacao,
                $acesso_todas_unidades, $acesso_todos_cursos, $quantidade_cursos,
                $aulas_grupais_ilimitadas, $personal_trainer, $nutricionista,
                $avaliacao_fisica, $app_exclusivo, $desconto_loja, $ativo
            ]);
            
            sendJson([
                'success' => true, 
                'message' => 'Plano criado com sucesso!',
                'id' => $pdo->lastInsertId()
            ]);
            break;
            
        case 'update':
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
            $descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
            $valor_mensal = isset($_POST['valor_mensal']) ? floatval(str_replace(',', '.', $_POST['valor_mensal'])) : 0;
            $acesso_academia = isset($_POST['acesso_academia']) ? 1 : 0;
            $acesso_musculacao = isset($_POST['acesso_musculacao']) ? 1 : 0;
            $acesso_todas_unidades = isset($_POST['acesso_todas_unidades']) ? 1 : 0;
            $acesso_todos_cursos = isset($_POST['acesso_todos_cursos']) ? 1 : 0;
            $quantidade_cursos = isset($_POST['quantidade_cursos']) ? intval($_POST['quantidade_cursos']) : 0;
            $aulas_grupais_ilimitadas = isset($_POST['aulas_grupais_ilimitadas']) ? 1 : 0;
            $personal_trainer = isset($_POST['personal_trainer']) ? 1 : 0;
            $nutricionista = isset($_POST['nutricionista']) ? 1 : 0;
            $avaliacao_fisica = isset($_POST['avaliacao_fisica']) ? 1 : 0;
            $app_exclusivo = isset($_POST['app_exclusivo']) ? 1 : 0;
            $desconto_loja = isset($_POST['desconto_loja']) ? 1 : 0;
            $ativo = isset($_POST['ativo']) ? intval($_POST['ativo']) : 1;
            
            // Validação
            $erros = [];
            if (!$id) $erros[] = 'ID inválido';
            if (empty($nome)) $erros[] = 'Nome é obrigatório';
            if ($valor_mensal <= 0) $erros[] = 'Valor mensal deve ser maior que zero';
            
            if (!empty($erros)) {
                sendJson(['success' => false, 'message' => 'Dados inválidos: ' . implode(', ', $erros)], 400);
            }
            
            $stmt = $pdo->prepare("UPDATE planos SET nome = ?, descricao = ?, valor_mensal = ?, acesso_academia = ?, acesso_musculacao = ?, acesso_todas_unidades = ?, acesso_todos_cursos = ?, quantidade_cursos = ?, aulas_grupais_ilimitadas = ?, personal_trainer = ?, nutricionista = ?, avaliacao_fisica = ?, app_exclusivo = ?, desconto_loja = ?, ativo = ? WHERE id = ?");
            $result = $stmt->execute([
                $nome, $descricao, $valor_mensal, $acesso_academia, $acesso_musculacao,
                $acesso_todas_unidades, $acesso_todos_cursos, $quantidade_cursos,
                $aulas_grupais_ilimitadas, $personal_trainer, $nutricionista,
                $avaliacao_fisica, $app_exclusivo, $desconto_loja, $ativo, $id
            ]);
            
            if ($result) {
                sendJson([
                    'success' => true, 
                    'message' => 'Plano atualizado com sucesso!',
                    'linhas_afetadas' => $stmt->rowCount()
                ]);
            } else {
                sendJson(['success' => false, 'message' => 'Erro ao atualizar plano'], 500);
            }
            break;
            
        case 'delete':
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            
            if (!$id) {
                sendJson(['success' => false, 'message' => 'ID não fornecido'], 400);
            }
            
            // Verificar se há usuários usando este plano (se houver tabela de assinaturas)
            // Por enquanto, apenas deletar
            $stmt = $pdo->prepare("DELETE FROM planos WHERE id = ?");
            $stmt->execute([$id]);
            
            sendJson(['success' => true, 'message' => 'Plano deletado com sucesso']);
            break;
            
        default:
            sendJson(['success' => false, 'message' => 'Ação não reconhecida: ' . $action], 400);
    }
    
} catch (PDOException $e) {
    error_log("Erro PDO na API de planos: " . $e->getMessage());
    sendJson(['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    error_log("Erro na API de planos: " . $e->getMessage());
    sendJson(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
}

