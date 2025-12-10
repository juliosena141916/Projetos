<?php
namespace Controller;

use Model\Matricula;
use Model\Turma;
use Model\Database;

/**
 * Controller para gerenciamento de matrículas
 */
class MatriculaController {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    public function listar($usuarioId = null, $turmaId = null) {
        $matricula = new Matricula();
        
        if ($usuarioId) {
            return $matricula->findByUsuario($usuarioId);
        } elseif ($turmaId) {
            return $matricula->findByTurma($turmaId);
        } else {
            // Admin - todas as matrículas
            $stmt = $this->pdo->query("
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
            return $stmt->fetchAll();
        }
    }
    
    public function buscar($id) {
        $matricula = new Matricula();
        return $matricula->findById($id);
    }
    
    public function criar($dados) {
        // Verificar se já existe matrícula
        $stmt = $this->pdo->prepare("SELECT id FROM matriculas WHERE usuario_id = ? AND turma_id = ? AND ativo = 1");
        $stmt->execute([$dados['usuario_id'], $dados['turma_id']]);
        if ($stmt->fetch()) {
            throw new \Exception('Usuário já está matriculado nesta turma');
        }
        
        // Verificar vagas disponíveis
        $turma = new Turma();
        $turmaData = $turma->findById($dados['turma_id']);
        if (!$turmaData || $turmaData->getVagasDisponiveis() <= 0) {
            throw new \Exception('Não há vagas disponíveis nesta turma');
        }
        
        $matricula = new Matricula();
        $matricula->setUsuarioId($dados['usuario_id'])
                  ->setTurmaId($dados['turma_id'])
                  ->setStatus($dados['status'] ?? 'pendente')
                  ->setValorPago($dados['valor_pago'] ?? null)
                  ->setFormaPagamento($dados['forma_pagamento'] ?? null)
                  ->setObservacoes($dados['observacoes'] ?? null);
        
        $matriculaId = $matricula->save();
        
        // Atualizar vagas disponíveis
        $stmt = $this->pdo->prepare("UPDATE turmas_cursos SET vagas_disponiveis = vagas_disponiveis - 1 WHERE id = ?");
        $stmt->execute([$dados['turma_id']]);
        
        return $matriculaId;
    }
    
    public function atualizar($id, $dados) {
        $matricula = new Matricula();
        $matricula->findById($id);
        
        if (!$matricula->getId()) {
            throw new \Exception('Matrícula não encontrada');
        }
        
        if (isset($dados['status'])) $matricula->setStatus($dados['status']);
        if (isset($dados['valor_pago'])) $matricula->setValorPago($dados['valor_pago']);
        if (isset($dados['forma_pagamento'])) $matricula->setFormaPagamento($dados['forma_pagamento']);
        if (isset($dados['observacoes'])) $matricula->setObservacoes($dados['observacoes']);
        
        return $matricula->save();
    }
    
    public function cancelar($id) {
        $matricula = new Matricula();
        $matricula->findById($id);
        
        if (!$matricula->getId()) {
            throw new \Exception('Matrícula não encontrada');
        }
        
        // Atualizar vagas disponíveis
        $stmt = $this->pdo->prepare("UPDATE turmas_cursos SET vagas_disponiveis = vagas_disponiveis + 1 WHERE id = ?");
        $stmt->execute([$matricula->getTurmaId()]);
        
        return $matricula->delete();
    }
    
    public static function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? $_POST['action'] ?? '';
        
        $controller = new self();
        
        try {
            switch ($method) {
                case 'GET':
                    if ($action === 'list') {
                        $usuarioId = $_GET['usuario_id'] ?? null;
                        $turmaId = $_GET['turma_id'] ?? null;
                        $matriculas = $controller->listar($usuarioId, $turmaId);
                        echo json_encode(['success' => true, 'data' => $matriculas], JSON_UNESCAPED_UNICODE);
                    } elseif (isset($_GET['id'])) {
                        $matricula = $controller->buscar($_GET['id']);
                        if ($matricula) {
                            echo json_encode(['success' => true, 'data' => $matricula->toArray()], JSON_UNESCAPED_UNICODE);
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Matrícula não encontrada'], JSON_UNESCAPED_UNICODE);
                        }
                    }
                    break;
                    
                case 'POST':
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        echo json_encode(['success' => false, 'message' => 'JSON inválido'], JSON_UNESCAPED_UNICODE);
                        break;
                    }
                    
                    $id = $controller->criar($data);
                    echo json_encode(['success' => true, 'message' => 'Matrícula criada com sucesso', 'data' => ['id' => $id]], JSON_UNESCAPED_UNICODE);
                    break;
                    
                case 'PUT':
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (!isset($data['id'])) {
                        echo json_encode(['success' => false, 'message' => 'ID não fornecido'], JSON_UNESCAPED_UNICODE);
                        break;
                    }
                    
                    $controller->atualizar($data['id'], $data);
                    echo json_encode(['success' => true, 'message' => 'Matrícula atualizada com sucesso'], JSON_UNESCAPED_UNICODE);
                    break;
                    
                case 'DELETE':
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (!isset($data['id'])) {
                        echo json_encode(['success' => false, 'message' => 'ID não fornecido'], JSON_UNESCAPED_UNICODE);
                        break;
                    }
                    
                    $controller->cancelar($data['id']);
                    echo json_encode(['success' => true, 'message' => 'Matrícula cancelada com sucesso'], JSON_UNESCAPED_UNICODE);
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'message' => 'Método não permitido'], JSON_UNESCAPED_UNICODE);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
}

