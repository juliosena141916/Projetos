<?php
/**
 * API para relatórios gerenciais
 */

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/includes/conexao.php';

ob_clean();
header('Content-Type: application/json');

// Verificar se é admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

try {
    $pdo = getConexao();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro de conexão']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'estatisticas':
            // Estatísticas gerais
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
            $total_usuarios = $stmt->fetch()['total'];

            $stmt = $pdo->query("SELECT COUNT(*) as total FROM matriculas WHERE status = 'confirmada'");
            $total_matriculas = $stmt->fetch()['total'];

            // Taxa de ocupação
            $stmt = $pdo->query("
                SELECT 
                    SUM(vagas_totais) as total_vagas,
                    SUM(vagas_disponiveis) as vagas_disponiveis
                FROM turmas_cursos
                WHERE ativo = 1
            ");
            $ocupacao = $stmt->fetch();
            $vagas_ocupadas = $ocupacao['total_vagas'] - $ocupacao['vagas_disponiveis'];
            $taxa_ocupacao = $ocupacao['total_vagas'] > 0 ? 
                round(($vagas_ocupadas / $ocupacao['total_vagas']) * 100, 1) : 0;

            // Taxa de frequência
            $stmt = $pdo->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'presente' THEN 1 ELSE 0 END) as presentes
                FROM frequencia_aulas
            ");
            $frequencia = $stmt->fetch();
            $taxa_frequencia = $frequencia['total'] > 0 ? 
                round(($frequencia['presentes'] / $frequencia['total']) * 100, 1) : 0;

            echo json_encode([
                'success' => true,
                'data' => [
                    'total_usuarios' => $total_usuarios,
                    'total_matriculas' => $total_matriculas,
                    'taxa_ocupacao' => $taxa_ocupacao,
                    'taxa_frequencia' => $taxa_frequencia
                ]
            ]);
            break;

        case 'ocupacao':
            // Ocupação por turma
            $stmt = $pdo->query("
                SELECT 
                    t.id,
                    t.nome_turma,
                    t.vagas_totais,
                    t.vagas_disponiveis,
                    c.nome as curso_nome,
                    u.nome as unidade_nome
                FROM turmas_cursos t
                JOIN cursos c ON t.curso_id = c.id
                JOIN unidades u ON t.unidade_id = u.id
                WHERE t.ativo = 1
                ORDER BY t.nome_turma
            ");
            $turmas = $stmt->fetchAll();

            echo json_encode(['success' => true, 'data' => $turmas]);
            break;

        case 'frequencia_mensal':
            // Frequência mensal dos últimos 6 meses
            $stmt = $pdo->query("
                SELECT 
                    DATE_FORMAT(f.data_presenca, '%Y-%m') as mes,
                    DATE_FORMAT(f.data_presenca, '%b/%Y') as mes_formatado,
                    COUNT(*) as total,
                    SUM(CASE WHEN f.status = 'presente' THEN 1 ELSE 0 END) as presentes,
                    SUM(CASE WHEN f.status = 'ausente' THEN 1 ELSE 0 END) as ausentes
                FROM frequencia_aulas f
                WHERE f.data_presenca >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(f.data_presenca, '%Y-%m')
                ORDER BY mes DESC
                LIMIT 6
            ");
            $frequencia_mensal = $stmt->fetchAll();

            // Formatar para o gráfico
            $dados = array_map(function($item) {
                return [
                    'mes' => $item['mes_formatado'],
                    'presentes' => intval($item['presentes']),
                    'ausentes' => intval($item['ausentes'])
                ];
            }, $frequencia_mensal);

            echo json_encode(['success' => true, 'data' => array_reverse($dados)]);
            break;

        case 'frequencia_detalhada':
            // Estatísticas detalhadas de frequência
            $stmt = $pdo->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'presente' THEN 1 ELSE 0 END) as presentes,
                    SUM(CASE WHEN status = 'ausente' THEN 1 ELSE 0 END) as ausentes,
                    SUM(CASE WHEN status = 'justificado' THEN 1 ELSE 0 END) as justificados
                FROM frequencia_aulas
            ");
            $frequencia = $stmt->fetch();
            
            $total = intval($frequencia['total']);
            $presentes = intval($frequencia['presentes']);
            $ausentes = intval($frequencia['ausentes']);
            $justificados = intval($frequencia['justificados']);
            $taxa = $total > 0 ? round(($presentes / $total) * 100, 1) : 0;
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'presentes' => $presentes,
                    'ausentes' => $ausentes,
                    'justificados' => $justificados,
                    'taxa_frequencia' => $taxa
                ]
            ]);
            break;

        case 'frequencia_por_turma':
            // Frequência agrupada por turma
            $stmt = $pdo->query("
                SELECT 
                    t.id,
                    t.nome_turma,
                    c.nome as curso_nome,
                    COUNT(f.id) as total,
                    SUM(CASE WHEN f.status = 'presente' THEN 1 ELSE 0 END) as presentes,
                    SUM(CASE WHEN f.status = 'ausente' THEN 1 ELSE 0 END) as ausentes,
                    SUM(CASE WHEN f.status = 'justificado' THEN 1 ELSE 0 END) as justificados
                FROM turmas_cursos t
                LEFT JOIN aulas_agendadas a ON a.turma_id = t.id
                LEFT JOIN frequencia_aulas f ON f.aula_id = a.id
                JOIN cursos c ON t.curso_id = c.id
                WHERE t.ativo = 1
                GROUP BY t.id, t.nome_turma, c.nome
                HAVING total > 0
                ORDER BY t.nome_turma
            ");
            $frequencia_turmas = $stmt->fetchAll();
            
            // Calcular taxa de frequência para cada turma
            $dados = array_map(function($item) {
                $total = intval($item['total']);
                $presentes = intval($item['presentes']);
                $taxa = $total > 0 ? round(($presentes / $total) * 100, 1) : 0;
                
                return [
                    'id' => $item['id'],
                    'nome_turma' => $item['nome_turma'],
                    'curso_nome' => $item['curso_nome'],
                    'total' => $total,
                    'presentes' => $presentes,
                    'ausentes' => intval($item['ausentes']),
                    'justificados' => intval($item['justificados']),
                    'taxa_frequencia' => $taxa
                ];
            }, $frequencia_turmas);
            
            echo json_encode(['success' => true, 'data' => $dados]);
            break;

        case 'avaliacoes_estatisticas':
            // Estatísticas de avaliações físicas
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM avaliacoes_fisicas WHERE ativo = 1");
            $total_avaliacoes = $stmt->fetch()['total'];
            
            $stmt = $pdo->query("
                SELECT COUNT(*) as total 
                FROM avaliacoes_fisicas 
                WHERE ativo = 1 
                AND MONTH(data_avaliacao) = MONTH(NOW()) 
                AND YEAR(data_avaliacao) = YEAR(NOW())
            ");
            $avaliacoes_mes = $stmt->fetch()['total'];
            
            $stmt = $pdo->query("
                SELECT COUNT(*) as total 
                FROM avaliacoes_fisicas 
                WHERE ativo = 1 
                AND proxima_avaliacao >= CURDATE() 
                AND proxima_avaliacao <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            ");
            $proximas_avaliacoes = $stmt->fetch()['total'];
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'total_avaliacoes' => intval($total_avaliacoes),
                    'avaliacoes_mes' => intval($avaliacoes_mes),
                    'proximas_avaliacoes' => intval($proximas_avaliacoes)
                ]
            ]);
            break;

        case 'avaliacoes_mensal':
            // Avaliações por mês dos últimos 6 meses
            $stmt = $pdo->query("
                SELECT 
                    DATE_FORMAT(data_avaliacao, '%Y-%m') as mes,
                    DATE_FORMAT(data_avaliacao, '%b/%Y') as mes_formatado,
                    COUNT(*) as total
                FROM avaliacoes_fisicas
                WHERE ativo = 1 
                AND data_avaliacao >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(data_avaliacao, '%Y-%m')
                ORDER BY mes DESC
                LIMIT 6
            ");
            $avaliacoes_mensal = $stmt->fetchAll();
            
            $dados = array_map(function($item) {
                return [
                    'mes' => $item['mes_formatado'],
                    'total' => intval($item['total'])
                ];
            }, $avaliacoes_mensal);
            
            echo json_encode(['success' => true, 'data' => array_reverse($dados)]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ação não especificada']);
    }
} catch (PDOException $e) {
    error_log("Erro PDO na API de relatórios: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro no banco de dados']);
} catch (Exception $e) {
    error_log("Erro na API de relatórios: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}

