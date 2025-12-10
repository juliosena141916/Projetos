<?php
/**
 * API para gestão de matrículas
 * Usuários podem se matricular e visualizar suas matrículas
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

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado. Faça login.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
try {
    $pdo = getConexao();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados: ' . $e->getMessage()]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$is_admin = isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin';

try {
    switch ($method) {
        case 'GET':
            // Se for para listar lista de espera do usuário
            if (isset($_GET['action']) && $_GET['action'] === 'lista_espera') {
                $stmt = $pdo->prepare("
                    SELECT le.*, t.nome_turma, t.data_inicio, t.data_fim,
                           c.nome as curso_nome, u.nome as unidade_nome
                    FROM lista_espera le
                    JOIN turmas_cursos t ON le.turma_id = t.id
                    JOIN cursos c ON t.curso_id = c.id
                    JOIN unidades u ON t.unidade_id = u.id
                    WHERE le.usuario_id = ? AND le.status = 'ativa'
                    ORDER BY le.prioridade ASC, le.data_inscricao ASC
                ");
                $stmt->execute([$usuario_id]);
                $lista_espera = $stmt->fetchAll();
                
                echo json_encode(['success' => true, 'data' => $lista_espera]);
                break;
            }
            
            if ($is_admin && isset($_GET['todas'])) {
                // Admin pode ver todas as matrículas
                $stmt = $pdo->query("
                    SELECT m.*, u.nome as usuario_nome, u.email,
                           t.nome_turma, c.nome as curso_nome,
                           un.nome as unidade_nome
                    FROM matriculas m
                    JOIN usuarios u ON m.usuario_id = u.id
                    JOIN turmas_cursos t ON m.turma_id = t.id
                    JOIN cursos c ON t.curso_id = c.id
                    JOIN unidades un ON t.unidade_id = un.id
                    ORDER BY m.data_matricula DESC
                ");
                $matriculas = $stmt->fetchAll();
            } elseif (isset($_GET['turma_id'])) {
                // Verificar matrículas de uma turma específica (para o usuário)
                $stmt = $pdo->prepare("
                    SELECT m.* FROM matriculas m
                    WHERE m.usuario_id = ? AND m.turma_id = ?
                ");
                $stmt->execute([$usuario_id, $_GET['turma_id']]);
                $matriculas = $stmt->fetchAll();
            } else {
                // Buscar matrículas do usuário logado
                $stmt = $pdo->prepare("
                    SELECT m.*, t.nome_turma, t.data_inicio, t.data_fim, t.status as turma_status,
                           c.id as curso_id, c.nome as curso_nome, c.categoria, c.duracao,
                           u.nome as unidade_nome, u.cidade, u.endereco,
                           (SELECT COUNT(*) FROM aulas_agendadas WHERE turma_id = t.id) as total_aulas
                    FROM matriculas m
                    JOIN turmas_cursos t ON m.turma_id = t.id
                    JOIN cursos c ON t.curso_id = c.id
                    JOIN unidades u ON t.unidade_id = u.id
                    WHERE m.usuario_id = ?
                    ORDER BY m.data_matricula DESC
                ");
                $stmt->execute([$usuario_id]);
                $matriculas = $stmt->fetchAll();
            }
            
            echo json_encode(['success' => true, 'matriculas' => $matriculas]);
            break;

        case 'POST':
            // Realizar matrícula
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['turma_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Turma não especificada']);
                break;
            }
            
            // Verificar se a turma existe e tem vagas
            $stmt = $pdo->prepare("
                SELECT t.*, c.valor_total 
                FROM turmas_cursos t
                JOIN cursos c ON t.curso_id = c.id
                WHERE t.id = ? AND t.ativo = 1
            ");
            $stmt->execute([$data['turma_id']]);
            $turma = $stmt->fetch();
            
            if (!$turma) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Turma não encontrada ou inativa']);
                break;
            }
            
            if ($turma['vagas_disponiveis'] <= 0) {
                // Verificar se já está na lista de espera
                $stmt = $pdo->prepare("
                    SELECT id FROM lista_espera 
                    WHERE usuario_id = ? AND turma_id = ? AND status = 'ativa'
                ");
                $stmt->execute([$usuario_id, $data['turma_id']]);
                
                if ($stmt->fetch()) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Você já está na lista de espera desta turma']);
                    break;
                }
                
                // Adicionar à lista de espera
                $stmt = $pdo->prepare("
                    SELECT COALESCE(MAX(prioridade), 0) + 1 as nova_prioridade 
                    FROM lista_espera 
                    WHERE turma_id = ? AND status = 'ativa'
                ");
                $stmt->execute([$data['turma_id']]);
                $result = $stmt->fetch();
                $prioridade = $result['nova_prioridade'];
                
                $stmt = $pdo->prepare("
                    INSERT INTO lista_espera (usuario_id, turma_id, prioridade, status) 
                    VALUES (?, ?, ?, 'ativa')
                ");
                $stmt->execute([$usuario_id, $data['turma_id'], $prioridade]);
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Turma sem vagas disponíveis. Você foi adicionado à lista de espera!',
                    'lista_espera' => true,
                    'prioridade' => $prioridade
                ]);
                break;
            }
            
            // Verificar se já está matriculado
            $stmt = $pdo->prepare("
                SELECT id FROM matriculas 
                WHERE usuario_id = ? AND turma_id = ? AND status != 'cancelada'
            ");
            $stmt->execute([$usuario_id, $data['turma_id']]);
            
            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Você já está matriculado nesta turma']);
                break;
            }
            
            // Iniciar transação
            $pdo->beginTransaction();
            
            try {
                // Criar matrícula
                $stmt = $pdo->prepare("
                    INSERT INTO matriculas 
                    (usuario_id, turma_id, status, valor_pago, forma_pagamento)
                    VALUES (?, ?, 'confirmada', ?, ?)
                ");
                
                $stmt->execute([
                    $usuario_id,
                    $data['turma_id'],
                    $turma['valor_total'],
                    $data['forma_pagamento'] ?? 'a_definir'
                ]);
                
                $matricula_id = $pdo->lastInsertId();
                
                // Atualizar vagas disponíveis
                $stmt = $pdo->prepare("
                    UPDATE turmas_cursos 
                    SET vagas_disponiveis = vagas_disponiveis - 1 
                    WHERE id = ?
                ");
                $stmt->execute([$data['turma_id']]);
                
                // Remover da lista de espera se estiver
                $stmt = $pdo->prepare("
                    UPDATE lista_espera 
                    SET status = 'atendida' 
                    WHERE usuario_id = ? AND turma_id = ? AND status = 'ativa'
                ");
                $stmt->execute([$usuario_id, $data['turma_id']]);
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Matrícula realizada com sucesso!',
                    'matricula_id' => $matricula_id
                ]);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'PUT':
            // Atualizar matrícula (cancelar, etc)
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
                break;
            }
            
            // Verificar se a matrícula pertence ao usuário (ou se é admin)
            $stmt = $pdo->prepare("SELECT * FROM matriculas WHERE id = ?");
            $stmt->execute([$data['id']]);
            $matricula = $stmt->fetch();
            
            if (!$matricula) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Matrícula não encontrada']);
                break;
            }
            
            if (!$is_admin && $matricula['usuario_id'] != $usuario_id) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                break;
            }
            
            // Se for cancelamento, liberar vaga
            if (isset($data['status']) && $data['status'] === 'cancelada' && $matricula['status'] !== 'cancelada') {
                $pdo->beginTransaction();
                
                try {
                    $stmt = $pdo->prepare("UPDATE matriculas SET status = 'cancelada' WHERE id = ?");
                    $stmt->execute([$data['id']]);
                    
                    $stmt = $pdo->prepare("
                        UPDATE turmas_cursos 
                        SET vagas_disponiveis = vagas_disponiveis + 1 
                        WHERE id = ?
                    ");
                    $stmt->execute([$matricula['turma_id']]);
                    
                    $pdo->commit();
                    
                    echo json_encode(['success' => true, 'message' => 'Matrícula cancelada com sucesso']);
                } catch (Exception $e) {
                    $pdo->rollBack();
                    throw $e;
                }
            } else {
                // Outras atualizações
                $updates = [];
                $params = [];
                $allowed_fields = $is_admin ? ['status', 'valor_pago', 'forma_pagamento', 'observacoes'] : ['observacoes'];
                
                foreach ($allowed_fields as $field) {
                    if (isset($data[$field])) {
                        $updates[] = "$field = ?";
                        $params[] = $data[$field];
                    }
                }
                
                if (!empty($updates)) {
                    $params[] = $data['id'];
                    $sql = "UPDATE matriculas SET " . implode(', ', $updates) . " WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                }
                
                echo json_encode(['success' => true, 'message' => 'Matrícula atualizada com sucesso']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
