<?php
namespace Controller;

use Model\Database;
use Model\DAO\MatriculaDAO;

/**
 * Controller para a página inicial
 */
class HomeController {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    /**
     * Exibir página inicial
     */
    public function index() {
        // Verificar autenticação
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: View/public/login.html');
            exit;
        }
        
        // Dados do usuário da sessão
        $usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';
        $usuario_email = $_SESSION['usuario_email'] ?? '';
        $tipo_usuario = $_SESSION['tipo_usuario'] ?? 'usuario';
        
        // Verificar se o usuário tem matrículas ativas
        $tem_matriculas = false;
        try {
            $matriculaDAO = new MatriculaDAO();
            $matriculas = $matriculaDAO->findByUsuario($_SESSION['usuario_id']);
            $tem_matriculas = !empty(array_filter($matriculas, function($m) {
                return isset($m['status']) && $m['status'] != 'cancelada';
            }));
        } catch (\Exception $e) {
            error_log("Erro ao verificar matrículas: " . $e->getMessage());
            $tem_matriculas = false;
        }
        
        // Renderizar view
        $this->renderView([
            'usuario_nome' => $usuario_nome,
            'usuario_email' => $usuario_email,
            'tipo_usuario' => $tipo_usuario,
            'tem_matriculas' => $tem_matriculas
        ]);
    }
    
    /**
     * Renderizar a view da página inicial
     */
    private function renderView($data) {
        extract($data);
        
        // Renderizar a view organizada em MVC
        require_once __DIR__ . '/../View/home/index.php';
    }
}

