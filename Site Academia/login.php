<?php
// Iniciar sessão
session_start();

// Headers de segurança
header("Content-Type: application/json");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

// Função para resposta JSON
function jsonResponse($success, $message, $data = null) {
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

// Verificar se é POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    jsonResponse(false, "Método não permitido!");
}

// Receber e sanitizar dados
$login = trim($_POST["login"] ?? $_POST["email"] ?? ""); // Aceita "login" ou "email"
$senha = $_POST["senha"] ?? "";
$remember = isset($_POST["remember"]) ? true : false;

// Array de erros
$erros = [];

// Validação do login (email ou nome de usuário)
if (empty($login)) {
    $erros[] = "E-mail ou usuário é obrigatório";
}

// Validação da senha
if (empty($senha)) {
    $erros[] = "Senha é obrigatória";
}

// Se houver erros, retornar
if (!empty($erros)) {
    jsonResponse(false, implode("; ", $erros));
}

// Incluir arquivo de conexão
require_once "includes/conexao.php";

try {
    // Verificar e criar banco/tabelas se necessário (incluindo admin padrão)
    try {
        verificarBancoDados();
    } catch (Exception $e) {
        // Se houver erro ao criar banco, tentar conectar mesmo assim
        // (pode ser que o banco já exista mas a função falhou por outro motivo)
        error_log("Aviso ao verificar banco de dados: " . $e->getMessage());
    }
    
    // Conectar ao banco usando PDO
    $pdo = getConexao();
    
    // Buscar usuário por email ou nome, incluindo o tipo_usuario
    $stmt = $pdo->prepare("SELECT id, nome, email, senha, tipo_usuario FROM usuarios WHERE email = ? OR nome = ?");
    $stmt->execute([$login, $login]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        jsonResponse(false, "E-mail/usuário ou senha incorretos!");
    }
    
    // Verificar senha
    if (!password_verify($senha, $usuario["senha"])) {
        jsonResponse(false, "E-mail/usuário ou senha incorretos!");
    }
    
    // Criar sessão para o usuário
    $_SESSION["usuario_id"] = $usuario["id"];
    $_SESSION["usuario_nome"] = $usuario["nome"];
    $_SESSION["usuario_email"] = $usuario["email"];
    $_SESSION["tipo_usuario"] = $usuario["tipo_usuario"]; // Adicionar tipo de usuário à sessão
    
    // Se "Lembrar-me" estiver marcado, criar cookie
    if ($remember) {
        try {
            $token = bin2hex(random_bytes(32));
            $expiry = time() + (30 * 24 * 60 * 60); // 30 dias
            
            // Tentar salvar token no banco (se a tabela existir)
            $stmt = $pdo->prepare(
                "INSERT INTO tokens_autenticacao (usuario_id, token, expira_em) 
                 VALUES (?, ?, FROM_UNIXTIME(?))"
            );
            $stmt->execute([$usuario["id"], $token, $expiry]);
            
            // Definir cookie
            setcookie("remember_token", $token, $expiry, "/", "", false, true);
        } catch (PDOException $e) {
            // Se a tabela não existir, apenas continuar sem o token
            error_log("Tabela tokens_autenticacao não encontrada: " . $e->getMessage());
        }
    }
    
    // Log de auditoria (se a tabela existir)
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO logs_auditoria (usuario_id, acao, ip, data_hora) 
             VALUES (?, 'login', ?, NOW())"
        );
        $stmt->execute([$usuario["id"], $_SERVER["REMOTE_ADDR"] ?? "0.0.0.0"]);
    } catch (PDOException $e) {
        // Se a tabela não existir, apenas continuar sem o log
        error_log("Tabela logs_auditoria não encontrada: " . $e->getMessage());
    }
    
    // Definir URL de redirecionamento
    $redirect_url = "paginaInicial.php";
    if ($usuario["tipo_usuario"] === "admin") {
        $redirect_url = "admin/index.php"; // Redirecionar admin para o painel
    }
    
    jsonResponse(true, "Login realizado com sucesso!", [
        "redirect" => $redirect_url,
        "usuario_nome" => $usuario["nome"],
        "usuario_email" => $usuario["email"]
    ]);
    
} catch (PDOException $e) {
    // Log do erro (em produção, não expor detalhes)
    error_log("Erro no login: " . $e->getMessage());
    
    jsonResponse(false, "Erro ao processar login. Tente novamente mais tarde.");
}
?>
