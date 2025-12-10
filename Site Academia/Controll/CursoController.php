<?php
namespace Controller;

use Model\Curso;
use Model\Database;

/**
 * Controller para gerenciamento de cursos
 */
class CursoController {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    public function listar($ativo = null) {
        $curso = new Curso();
        return $curso->findAll($ativo);
    }
    
    public function buscar($id) {
        $curso = new Curso();
        return $curso->findById($id);
    }
    
    public function criar($dados) {
        $curso = new Curso();
        $curso->setNome($dados['nome'])
              ->setCategoria($dados['categoria'])
              ->setDescricao($dados['descricao'])
              ->setDuracao($dados['duracao'])
              ->setValorTotal($dados['valor_total'])
              ->setAtivo($dados['ativo'] ?? 1);
        
        return $curso->save();
    }
    
    public function atualizar($id, $dados) {
        $curso = new Curso();
        $curso->findById($id);
        
        if (!$curso->getId()) {
            throw new \Exception('Curso não encontrado');
        }
        
        if (isset($dados['nome'])) $curso->setNome($dados['nome']);
        if (isset($dados['categoria'])) $curso->setCategoria($dados['categoria']);
        if (isset($dados['descricao'])) $curso->setDescricao($dados['descricao']);
        if (isset($dados['duracao'])) $curso->setDuracao($dados['duracao']);
        if (isset($dados['valor_total'])) $curso->setValorTotal($dados['valor_total']);
        if (isset($dados['ativo'])) $curso->setAtivo($dados['ativo']);
        
        return $curso->save();
    }
    
    public function deletar($id) {
        $curso = new Curso();
        $curso->findById($id);
        
        if (!$curso->getId()) {
            throw new \Exception('Curso não encontrado');
        }
        
        return $curso->delete();
    }
    
    public function getCategorias() {
        $curso = new Curso();
        return $curso->getCategorias();
    }
    
    public static function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? $_POST['action'] ?? '';
        
        $controller = new self();
        
        try {
            switch ($method) {
                case 'GET':
                    if ($action === 'list') {
                        $cursos = $controller->listar();
                        echo json_encode(['success' => true, 'data' => $cursos], JSON_UNESCAPED_UNICODE);
                    } elseif (isset($_GET['id'])) {
                        $curso = $controller->buscar($_GET['id']);
                        if ($curso) {
                            echo json_encode(['success' => true, 'data' => $curso->toArray()], JSON_UNESCAPED_UNICODE);
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Curso não encontrado'], JSON_UNESCAPED_UNICODE);
                        }
                    } elseif ($action === 'categorias') {
                        $categorias = $controller->getCategorias();
                        echo json_encode(['success' => true, 'data' => $categorias], JSON_UNESCAPED_UNICODE);
                    }
                    break;
                    
                case 'POST':
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        echo json_encode(['success' => false, 'message' => 'JSON inválido'], JSON_UNESCAPED_UNICODE);
                        break;
                    }
                    
                    $id = $controller->criar($data);
                    echo json_encode(['success' => true, 'message' => 'Curso criado com sucesso', 'data' => ['id' => $id]], JSON_UNESCAPED_UNICODE);
                    break;
                    
                case 'PUT':
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (!isset($data['id'])) {
                        echo json_encode(['success' => false, 'message' => 'ID não fornecido'], JSON_UNESCAPED_UNICODE);
                        break;
                    }
                    
                    $controller->atualizar($data['id'], $data);
                    echo json_encode(['success' => true, 'message' => 'Curso atualizado com sucesso'], JSON_UNESCAPED_UNICODE);
                    break;
                    
                case 'DELETE':
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (!isset($data['id'])) {
                        echo json_encode(['success' => false, 'message' => 'ID não fornecido'], JSON_UNESCAPED_UNICODE);
                        break;
                    }
                    
                    $controller->deletar($data['id']);
                    echo json_encode(['success' => true, 'message' => 'Curso deletado com sucesso'], JSON_UNESCAPED_UNICODE);
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

