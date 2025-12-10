<?php
/**
 * API pública para listar turmas disponíveis
 * Usuários podem visualizar turmas disponíveis para matrícula
 */

// Prevenir output antes do JSON
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/conexao.php';

// Limpar qualquer output anterior
ob_clean();
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$method = $_SERVER['REQUEST_METHOD'];
try {
    $pdo = getConexao();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados: ' . $e->getMessage()]);
    exit;
}

try {
    if ($method === 'GET') {
        $where_conditions = ["t.ativo = 1", "t.vagas_disponiveis > 0"];
        $params = [];
        
        // Filtros opcionais
        if (isset($_GET['curso_id'])) {
            $where_conditions[] = "t.curso_id = ?";
            $params[] = $_GET['curso_id'];
        }
        
        if (isset($_GET['unidade_id'])) {
            $where_conditions[] = "t.unidade_id = ?";
            $params[] = $_GET['unidade_id'];
        }
        
        if (isset($_GET['categoria'])) {
            $where_conditions[] = "c.categoria = ?";
            $params[] = $_GET['categoria'];
        }
        
        if (isset($_GET['cidade'])) {
            $where_conditions[] = "u.cidade = ?";
            $params[] = $_GET['cidade'];
        }
        
        // Filtrar apenas turmas futuras ou em andamento
        $where_conditions[] = "t.data_fim >= CURDATE()";
        $where_conditions[] = "t.status IN ('planejada', 'em_andamento')";
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $sql = "
            SELECT t.*, 
                   c.nome as curso_nome, c.categoria, c.descricao as curso_descricao,
                   c.duracao, c.valor_total,
                   u.nome as unidade_nome, u.cidade, u.endereco, u.telefone,
                   (SELECT COUNT(*) FROM aulas_agendadas WHERE turma_id = t.id AND ativo = 1) as total_aulas,
                   (SELECT COUNT(*) FROM matriculas WHERE turma_id = t.id AND status != 'cancelada') as total_matriculados
            FROM turmas_cursos t
            JOIN cursos c ON t.curso_id = c.id
            JOIN unidades u ON t.unidade_id = u.id
            WHERE $where_clause
            ORDER BY t.data_inicio ASC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $turmas = $stmt->fetchAll();
        
        // Se o usuário estiver logado, verificar se já está matriculado
        if (isset($_SESSION['usuario_id'])) {
            $usuario_id = $_SESSION['usuario_id'];
            
            foreach ($turmas as &$turma) {
                $stmt = $pdo->prepare("
                    SELECT id, status FROM matriculas 
                    WHERE usuario_id = ? AND turma_id = ? AND status != 'cancelada'
                ");
                $stmt->execute([$usuario_id, $turma['id']]);
                $matricula = $stmt->fetch();
                
                $turma['ja_matriculado'] = $matricula ? true : false;
                $turma['matricula_status'] = $matricula ? $matricula['status'] : null;
            }
        }
        
        echo json_encode(['success' => true, 'turmas' => $turmas]);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
