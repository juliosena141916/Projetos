<?php
/**
 * API para gestão de frequência em aulas
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
    echo json_encode(['success' => false, 'message' => 'Erro de conexão: ' . $e->getMessage()]);
    exit;
}

try {
    switch ($method) {
        case 'GET':
            $action = $_GET['action'] ?? '';
            
            if ($action === 'all' && $is_admin) {
                // Listar todas as frequências (admin)
                $turma_id = isset($_GET['turma_id']) ? intval($_GET['turma_id']) : null;
                $aula_id = isset($_GET['aula_id']) ? intval($_GET['aula_id']) : null;
                $data = isset($_GET['data']) ? $_GET['data'] : null;

                $sql = "
                    SELECT f.*, u.nome as usuario_nome, u.email as usuario_email,
                           a.data_aula, a.hora_inicio, a.hora_fim,
                           t.nome_turma, c.nome as curso_nome
                    FROM frequencia_aulas f
                    JOIN usuarios u ON f.usuario_id = u.id
                    JOIN aulas_agendadas a ON f.aula_id = a.id
                    JOIN turmas_cursos t ON a.turma_id = t.id
                    JOIN cursos c ON t.curso_id = c.id
                    WHERE 1=1
                ";

                $params = [];
                if ($turma_id) {
                    $sql .= " AND t.id = ?";
                    $params[] = $turma_id;
                }
                if ($aula_id) {
                    $sql .= " AND a.id = ?";
                    $params[] = $aula_id;
                }
                if ($data) {
                    $sql .= " AND a.data_aula = ?";
                    $params[] = $data;
                }

                $sql .= " ORDER BY a.data_aula DESC, a.hora_inicio DESC LIMIT 100";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $frequencia = $stmt->fetchAll();

                echo json_encode(['success' => true, 'data' => $frequencia]);
                break;
            } elseif ($action === 'minha_frequencia') {
                // Frequência do próprio usuário
                $stmt = $pdo->prepare("
                    SELECT f.*, a.data_aula, a.hora_inicio, a.hora_fim,
                           t.nome_turma, c.nome as curso_nome
                    FROM frequencia_aulas f
                    JOIN aulas_agendadas a ON f.aula_id = a.id
                    JOIN turmas_cursos t ON a.turma_id = t.id
                    JOIN cursos c ON t.curso_id = c.id
                    WHERE f.usuario_id = ?
                    ORDER BY a.data_aula DESC, a.hora_inicio DESC
                    LIMIT 50
                ");
                $stmt->execute([$usuario_id]);
                $frequencia = $stmt->fetchAll();
                
                echo json_encode(['success' => true, 'data' => $frequencia]);
                break;
                
            } elseif ($action === 'aula' && isset($_GET['aula_id'])) {
                // Frequência de uma aula específica (admin)
                if (!$is_admin) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                    break;
                }
                
                $aula_id = intval($_GET['aula_id']);
                $stmt = $pdo->prepare("
                    SELECT f.*, u.nome as usuario_nome, u.email as usuario_email
                    FROM frequencia_aulas f
                    JOIN usuarios u ON f.usuario_id = u.id
                    WHERE f.aula_id = ?
                    ORDER BY f.data_presenca DESC
                ");
                $stmt->execute([$aula_id]);
                $frequencia = $stmt->fetchAll();
                
                echo json_encode(['success' => true, 'data' => $frequencia]);
                break;
            }
            break;
            
        case 'POST':
            // Registrar frequência (admin ou próprio usuário)
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['aula_id']) || !isset($data['usuario_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
                break;
            }
            
            // Verificar se é admin ou se está registrando sua própria frequência
            $usuario_frequencia = intval($data['usuario_id']);
            if (!$is_admin && $usuario_frequencia != $usuario_id) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                break;
            }
            
            // Verificar se já existe registro
            $stmt = $pdo->prepare("
                SELECT id FROM frequencia_aulas 
                WHERE usuario_id = ? AND aula_id = ?
            ");
            $stmt->execute([$usuario_frequencia, $data['aula_id']]);
            
            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Frequência já registrada']);
                break;
            }
            
            // Verificar se o usuário está matriculado na turma
            $stmt = $pdo->prepare("
                SELECT m.id 
                FROM matriculas m
                JOIN aulas_agendadas a ON m.turma_id = a.turma_id
                WHERE m.usuario_id = ? AND a.id = ? AND m.status = 'confirmada'
            ");
            $stmt->execute([$usuario_frequencia, $data['aula_id']]);
            
            if (!$stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Usuário não matriculado nesta turma']);
                break;
            }
            
            // Inserir frequência
            $status = $data['status'] ?? 'presente';
            $observacoes = $data['observacoes'] ?? null;
            
            $stmt = $pdo->prepare("
                INSERT INTO frequencia_aulas 
                (usuario_id, aula_id, status, observacoes, registrado_por) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $usuario_frequencia,
                $data['aula_id'],
                $status,
                $observacoes,
                $is_admin ? $usuario_id : null
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Frequência registrada com sucesso',
                'id' => $pdo->lastInsertId()
            ]);
            break;
            
        case 'PUT':
            // Atualizar frequência
            if (!$is_admin) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                break;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
                break;
            }
            
            $campos = [];
            $valores = [];
            
            if (isset($data['status'])) {
                $campos[] = "status = ?";
                $valores[] = $data['status'];
            }
            
            if (isset($data['observacoes'])) {
                $campos[] = "observacoes = ?";
                $valores[] = $data['observacoes'];
            }
            
            if (empty($campos)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Nenhum campo para atualizar']);
                break;
            }
            
            $valores[] = $data['id'];
            
            $stmt = $pdo->prepare("
                UPDATE frequencia_aulas 
                SET " . implode(', ', $campos) . " 
                WHERE id = ?
            ");
            $stmt->execute($valores);
            
            echo json_encode(['success' => true, 'message' => 'Frequência atualizada com sucesso']);
            break;
            
        case 'DELETE':
            // Deletar frequência (apenas admin)
            if (!$is_admin) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                break;
            }
            
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
                break;
            }
            
            $stmt = $pdo->prepare("DELETE FROM frequencia_aulas WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Frequência removida com sucesso']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    }
    
} catch (PDOException $e) {
    error_log("Erro PDO na API de frequência: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro no banco de dados']);
} catch (Exception $e) {
    error_log("Erro na API de frequência: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}

