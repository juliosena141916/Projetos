<?php
// Log para debug (remover em produção)
error_log("cadastro.php - Método: " . ($_SERVER["REQUEST_METHOD"] ?? 'N/A'));
error_log("cadastro.php - POST data: " . print_r($_POST, true));

// Headers CORS (se necessário) - DEVEM ser enviados ANTES de qualquer output
if (!headers_sent()) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS, GET');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Max-Age: 3600');
}

// Responder a requisições OPTIONS (preflight)
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// Iniciar sessão (pode falhar se headers já foram enviados)
if (!headers_sent()) {
    session_start();
}

// Função para resposta JSON
function jsonResponse($success, $message, $data = null) {
    http_response_code($success ? 200 : 400);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar se é POST
$requestMethod = $_SERVER["REQUEST_METHOD"] ?? 'N/A';

// Para debug: aceitar GET temporariamente para testar se PHP está funcionando
if ($requestMethod === "GET" && isset($_GET['test'])) {
    jsonResponse(true, "PHP está funcionando! Método recebido: " . $requestMethod, [
        'method' => $requestMethod,
        'php_version' => PHP_VERSION,
        'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
        'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'N/A',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'N/A'
    ]);
}

// Verificar se é POST
if ($requestMethod !== "POST") {
    // Se não for POST e não for GET com test, retornar erro
    // Mas verificar se é uma requisição válida primeiro
    $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
    $isAjaxRequest = !empty($acceptHeader) && strpos($acceptHeader, 'application/json') !== false;
    
    // Log do erro detalhado
    error_log("cadastro.php - Método não permitido: " . $requestMethod);
    error_log("cadastro.php - REQUEST_METHOD: " . ($_SERVER["REQUEST_METHOD"] ?? 'não definido'));
    error_log("cadastro.php - REQUEST_URI: " . ($_SERVER["REQUEST_URI"] ?? 'não definido'));
    error_log("cadastro.php - HTTP_ACCEPT: " . ($_SERVER['HTTP_ACCEPT'] ?? 'não definido'));
    
    // Se for uma requisição AJAX, retornar JSON
    if ($isAjaxRequest || !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        http_response_code(405);
        jsonResponse(false, "Método não permitido! Use POST. Método recebido: " . $requestMethod . ". Verifique se está usando um servidor PHP.");
    } else {
        // Se não for AJAX, retornar HTML de erro
        http_response_code(405);
        die("Erro 405: Método não permitido! Use POST. Método recebido: " . $requestMethod);
    }
}

// Receber e sanitizar dados
// Log para debug
error_log("cadastro.php - POST superglobal: " . print_r($_POST, true));
error_log("cadastro.php - CONTENT_TYPE: " . ($_SERVER["CONTENT_TYPE"] ?? 'não definido'));

// Receber dados do POST
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';
$confirmar_senha = $_POST['confirmar_senha'] ?? '';

// Se não houver dados no $_POST, pode ser que o FormData não esteja sendo processado corretamente
if (empty($nome) && empty($email) && empty($senha)) {
    error_log("cadastro.php - AVISO: Nenhum dado recebido no POST!");
    error_log("cadastro.php - REQUEST_METHOD: " . ($_SERVER["REQUEST_METHOD"] ?? 'não definido'));
    error_log("cadastro.php - CONTENT_TYPE: " . ($_SERVER["CONTENT_TYPE"] ?? 'não definido'));
    
    // Tentar ler do input stream
    $input = file_get_contents('php://input');
    if (!empty($input)) {
        error_log("cadastro.php - Dados do input stream: " . substr($input, 0, 200));
        // Se for JSON
        $jsonData = json_decode($input, true);
        if ($jsonData) {
            $nome = trim($jsonData['nome'] ?? '');
            $email = trim($jsonData['email'] ?? '');
            $senha = $jsonData['senha'] ?? '';
            $confirmar_senha = $jsonData['confirmar_senha'] ?? '';
        }
    }
}

// Array de erros
$erros = [];

// Validação do nome
if (empty($nome)) {
    $erros[] = "Nome é obrigatório";
} elseif (strlen($nome) < 3) {
    $erros[] = "Nome deve ter pelo menos 3 caracteres";
} elseif (strlen($nome) > 100) {
    $erros[] = "Nome muito longo";
} elseif (!preg_match("/^[a-zA-ZÀ-ÿ\s'-]+$/u", $nome)) {
    $erros[] = "Nome contém caracteres inválidos";
}

// Validação do email
if (empty($email)) {
    $erros[] = "E-mail é obrigatório";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros[] = "E-mail inválido";
} elseif (strlen($email) > 255) {
    $erros[] = "E-mail muito longo";
}

// Validação da senha
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

// Validação da confirmação de senha
if (empty($confirmar_senha)) {
    $erros[] = "Confirmação de senha é obrigatória";
} elseif ($senha !== $confirmar_senha) {
    $erros[] = "As senhas não coincidem";
}

// Se houver erros, retornar
if (!empty($erros)) {
    jsonResponse(false, implode("; ", $erros));
}

// Incluir arquivo de conexão
require_once 'includes/conexao.php';

try {
    // Verificar e criar banco/tabelas se necessário
    // Esta função já conecta ao banco, então não precisamos chamar getConexao() novamente
    verificarBancoDados();
    
    // Obter conexão (verificarBancoDados já criou o banco, então esta conexão deve funcionar)
    $pdo = getConexao();
    
    // Verificar se o email já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        jsonResponse(false, "Este e-mail já está cadastrado!");
    }
    
    // Verificar se o nome de usuário já existe (opcional)
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE nome = ?");
    $stmt->execute([$nome]);
    
    if ($stmt->fetch()) {
        jsonResponse(false, "Este nome de usuário já está em uso!");
    }
    
    // Hash seguro da senha
    $senha_hash = password_hash($senha, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ]);
    
    // Se ARGON2ID não estiver disponível, usar BCRYPT
    if ($senha_hash === false) {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    }
    
    // Inserir usuário
    $stmt = $pdo->prepare(
        "INSERT INTO usuarios (nome, email, senha, data_cadastro) 
         VALUES (?, ?, ?, NOW())"
    );
    
    $stmt->execute([$nome, $email, $senha_hash]);
    
    // Pegar ID do usuário inserido
    $usuario_id = $pdo->lastInsertId();
    
    // Log de auditoria (se a tabela existir)
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO logs_auditoria (usuario_id, acao, ip, data_hora) 
             VALUES (?, 'cadastro', ?, NOW())"
        );
        $stmt->execute([$usuario_id, $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0']);
    } catch (PDOException $e) {
        // Se a tabela não existir, apenas continuar sem o log
        error_log("Tabela logs_auditoria não encontrada: " . $e->getMessage());
    }
    
    // Criar sessão para o usuário
    $_SESSION['usuario_id'] = $usuario_id;
    $_SESSION['usuario_nome'] = $nome;
    $_SESSION['usuario_email'] = $email;
    
    // Enviar email de boas-vindas (opcional)
    // mail($email, "Bem-vindo à TechFit!", "...");
    
    jsonResponse(true, "Cadastro realizado com sucesso!", [
        'redirect' => 'paginaInicial.php',
        'usuario_nome' => $nome,
        'usuario_email' => $email
    ]);
    
} catch (PDOException $e) {
    // Log do erro completo
    error_log("Erro no cadastro: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Em desenvolvimento, mostrar mais detalhes
    $errorMessage = "Erro ao processar cadastro. ";
    if (strpos($e->getMessage(), "Unknown database") !== false) {
        $errorMessage .= "Banco de dados não encontrado. Verifique a conexão.";
    } elseif (strpos($e->getMessage(), "Access denied") !== false) {
        $errorMessage .= "Erro de autenticação no banco de dados. Verifique as credenciais.";
    } else {
        $errorMessage .= "Detalhes: " . $e->getMessage();
    }
    
    jsonResponse(false, $errorMessage);
} catch (Exception $e) {
    // Log de outros tipos de erro
    error_log("Erro geral no cadastro: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    jsonResponse(false, "Erro inesperado: " . $e->getMessage());
}
?>