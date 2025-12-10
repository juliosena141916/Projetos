<?php
/**
 * API para gestão de aulas agendadas
 * Apenas para administradores
 */

// Iniciar output buffering para capturar qualquer aviso/erro
ob_start();

// Iniciar sessão apenas se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir arquivo de conexão de forma segura
$conexao_path = dirname(__DIR__) . '/includes/conexao.php';
if (!file_exists($conexao_path)) {
    ob_end_clean(); // Limpar buffer
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro: Arquivo de conexão não encontrado']);
    exit;
}
require_once $conexao_path;
require_once __DIR__ . '/check_admin.php';

// Função helper para limpar buffer e enviar JSON
function cleanAndSendJson($data, $statusCode = 200) {
    ob_clean(); // Limpar qualquer output anterior (avisos, etc)
    if ($statusCode !== 200) {
        http_response_code($statusCode);
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
}

// Limpar qualquer output gerado pelos includes (avisos, etc)
ob_clean();

// Definir header JSON
header('Content-Type: application/json; charset=utf-8');

// Verificar se é admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getConexao();

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['turma_id'])) {
                // Buscar aulas de uma turma específica
                $turma_id = intval($_GET['turma_id']);
                
                if ($turma_id <= 0) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'ID da turma inválido']);
                    break;
                }
                
                try {
                    $stmt = $pdo->prepare("
                        SELECT aa.*, tc.nome_turma
                        FROM aulas_agendadas aa
                        JOIN turmas_cursos tc ON aa.turma_id = tc.id
                        WHERE aa.turma_id = ?
                        ORDER BY aa.data_aula, aa.hora_inicio
                    ");
                    $stmt->execute([$turma_id]);
                    $aulas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Garantir que todos os campos necessários existam
                    if (is_array($aulas)) {
                        foreach ($aulas as &$aula) {
                            if (!is_array($aula)) continue;
                            
                            $aula['id'] = isset($aula['id']) ? intval($aula['id']) : 0;
                            $aula['data_aula'] = isset($aula['data_aula']) ? strval($aula['data_aula']) : '';
                            $aula['hora_inicio'] = isset($aula['hora_inicio']) ? strval($aula['hora_inicio']) : null;
                            $aula['hora_fim'] = isset($aula['hora_fim']) ? strval($aula['hora_fim']) : null;
                            $aula['status'] = isset($aula['status']) ? strval($aula['status']) : 'agendada';
                            $aula['sala'] = isset($aula['sala']) ? strval($aula['sala']) : null;
                            $aula['observacoes'] = isset($aula['observacoes']) ? strval($aula['observacoes']) : null;
                            $aula['nome_turma'] = isset($aula['nome_turma']) ? strval($aula['nome_turma']) : '';
                        }
                        unset($aula);
                    } else {
                        $aulas = [];
                    }
                    
                    cleanAndSendJson(['success' => true, 'aulas' => $aulas]);
                    break;
                } catch (PDOException $e) {
                    error_log("Erro ao buscar aulas: " . $e->getMessage());
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Erro ao buscar aulas: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
                }
            } elseif (isset($_GET['curso_id'])) {
                // Buscar aulas de todas as turmas de um curso
                $stmt = $pdo->prepare("
                    SELECT aa.*, tc.nome_turma
                    FROM aulas_agendadas aa
                    JOIN turmas_cursos tc ON aa.turma_id = tc.id
                    WHERE tc.curso_id = ?
                    ORDER BY aa.data_aula, aa.hora_inicio
                ");
                $stmt->execute([$_GET['curso_id']]);
                $aulas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'aulas' => $aulas]);
            } elseif (isset($_GET['id'])) {
                // Buscar aula específica
                $stmt = $pdo->prepare("SELECT * FROM aulas_agendadas WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $aula = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($aula) {
                    echo json_encode(['success' => true, 'aula' => $aula]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Aula não encontrada']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
            }
            break;

        case 'POST':
            // Aceitar tanto JSON quanto FormData
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'application/json') !== false) {
                $data = json_decode(file_get_contents('php://input'), true);
            } else {
                $data = $_POST;
            }
            
            // Verificar se é uma ação de gerar aulas automaticamente
            if (isset($data['action']) && $data['action'] === 'gerar_aulas') {
                if (!isset($data['turma_id'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'ID da turma não fornecido']);
                    break;
                }
                
                // Buscar informações da turma
                $stmt = $pdo->prepare("
                    SELECT * FROM turmas_cursos WHERE id = ?
                ");
                $stmt->execute([$data['turma_id']]);
                $turma = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$turma) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Turma não encontrada']);
                    break;
                }
                
                if (!$turma['dias_semana'] || !$turma['hora_inicio'] || !$turma['hora_fim']) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Turma não possui dias da semana ou horários configurados']);
                    break;
                }
                
                // Converter dias da semana de string para array de inteiros
                $dias_semana_str = explode(',', $turma['dias_semana']);
                $dias_semana = array_map('intval', $dias_semana_str);
                
                // Gerar aulas entre data_inicio e data_fim
                $data_inicio = new DateTime($turma['data_inicio']);
                $data_fim = new DateTime($turma['data_fim']);
                $aulas_geradas = [];
                
                $stmt_insert = $pdo->prepare("
                    INSERT INTO aulas_agendadas 
                    (turma_id, data_aula, hora_inicio, hora_fim, sala, status)
                    VALUES (?, ?, ?, ?, ?, 'agendada')
                ");
                
                $pdo->beginTransaction();
                $count = 0;
                
                // Iterar por cada dia entre data_inicio e data_fim
                $current_date = clone $data_inicio;
                while ($current_date <= $data_fim) {
                    // Verificar se o dia da semana atual está na lista
                    // format('w') retorna 0=domingo, 1=segunda, etc (igual ao banco)
                    $dia_semana_atual = (int)$current_date->format('w');
                    
                    if (in_array($dia_semana_atual, $dias_semana, true)) {
                        // Verificar se já existe aula nesta data
                        $stmt_check = $pdo->prepare("
                            SELECT COUNT(*) as count FROM aulas_agendadas 
                            WHERE turma_id = ? AND data_aula = ?
                        ");
                        $stmt_check->execute([$turma['id'], $current_date->format('Y-m-d')]);
                        $exists = $stmt_check->fetch(PDO::FETCH_ASSOC);
                        
                        if ($exists['count'] == 0) {
                            $stmt_insert->execute([
                                $turma['id'],
                                $current_date->format('Y-m-d'),
                                $turma['hora_inicio'],
                                $turma['hora_fim'],
                                $turma['sala_padrao']
                            ]);
                            $count++;
                        }
                    }
                    
                    $current_date->modify('+1 day');
                }
                
                $pdo->commit();
                echo json_encode([
                    'success' => true,
                    'message' => "$count aulas geradas automaticamente",
                    'count' => $count
                ]);
                break;
            }
            
            // Criar aulas em lote ou individual
            if (isset($data['aulas']) && is_array($data['aulas'])) {
                // Criar múltiplas aulas
                $stmt = $pdo->prepare("
                    INSERT INTO aulas_agendadas 
                    (turma_id, data_aula, hora_inicio, hora_fim, sala, observacoes)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                $pdo->beginTransaction();
                $count = 0;
                
                foreach ($data['aulas'] as $aula) {
                    if (!isset($aula['turma_id'], $aula['data_aula'], $aula['hora_inicio'], $aula['hora_fim'])) {
                        continue;
                    }
                    
                    $stmt->execute([
                        $aula['turma_id'],
                        $aula['data_aula'],
                        $aula['hora_inicio'],
                        $aula['hora_fim'],
                        $aula['sala'] ?? null,
                        $aula['observacoes'] ?? null
                    ]);
                    $count++;
                }
                
                $pdo->commit();
                echo json_encode([
                    'success' => true, 
                    'message' => "$count aulas criadas com sucesso"
                ]);
            } else {
                // Criar aula individual
                if (!isset($data['turma_id'], $data['data_aula'], $data['hora_inicio'], $data['hora_fim'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
                    break;
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO aulas_agendadas 
                    (turma_id, data_aula, hora_inicio, hora_fim, sala, observacoes, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'agendada')
                ");
                
                $stmt->execute([
                    $data['turma_id'],
                    $data['data_aula'],
                    $data['hora_inicio'],
                    $data['hora_fim'],
                    $data['sala'] ?? null,
                    $data['observacoes'] ?? null
                ]);
                
                $aula_id = $pdo->lastInsertId();
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Aula criada com sucesso',
                    'aula_id' => $aula_id
                ]);
            }
            break;

        case 'PUT':
            // Atualizar aula
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'application/json') !== false) {
                $data = json_decode(file_get_contents('php://input'), true);
            } else {
                $data = $_POST;
            }
            
            if (!isset($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
                break;
            }
            
            $updates = [];
            $params = [];
            
            $allowed_fields = ['turma_id', 'data_aula', 'hora_inicio', 'hora_fim', 'sala', 
                              'observacoes', 'status', 'ativo'];
            
            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
            
            if (empty($updates)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Nenhum campo para atualizar']);
                break;
            }
            
            $params[] = $data['id'];
            $sql = "UPDATE aulas_agendadas SET " . implode(', ', $updates) . " WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            echo json_encode(['success' => true, 'message' => 'Aula atualizada com sucesso']);
            break;

        case 'DELETE':
            // Deletar aula
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
            } else {
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
                if (strpos($contentType, 'application/json') !== false) {
                    $data = json_decode(file_get_contents('php://input'), true);
                    $id = $data['id'] ?? null;
                } else {
                    $id = $_POST['id'] ?? null;
                }
            }
            
            if (!isset($id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
                break;
            }
            
            $stmt = $pdo->prepare("DELETE FROM aulas_agendadas WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Aula excluída com sucesso']);
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
    error_log("Erro na API de aulas: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
?>
