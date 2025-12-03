<?php
/**
 * API para gestão de avaliações físicas
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
                // Listar todas as avaliações (admin)
                $usuario_avaliacao = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : null;

                $sql = "
                    SELECT a.*, u.nome as usuario_nome, u.email as usuario_email,
                           av.nome as avaliador_nome
                    FROM avaliacoes_fisicas a
                    JOIN usuarios u ON a.usuario_id = u.id
                    LEFT JOIN usuarios av ON a.avaliador_id = av.id
                    WHERE a.ativo = 1
                ";

                $params = [];
                if ($usuario_avaliacao) {
                    $sql .= " AND a.usuario_id = ?";
                    $params[] = $usuario_avaliacao;
                }

                $sql .= " ORDER BY a.data_avaliacao DESC LIMIT 100";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $avaliacoes = $stmt->fetchAll();

                echo json_encode(['success' => true, 'data' => $avaliacoes]);
                break;
            } elseif ($action === 'minhas_avaliacoes') {
                // Avaliações do próprio usuário
                $stmt = $pdo->prepare("
                    SELECT a.*, u.nome as avaliador_nome
                    FROM avaliacoes_fisicas a
                    LEFT JOIN usuarios u ON a.avaliador_id = u.id
                    WHERE a.usuario_id = ? AND a.ativo = 1
                    ORDER BY a.data_avaliacao DESC
                ");
                $stmt->execute([$usuario_id]);
                $avaliacoes = $stmt->fetchAll();
                
                echo json_encode(['success' => true, 'data' => $avaliacoes]);
                break;
                
            } elseif ($action === 'usuario' && isset($_GET['usuario_id']) && $is_admin) {
                // Avaliações de um usuário específico (admin)
                $usuario_avaliacao = intval($_GET['usuario_id']);
                $stmt = $pdo->prepare("
                    SELECT a.*, u.nome as avaliador_nome
                    FROM avaliacoes_fisicas a
                    LEFT JOIN usuarios u ON a.avaliador_id = u.id
                    WHERE a.usuario_id = ? AND a.ativo = 1
                    ORDER BY a.data_avaliacao DESC
                ");
                $stmt->execute([$usuario_avaliacao]);
                $avaliacoes = $stmt->fetchAll();
                
                echo json_encode(['success' => true, 'data' => $avaliacoes]);
                break;
                
            } elseif ($action === 'get' && isset($_GET['id'])) {
                // Obter uma avaliação específica
                $id = intval($_GET['id']);
                $stmt = $pdo->prepare("
                    SELECT a.*, u.nome as avaliador_nome, u2.nome as usuario_nome
                    FROM avaliacoes_fisicas a
                    LEFT JOIN usuarios u ON a.avaliador_id = u.id
                    JOIN usuarios u2 ON a.usuario_id = u2.id
                    WHERE a.id = ?
                ");
                $stmt->execute([$id]);
                $avaliacao = $stmt->fetch();
                
                if (!$avaliacao) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Avaliação não encontrada']);
                    break;
                }
                
                // Verificar permissão
                if (!$is_admin && $avaliacao['usuario_id'] != $usuario_id) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                    break;
                }
                
                echo json_encode(['success' => true, 'data' => $avaliacao]);
                break;
            }
            break;
            
        case 'POST':
            // Criar nova avaliação (apenas admin)
            if (!$is_admin) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                break;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['usuario_id']) || !isset($data['data_avaliacao'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
                break;
            }
            
            // Calcular IMC se peso e altura estiverem presentes
            $imc = null;
            if (isset($data['peso']) && isset($data['altura']) && $data['peso'] > 0 && $data['altura'] > 0) {
                $imc = $data['peso'] / ($data['altura'] * $data['altura']);
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO avaliacoes_fisicas 
                (usuario_id, avaliador_id, data_avaliacao, peso, altura, imc,
                 percentual_gordura, percentual_massa_magra,
                 circunferencia_peito, circunferencia_cintura, circunferencia_quadril,
                 circunferencia_braco, circunferencia_coxa,
                 pressao_arterial_sistolica, pressao_arterial_diastolica,
                 frequencia_cardiaca_repouso, flexibilidade_cm, forca_abdominal,
                 resistencia_cardiovascular, observacoes, proxima_avaliacao)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['usuario_id'],
                $usuario_id, // avaliador
                $data['data_avaliacao'],
                $data['peso'] ?? null,
                $data['altura'] ?? null,
                $imc,
                $data['percentual_gordura'] ?? null,
                $data['percentual_massa_magra'] ?? null,
                $data['circunferencia_peito'] ?? null,
                $data['circunferencia_cintura'] ?? null,
                $data['circunferencia_quadril'] ?? null,
                $data['circunferencia_braco'] ?? null,
                $data['circunferencia_coxa'] ?? null,
                $data['pressao_arterial_sistolica'] ?? null,
                $data['pressao_arterial_diastolica'] ?? null,
                $data['frequencia_cardiaca_repouso'] ?? null,
                $data['flexibilidade_cm'] ?? null,
                $data['forca_abdominal'] ?? null,
                $data['resistencia_cardiovascular'] ?? null,
                $data['observacoes'] ?? null,
                $data['proxima_avaliacao'] ?? null
            ]);
            
            // Criar notificação para o usuário
            $stmt = $pdo->prepare("
                INSERT INTO notificacoes 
                (usuario_id, tipo, titulo, mensagem) 
                VALUES (?, 'avaliacao_pendente', 'Nova Avaliação Física', 
                        CONCAT('Uma nova avaliação física foi registrada em ', DATE_FORMAT(?, '%d/%m/%Y')))
            ");
            $stmt->execute([$data['usuario_id'], $data['data_avaliacao']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Avaliação física registrada com sucesso',
                'id' => $pdo->lastInsertId()
            ]);
            break;
            
        case 'PUT':
            // Atualizar avaliação (apenas admin)
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
            
            // Recalcular IMC se peso ou altura mudaram
            $imc = null;
            if (isset($data['peso']) && isset($data['altura']) && $data['peso'] > 0 && $data['altura'] > 0) {
                $imc = $data['peso'] / ($data['altura'] * $data['altura']);
            } elseif (isset($data['peso']) || isset($data['altura'])) {
                // Buscar valores existentes se não foram fornecidos
                $stmt = $pdo->prepare("SELECT peso, altura FROM avaliacoes_fisicas WHERE id = ?");
                $stmt->execute([$data['id']]);
                $existente = $stmt->fetch();
                
                $peso = $data['peso'] ?? $existente['peso'];
                $altura = $data['altura'] ?? $existente['altura'];
                
                if ($peso > 0 && $altura > 0) {
                    $imc = $peso / ($altura * $altura);
                }
            }
            
            $campos = [];
            $valores = [];
            
            $campos_permitidos = [
                'data_avaliacao', 'peso', 'altura', 'percentual_gordura', 'percentual_massa_magra',
                'circunferencia_peito', 'circunferencia_cintura', 'circunferencia_quadril',
                'circunferencia_braco', 'circunferencia_coxa',
                'pressao_arterial_sistolica', 'pressao_arterial_diastolica',
                'frequencia_cardiaca_repouso', 'flexibilidade_cm', 'forca_abdominal',
                'resistencia_cardiovascular', 'observacoes', 'proxima_avaliacao'
            ];
            
            foreach ($campos_permitidos as $campo) {
                if (isset($data[$campo])) {
                    $campos[] = "$campo = ?";
                    $valores[] = $data[$campo];
                }
            }
            
            if (isset($imc)) {
                $campos[] = "imc = ?";
                $valores[] = $imc;
            }
            
            if (empty($campos)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Nenhum campo para atualizar']);
                break;
            }
            
            $valores[] = $data['id'];
            
            $stmt = $pdo->prepare("
                UPDATE avaliacoes_fisicas 
                SET " . implode(', ', $campos) . " 
                WHERE id = ?
            ");
            $stmt->execute($valores);
            
            echo json_encode(['success' => true, 'message' => 'Avaliação atualizada com sucesso']);
            break;
            
        case 'DELETE':
            // Desativar avaliação (apenas admin)
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
            
            $stmt = $pdo->prepare("UPDATE avaliacoes_fisicas SET ativo = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Avaliação removida com sucesso']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    }
    
} catch (PDOException $e) {
    error_log("Erro PDO na API de avaliações: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro no banco de dados']);
} catch (Exception $e) {
    error_log("Erro na API de avaliações: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}

