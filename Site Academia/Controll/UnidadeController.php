<?php
namespace Controller;

use Model\Unidade;
use Model\Database;

/**
 * Controller para gerenciamento de unidades
 */
class UnidadeController {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    public function listar($ativo = null) {
        $unidade = new Unidade();
        return $unidade->findAll($ativo);
    }
    
    public function buscar($id) {
        $unidade = new Unidade();
        return $unidade->findById($id);
    }
    
    public function criar($dados) {
        $unidade = new Unidade();
        $unidade->setNome($dados['nome'])
                ->setCidade($dados['cidade'])
                ->setEndereco($dados['endereco'])
                ->setTelefone($dados['telefone'] ?? null)
                ->setHorarioFuncionamento($dados['horario_funcionamento'] ?? null)
                ->setAtivo($dados['ativo'] ?? 1);
        
        return $unidade->save();
    }
    
    public function atualizar($id, $dados) {
        $unidade = new Unidade();
        $unidade->findById($id);
        
        if (!$unidade->getId()) {
            throw new \Exception('Unidade não encontrada');
        }
        
        if (isset($dados['nome'])) $unidade->setNome($dados['nome']);
        if (isset($dados['cidade'])) $unidade->setCidade($dados['cidade']);
        if (isset($dados['endereco'])) $unidade->setEndereco($dados['endereco']);
        if (isset($dados['telefone'])) $unidade->setTelefone($dados['telefone']);
        if (isset($dados['horario_funcionamento'])) $unidade->setHorarioFuncionamento($dados['horario_funcionamento']);
        if (isset($dados['ativo'])) $unidade->setAtivo($dados['ativo']);
        
        return $unidade->save();
    }
    
    public function deletar($id) {
        $unidade = new Unidade();
        $unidade->findById($id);
        
        if (!$unidade->getId()) {
            throw new \Exception('Unidade não encontrada');
        }
        
        return $unidade->delete();
    }
    
    public function buscarPorCidade($cidade) {
        $unidade = new Unidade();
        return $unidade->findByCidade($cidade);
    }
    
    public static function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? $_POST['action'] ?? '';
        
        $controller = new self();
        
        try {
            switch ($method) {
                case 'GET':
                    if ($action === 'list') {
                        $unidades = $controller->listar();
                        echo json_encode(['success' => true, 'data' => $unidades], JSON_UNESCAPED_UNICODE);
                    } elseif (isset($_GET['id'])) {
                        $unidade = $controller->buscar($_GET['id']);
                        if ($unidade) {
                            echo json_encode(['success' => true, 'data' => $unidade->toArray()], JSON_UNESCAPED_UNICODE);
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Unidade não encontrada'], JSON_UNESCAPED_UNICODE);
                        }
                    } elseif (isset($_GET['cidade'])) {
                        $unidades = $controller->buscarPorCidade($_GET['cidade']);
                        echo json_encode(['success' => true, 'data' => $unidades], JSON_UNESCAPED_UNICODE);
                    }
                    break;
                    
                case 'POST':
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        echo json_encode(['success' => false, 'message' => 'JSON inválido'], JSON_UNESCAPED_UNICODE);
                        break;
                    }
                    
                    $id = $controller->criar($data);
                    echo json_encode(['success' => true, 'message' => 'Unidade criada com sucesso', 'data' => ['id' => $id]], JSON_UNESCAPED_UNICODE);
                    break;
                    
                case 'PUT':
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (!isset($data['id'])) {
                        echo json_encode(['success' => false, 'message' => 'ID não fornecido'], JSON_UNESCAPED_UNICODE);
                        break;
                    }
                    
                    $controller->atualizar($data['id'], $data);
                    echo json_encode(['success' => true, 'message' => 'Unidade atualizada com sucesso'], JSON_UNESCAPED_UNICODE);
                    break;
                    
                case 'DELETE':
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (!isset($data['id'])) {
                        echo json_encode(['success' => false, 'message' => 'ID não fornecido'], JSON_UNESCAPED_UNICODE);
                        break;
                    }
                    
                    $controller->deletar($data['id']);
                    echo json_encode(['success' => true, 'message' => 'Unidade deletada com sucesso'], JSON_UNESCAPED_UNICODE);
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

