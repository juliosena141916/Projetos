<?php
namespace Controller;

use Model\Turma;
use Model\Matricula;
use Model\Database;
use Exception;

/**
 * Controller para gerenciamento de turmas
 */
class TurmaController {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    public static function jsonResponse($success, $message, $data = null, $code = 200) {
        http_response_code($code);
        echo json_encode([
            "success" => $success,
            "message" => $message,
            "data" => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private static function checkAdmin() {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
            self::jsonResponse(false, 'Acesso negado. Faça login como administrador.', null, 403);
        }
    }

    public static function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? $_POST['action'] ?? '';
        
        // Para turmas disponíveis (público), não requer admin
        if ($action === 'disponiveis' || (empty($action) && $method === 'GET' && !isset($_GET['id']))) {
            self::listarDisponiveis();
            return;
        }
        
        // Para outras ações, requer admin
        self::checkAdmin();
        
        // Usar Aula do namespace antigo se ainda não migrado, ou novo se migrado
        $aulaClass = class_exists('Model\Aula') ? 'Model\Aula' : 'App\Models\Aula';
        $turmaModel = new Turma();
        $aulaModel = new $aulaClass();

        try {
            switch ($method) {
                case 'GET':
                    if (isset($_GET['id'])) {
                        $turma = $turmaModel->findById($_GET['id']);
                        if ($turma) {
                            $aulas = $aulaModel->findByTurma($_GET['id']);
                            $turma['aulas'] = $aulas;
                            self::jsonResponse(true, 'Turma encontrada', ['turma' => $turma]);
                        } else {
                            self::jsonResponse(false, 'Turma não encontrada', null, 404);
                        }
                    } else {
                        $turmas = $turmaModel->findAll(1);
                        self::jsonResponse(true, 'Turmas listadas com sucesso', ['turmas' => $turmas]);
                    }
                    break;

                case 'POST':
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        self::jsonResponse(false, 'JSON inválido: ' . json_last_error_msg(), null, 400);
                    }

                    $erros = self::validateTurmaData($data);
                    if (!empty($erros)) {
                        self::jsonResponse(false, 'Dados inválidos: ' . implode(', ', $erros), null, 400);
                    }

                    $turmaId = self::criarTurma($data);
                    if ($turmaId) {
                        self::jsonResponse(true, 'Turma criada com sucesso', ['id' => $turmaId]);
                    } else {
                        self::jsonResponse(false, 'Erro ao criar turma', null, 500);
                    }
                    break;

                case 'PUT':
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        self::jsonResponse(false, 'JSON inválido: ' . json_last_error_msg(), null, 400);
                    }

                    if (!isset($data['id'])) {
                        self::jsonResponse(false, 'ID não fornecido', null, 400);
                    }

                    $erros = self::validateTurmaData($data, true);
                    if (!empty($erros)) {
                        self::jsonResponse(false, 'Dados inválidos: ' . implode(', ', $erros), null, 400);
                    }

                    $result = self::atualizarTurma($data['id'], $data);
                    if ($result) {
                        self::jsonResponse(true, 'Turma atualizada com sucesso');
                    } else {
                        self::jsonResponse(false, 'Erro ao atualizar turma', null, 500);
                    }
                    break;

                case 'DELETE':
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (!isset($data['id'])) {
                        self::jsonResponse(false, 'ID não fornecido', null, 400);
                    }

                    $result = self::deletarTurma($data['id']);
                    if ($result) {
                        self::jsonResponse(true, 'Turma deletada com sucesso');
                    } else {
                        self::jsonResponse(false, 'Erro ao deletar turma', null, 500);
                    }
                    break;

                default:
                    self::jsonResponse(false, 'Método não permitido', null, 405);
            }
        } catch (Exception $e) {
            error_log("Erro no TurmaController: " . $e->getMessage());
            self::jsonResponse(false, 'Erro interno do servidor: ' . $e->getMessage(), null, 500);
        }
    }

    private static function listarDisponiveis() {
        $pdo = Database::getInstance()->getConnection();
        $where_conditions = ["t.ativo = 1", "t.vagas_disponiveis > 0"];
        $params = [];
        
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
        $turmas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Se o usuário estiver logado, verificar se já está matriculado
        if (isset($_SESSION['usuario_id'])) {
            $usuario_id = $_SESSION['usuario_id'];
            $matriculaModel = new Matricula();
            
            foreach ($turmas as &$turma) {
                $matriculas = $matriculaModel->findByTurma($turma['id']);
                $jaMatriculado = false;
                $statusMatricula = null;
                
                foreach ($matriculas as $mat) {
                    if ($mat['usuario_id'] == $usuario_id && $mat['status'] != 'cancelada') {
                        $jaMatriculado = true;
                        $statusMatricula = $mat['status'];
                        break;
                    }
                }
                
                $turma['ja_matriculado'] = $jaMatriculado;
                $turma['matricula_status'] = $statusMatricula;
            }
        }
        
        self::jsonResponse(true, 'Turmas disponíveis listadas', ['turmas' => $turmas]);
    }

    private static function validateTurmaData(array $data, $isUpdate = false) {
        $erros = [];
        if (!$isUpdate) {
            if (empty($data['curso_id'])) $erros[] = 'ID do curso é obrigatório';
            if (empty($data['unidade_id'])) $erros[] = 'ID da unidade é obrigatório';
            if (empty($data['nome_turma'])) $erros[] = 'Nome da turma é obrigatório';
            if (empty($data['data_inicio'])) $erros[] = 'Data de início é obrigatória';
            if (empty($data['data_fim'])) $erros[] = 'Data de fim é obrigatória';
        }
        
        if (isset($data['data_inicio']) && isset($data['data_fim'])) {
            if (strtotime($data['data_inicio']) > strtotime($data['data_fim'])) {
                $erros[] = 'Data de início não pode ser posterior à data de fim';
            }
        }
        
        if (isset($data['hora_inicio']) && isset($data['hora_fim'])) {
            if (strtotime($data['hora_inicio']) >= strtotime($data['hora_fim'])) {
                $erros[] = 'Hora de início não pode ser igual ou posterior à hora de fim';
            }
        }
        
        return $erros;
    }

    private static function criarTurma($data) {
        $turma = new Turma();
        $turma->setCursoId($data['curso_id'])
              ->setUnidadeId($data['unidade_id'])
              ->setNomeTurma($data['nome_turma'])
              ->setInstrutor($data['instrutor'] ?? null)
              ->setVagasTotais($data['vagas_totais'] ?? 20)
              ->setVagasDisponiveis($data['vagas_disponiveis'] ?? ($data['vagas_totais'] ?? 20))
              ->setDataInicio($data['data_inicio'])
              ->setDataFim($data['data_fim'])
              ->setDiasSemana($data['dias_semana'] ?? null)
              ->setHoraInicio($data['hora_inicio'] ?? null)
              ->setHoraFim($data['hora_fim'] ?? null)
              ->setSalaPadrao($data['sala_padrao'] ?? null)
              ->setStatus($data['status'] ?? 'planejada')
              ->setAtivo(1);
        
        $turmaId = $turma->save();
        
        // Gerar aulas automaticamente se houver dias_semana, hora_inicio e hora_fim
        if (!empty($data['dias_semana']) && !empty($data['hora_inicio']) && !empty($data['hora_fim'])) {
            self::gerarAulas($turmaId, $data);
        }
        
        return $turmaId;
    }

    private static function atualizarTurma($id, $data) {
        $turma = new Turma();
        $turmaData = $turma->findById($id);
        
        if (!$turmaData) {
            throw new Exception('Turma não encontrada');
        }
        
        // Verificar se campos relevantes mudaram
        $camposRelevantes = ['dias_semana', 'hora_inicio', 'hora_fim', 'data_inicio', 'data_fim'];
        $camposMudaram = false;
        foreach ($camposRelevantes as $campo) {
            if (isset($data[$campo]) && $data[$campo] != ($turmaData[$campo] ?? null)) {
                $camposMudaram = true;
                break;
            }
        }
        
        // Se campos relevantes mudaram, remover aulas antigas
        if ($camposMudaram) {
            $aulaClass = class_exists('Model\Aula') ? 'Model\Aula' : 'App\Models\Aula';
            $aulaModel = new $aulaClass();
            $aulas = $aulaModel->findByTurma($id);
            foreach ($aulas as $aula) {
                $aulaModel->delete($aula['id']);
            }
        }
        
        $turmaObj = new Turma();
        $turmaObj->fillFromArray($turmaData);
        
        if (isset($data['curso_id'])) $turmaObj->setCursoId($data['curso_id']);
        if (isset($data['unidade_id'])) $turmaObj->setUnidadeId($data['unidade_id']);
        if (isset($data['nome_turma'])) $turmaObj->setNomeTurma($data['nome_turma']);
        if (isset($data['instrutor'])) $turmaObj->setInstrutor($data['instrutor']);
        if (isset($data['vagas_totais'])) $turmaObj->setVagasTotais($data['vagas_totais']);
        if (isset($data['vagas_disponiveis'])) $turmaObj->setVagasDisponiveis($data['vagas_disponiveis']);
        if (isset($data['data_inicio'])) $turmaObj->setDataInicio($data['data_inicio']);
        if (isset($data['data_fim'])) $turmaObj->setDataFim($data['data_fim']);
        if (isset($data['dias_semana'])) $turmaObj->setDiasSemana($data['dias_semana']);
        if (isset($data['hora_inicio'])) $turmaObj->setHoraInicio($data['hora_inicio']);
        if (isset($data['hora_fim'])) $turmaObj->setHoraFim($data['hora_fim']);
        if (isset($data['sala_padrao'])) $turmaObj->setSalaPadrao($data['sala_padrao']);
        if (isset($data['status'])) $turmaObj->setStatus($data['status']);
        if (isset($data['ativo'])) $turmaObj->setAtivo($data['ativo']);
        
        $result = $turmaObj->save();
        
        // Gerar aulas se necessário
        if ($camposMudaram && !empty($data['dias_semana']) && !empty($data['hora_inicio']) && !empty($data['hora_fim'])) {
            self::gerarAulas($id, $data);
        }
        
        return $result;
    }

    private static function deletarTurma($id) {
        $turma = new Turma();
        $turmaData = $turma->findById($id);
        
        if (!$turmaData) {
            throw new Exception('Turma não encontrada');
        }
        
        // Verificar se há matrículas ativas
        $matriculaModel = new Matricula();
        $matriculas = $matriculaModel->findByTurma($id);
        $temMatriculasAtivas = false;
        
        foreach ($matriculas as $mat) {
            if ($mat['status'] != 'cancelada') {
                $temMatriculasAtivas = true;
                break;
            }
        }
        
        if ($temMatriculasAtivas) {
            // Apenas desativar
            $turmaObj = new Turma();
            $turmaObj->fillFromArray($turmaData);
            $turmaObj->setAtivo(0);
            return $turmaObj->save();
        } else {
            // Deletar completamente
            return $turma->delete();
        }
    }

    private static function gerarAulas($turmaId, $dados) {
        $diasSemana = array_map('intval', explode(',', $dados['dias_semana']));
        $dataInicio = new \DateTime($dados['data_inicio']);
        $dataFim = new \DateTime($dados['data_fim']);
        $horaInicio = $dados['hora_inicio'];
        $horaFim = $dados['hora_fim'];
        $sala = $dados['sala_padrao'] ?? null;
        
        $aulaClass = class_exists('Model\Aula') ? 'Model\Aula' : 'App\Models\Aula';
        $aulaModel = new $aulaClass();
        $dataAtual = clone $dataInicio;
        
        while ($dataAtual <= $dataFim) {
            $diaSemana = (int)$dataAtual->format('w'); // 0 = domingo, 1 = segunda, etc.
            
            if (in_array($diaSemana, $diasSemana, true)) {
                $aula = new $aulaClass();
                $aula->setTurmaId($turmaId)
                     ->setDataAula($dataAtual->format('Y-m-d'))
                     ->setHoraInicio($horaInicio)
                     ->setHoraFim($horaFim)
                     ->setSala($sala)
                     ->setStatus('agendada')
                     ->setAtivo(1);
                
                $aula->save();
            }
            
            $dataAtual->modify('+1 day');
        }
    }
}

