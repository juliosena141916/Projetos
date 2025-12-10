<?php
namespace Controller;

use Model\Usuario;
use Model\Database;

/**
 * Controller para autenticação (login, cadastro, logout)
 */
class AuthController {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    /**
     * Realizar login
     */
    public function login($login, $senha, $remember = false) {
        // Buscar usuário por email ou nome
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = ? OR nome = ?");
        $stmt->execute([$login, $login]);
        $data = $stmt->fetch();
        
        if (!$data) {
            return [
                'success' => false,
                'message' => 'E-mail/usuário ou senha incorretos!'
            ];
        }
        
        $usuario = (new Usuario())->fillFromArray($data);
        
        if (!$usuario || !$usuario->verifyPassword($senha)) {
            return [
                'success' => false,
                'message' => 'E-mail/usuário ou senha incorretos!'
            ];
        }
        
        // Criar sessão
        $_SESSION['usuario_id'] = $usuario->getId();
        $_SESSION['usuario_nome'] = $usuario->getNome();
        $_SESSION['usuario_email'] = $usuario->getEmail();
        $_SESSION['tipo_usuario'] = $usuario->getTipoUsuario();
        
        // Lembrar-me
        if ($remember) {
            $this->createRememberToken($usuario->getId());
        }
        
        // Log de auditoria
        $this->logAuditoria($usuario->getId(), 'login');
        
        // URL de redirecionamento
        $redirect_url = "paginaInicial.php";
        if ($usuario->getTipoUsuario() === "admin") {
            $redirect_url = "admin/index.php";
        }
        
        return [
            'success' => true,
            'message' => 'Login realizado com sucesso!',
            'data' => [
                'redirect' => $redirect_url,
                'usuario_nome' => $usuario->getNome(),
                'usuario_email' => $usuario->getEmail()
            ]
        ];
    }
    
    /**
     * Realizar cadastro
     */
    public function cadastro($nome, $email, $senha, $confirmarSenha) {
        // Validações
        $erros = [];
        
        if (empty($nome)) {
            $erros[] = "Nome é obrigatório";
        } elseif (strlen($nome) < 3) {
            $erros[] = "Nome deve ter pelo menos 3 caracteres";
        }
        
        if (empty($email)) {
            $erros[] = "E-mail é obrigatório";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros[] = "E-mail inválido";
        }
        
        if (empty($senha)) {
            $erros[] = "Senha é obrigatória";
        } elseif (strlen($senha) < 8) {
            $erros[] = "Senha deve ter pelo menos 8 caracteres";
        } elseif (!preg_match("/[A-Z]/", $senha)) {
            $erros[] = "Senha deve conter pelo menos uma letra maiúscula";
        } elseif (!preg_match("/[a-z]/", $senha)) {
            $erros[] = "Senha deve conter pelo menos uma letra minúscula";
        } elseif (!preg_match("/[0-9]/", $senha)) {
            $erros[] = "Senha deve conter pelo menos um número";
        }
        
        if ($senha !== $confirmarSenha) {
            $erros[] = "As senhas não coincidem";
        }
        
        if (!empty($erros)) {
            return [
                'success' => false,
                'message' => implode("; ", $erros)
            ];
        }
        
        // Verificar se email já existe
        $usuarioModel = new Usuario();
        if ($usuarioModel->findByEmail($email)) {
            return [
                'success' => false,
                'message' => 'Este e-mail já está cadastrado!'
            ];
        }
        
        // Criar usuário
        $usuario = new Usuario();
        $usuario->setNome($nome)
                ->setEmail($email)
                ->setSenha($senha)
                ->setTipoUsuario('usuario');
        
        $usuarioId = $usuario->save();
        
        // Log de auditoria
        $this->logAuditoria($usuarioId, 'cadastro');
        
        // Criar sessão
        $_SESSION['usuario_id'] = $usuarioId;
        $_SESSION['usuario_nome'] = $nome;
        $_SESSION['usuario_email'] = $email;
        
        return [
            'success' => true,
            'message' => 'Cadastro realizado com sucesso!',
            'data' => [
                'redirect' => 'paginaInicial.php',
                'usuario_nome' => $nome,
                'usuario_email' => $email
            ]
        ];
    }
    
    /**
     * Realizar logout
     */
    public function logout() {
        session_destroy();
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        return [
            'success' => true,
            'message' => 'Logout realizado com sucesso!'
        ];
    }
    
    private function createRememberToken($usuarioId) {
        try {
            $token = bin2hex(random_bytes(32));
            $expiry = time() + (30 * 24 * 60 * 60); // 30 dias
            
            $stmt = $this->pdo->prepare(
                "INSERT INTO tokens_autenticacao (usuario_id, token, expira_em) 
                 VALUES (?, ?, FROM_UNIXTIME(?))"
            );
            $stmt->execute([$usuarioId, $token, $expiry]);
            
            setcookie("remember_token", $token, $expiry, "/", "", false, true);
        } catch (\PDOException $e) {
            error_log("Erro ao criar token: " . $e->getMessage());
        }
    }
    
    private function logAuditoria($usuarioId, $acao) {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO logs_auditoria (usuario_id, acao, ip, data_hora) 
                 VALUES (?, ?, ?, NOW())"
            );
            $stmt->execute([$usuarioId, $acao, $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0']);
        } catch (\PDOException $e) {
            error_log("Erro ao registrar log: " . $e->getMessage());
        }
    }
}

