<?php
/**
 * API pública para visualizar aulas de uma turma
 * Usuários podem ver o calendário de aulas das turmas em que estão matriculados
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
        // Se buscar uma aula específica por ID
        if (isset($_GET['id'])) {
            $aula_id = intval($_GET['id']);
            $stmt = $pdo->prepare("
                SELECT a.*, t.id as turma_id, t.nome_turma
                FROM aulas_agendadas a
                JOIN turmas_cursos t ON a.turma_id = t.id
                WHERE a.id = ?
            ");
            $stmt->execute([$aula_id]);
            $aula = $stmt->fetch();
            
            if (!$aula) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Aula não encontrada']);
                exit;
            }
            
            echo json_encode([
                'success' => true,
                'aula' => $aula
            ]);
            exit;
        }
        
        if (!isset($_GET['turma_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID da turma não fornecido']);
            exit;
        }

        $turma_id = $_GET['turma_id'];
        
        // Buscar informações da turma
        $stmt = $pdo->prepare("
            SELECT t.*, c.nome as curso_nome, c.categoria, c.descricao as curso_descricao,
                   u.nome as unidade_nome, u.cidade, u.endereco
            FROM turmas_cursos t
            JOIN cursos c ON t.curso_id = c.id
            JOIN unidades u ON t.unidade_id = u.id
            WHERE t.id = ? AND t.ativo = 1
        ");
        $stmt->execute([$turma_id]);
        $turma = $stmt->fetch();

        if (!$turma) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Turma não encontrada']);
            exit;
        }

        // Se o usuário estiver logado, verificar se está matriculado
        $pode_ver_detalhes = true;
        if (isset($_SESSION['usuario_id'])) {
            $usuario_id = $_SESSION['usuario_id'];
            $is_admin = isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin';
            
            if (!$is_admin) {
                // Verificar se está matriculado
                $stmt = $pdo->prepare("
                    SELECT id FROM matriculas 
                    WHERE usuario_id = ? AND turma_id = ? AND status != 'cancelada'
                ");
                $stmt->execute([$usuario_id, $turma_id]);
                $pode_ver_detalhes = $stmt->fetch() ? true : false;
            }
        }

        // Buscar aulas da turma
        $stmt = $pdo->prepare("
            SELECT * FROM aulas_agendadas 
            WHERE turma_id = ? AND ativo = 1 AND status != 'cancelada'
            ORDER BY data_aula, hora_inicio
        ");
        $stmt->execute([$turma_id]);
        $aulas = $stmt->fetchAll();

        // Se o usuário estiver matriculado, incluir informações de presença
        if ($pode_ver_detalhes && isset($_SESSION['usuario_id'])) {
            $stmt = $pdo->prepare("
                SELECT id FROM matriculas 
                WHERE usuario_id = ? AND turma_id = ? AND status != 'cancelada'
            ");
            $stmt->execute([$_SESSION['usuario_id'], $turma_id]);
            $matricula = $stmt->fetch();

            if ($matricula) {
                // Verificar se a tabela presencas existe
                try {
                    $checkTable = $pdo->query("SHOW TABLES LIKE 'presencas'");
                    if ($checkTable->rowCount() > 0) {
                        foreach ($aulas as &$aula) {
                            $stmt = $pdo->prepare("
                                SELECT presente FROM presencas 
                                WHERE matricula_id = ? AND aula_id = ?
                            ");
                            $stmt->execute([$matricula['id'], $aula['id']]);
                            $presenca = $stmt->fetch();
                            
                            $aula['presente'] = $presenca ? $presenca['presente'] : null;
                        }
                    } else {
                        // Tabela não existe, setar presente como null
                        foreach ($aulas as &$aula) {
                            $aula['presente'] = null;
                        }
                    }
                } catch (Exception $e) {
                    // Em caso de erro, continuar sem presenças
                    foreach ($aulas as &$aula) {
                        $aula['presente'] = null;
                    }
                }
            }
        }

        echo json_encode([
            'success' => true,
            'turma' => $turma,
            'aulas' => $aulas,
            'pode_ver_detalhes' => $pode_ver_detalhes
        ]);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
