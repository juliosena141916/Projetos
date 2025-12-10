<?php
namespace Controller;

use Model\Plano;
use Model\Database;

/**
 * Controller para gerenciamento de planos
 */
class PlanoController {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    public function listar($ativo = null) {
        $plano = new Plano();
        return $plano->findAll($ativo);
    }
    
    public function buscar($id) {
        $plano = new Plano();
        return $plano->findById($id);
    }
    
    public function criar($dados) {
        $plano = new Plano();
        $plano->setNome($dados['nome'])
              ->setDescricao($dados['descricao'] ?? null)
              ->setValorMensal($dados['valor_mensal'])
              ->setAcessoAcademia($dados['acesso_academia'] ?? 1)
              ->setAcessoMusculacao($dados['acesso_musculacao'] ?? 1)
              ->setAcessoTodasUnidades($dados['acesso_todas_unidades'] ?? 0)
              ->setAcessoTodosCursos($dados['acesso_todos_cursos'] ?? 0)
              ->setQuantidadeCursos($dados['quantidade_cursos'] ?? 0)
              ->setAulasGrupaisIlimitadas($dados['aulas_grupais_ilimitadas'] ?? 0)
              ->setPersonalTrainer($dados['personal_trainer'] ?? 0)
              ->setNutricionista($dados['nutricionista'] ?? 0)
              ->setAvaliacaoFisica($dados['avaliacao_fisica'] ?? 0)
              ->setAppExclusivo($dados['app_exclusivo'] ?? 0)
              ->setDescontoLoja($dados['desconto_loja'] ?? 0)
              ->setAtivo($dados['ativo'] ?? 1);
        
        return $plano->save();
    }
    
    public function atualizar($id, $dados) {
        $plano = new Plano();
        $plano->findById($id);
        
        if (!$plano->getId()) {
            throw new \Exception('Plano não encontrado');
        }
        
        if (isset($dados['nome'])) $plano->setNome($dados['nome']);
        if (isset($dados['descricao'])) $plano->setDescricao($dados['descricao']);
        if (isset($dados['valor_mensal'])) $plano->setValorMensal($dados['valor_mensal']);
        if (isset($dados['acesso_academia'])) $plano->setAcessoAcademia($dados['acesso_academia']);
        if (isset($dados['acesso_musculacao'])) $plano->setAcessoMusculacao($dados['acesso_musculacao']);
        if (isset($dados['acesso_todas_unidades'])) $plano->setAcessoTodasUnidades($dados['acesso_todas_unidades']);
        if (isset($dados['acesso_todos_cursos'])) $plano->setAcessoTodosCursos($dados['acesso_todos_cursos']);
        if (isset($dados['quantidade_cursos'])) $plano->setQuantidadeCursos($dados['quantidade_cursos']);
        if (isset($dados['aulas_grupais_ilimitadas'])) $plano->setAulasGrupaisIlimitadas($dados['aulas_grupais_ilimitadas']);
        if (isset($dados['personal_trainer'])) $plano->setPersonalTrainer($dados['personal_trainer']);
        if (isset($dados['nutricionista'])) $plano->setNutricionista($dados['nutricionista']);
        if (isset($dados['avaliacao_fisica'])) $plano->setAvaliacaoFisica($dados['avaliacao_fisica']);
        if (isset($dados['app_exclusivo'])) $plano->setAppExclusivo($dados['app_exclusivo']);
        if (isset($dados['desconto_loja'])) $plano->setDescontoLoja($dados['desconto_loja']);
        if (isset($dados['ativo'])) $plano->setAtivo($dados['ativo']);
        
        return $plano->save();
    }
    
    public function deletar($id) {
        $plano = new Plano();
        $plano->findById($id);
        
        if (!$plano->getId()) {
            throw new \Exception('Plano não encontrado');
        }
        
        return $plano->delete();
    }
    
    public static function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? $_POST['action'] ?? '';
        
        $controller = new self();
        
        try {
            switch ($method) {
                case 'GET':
                    if ($action === 'list') {
                        $planos = $controller->listar();
                        echo json_encode(['success' => true, 'data' => $planos], JSON_UNESCAPED_UNICODE);
                    } elseif (isset($_GET['id'])) {
                        $plano = $controller->buscar($_GET['id']);
                        if ($plano) {
                            echo json_encode(['success' => true, 'data' => $plano->toArray()], JSON_UNESCAPED_UNICODE);
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Plano não encontrado'], JSON_UNESCAPED_UNICODE);
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
                    echo json_encode(['success' => true, 'message' => 'Plano criado com sucesso', 'data' => ['id' => $id]], JSON_UNESCAPED_UNICODE);
                    break;
                    
                case 'PUT':
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (!isset($data['id'])) {
                        echo json_encode(['success' => false, 'message' => 'ID não fornecido'], JSON_UNESCAPED_UNICODE);
                        break;
                    }
                    
                    $controller->atualizar($data['id'], $data);
                    echo json_encode(['success' => true, 'message' => 'Plano atualizado com sucesso'], JSON_UNESCAPED_UNICODE);
                    break;
                    
                case 'DELETE':
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (!isset($data['id'])) {
                        echo json_encode(['success' => false, 'message' => 'ID não fornecido'], JSON_UNESCAPED_UNICODE);
                        break;
                    }
                    
                    $controller->deletar($data['id']);
                    echo json_encode(['success' => true, 'message' => 'Plano deletado com sucesso'], JSON_UNESCAPED_UNICODE);
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

