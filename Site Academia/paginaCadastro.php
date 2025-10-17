<?php
session_start();
include 'conexao.php';

// Verifica se os dados foram enviados via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validação básica de campos obrigatórios
    if (empty($_POST['usuario']) || empty($_POST['email']) || empty($_POST['cpf']) || empty($_POST['senha']) || empty($_POST['confirmarSenha'])) {
        header("Location: paginaCadastro.html?error=" . urlencode("Todos os campos obrigatórios devem ser preenchidos."));
        exit();
    }
    
    // Verifica se as senhas coincidem
    if ($_POST['senha'] !== $_POST['confirmarSenha']) {
        header("Location: paginaCadastro.html?error=" . urlencode("As senhas não coincidem."));
        exit();
    }
    
    // Limpa e valida os dados
    $usuario = trim($_POST['usuario']);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
    $telefone = !empty($_POST['telefone']) ? preg_replace('/[^0-9]/', '', $_POST['telefone']) : null;
    $senha = $_POST['senha'];
    
    // Validações adicionais
    if (!$email) {
        header("Location: paginaCadastro.html?error=" . urlencode("E-mail inválido."));
        exit();
    }
    
    if (strlen($cpf) !== 11) {
        header("Location: paginaCadastro.html?error=" . urlencode("CPF inválido."));
        exit();
    }
    
    if (strlen($senha) < 6) {
        header("Location: paginaCadastro.html?error=" . urlencode("A senha deve ter pelo menos 6 caracteres."));
        exit();
    }
    
    // Verifica se usuário ou email já existem
    $check_sql = "SELECT ID_ALUNO FROM ALUNO WHERE USUARIO_ALUNO = ? OR EMAIL_ALUNO = ? OR CPF_ALUNO = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("sss", $usuario, $email, $cpf);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        header("Location: paginaCadastro.html?error=" . urlencode("Usuário, e-mail ou CPF já cadastrados."));
        exit();
    }
    $check_stmt->close();
    
    // Hash da senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    
    // Prepara a query com prepared statements
    $sql = "INSERT INTO ALUNO (USUARIO_ALUNO, EMAIL_ALUNO, CPF_ALUNO, TEL_ALUNO, SENHA_HASH) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $usuario, $email, $cpf, $telefone, $senha_hash);
    
    if ($stmt->execute()) {
        // Cadastro bem-sucedido - redireciona para a página de cadastro com mensagem de sucesso
        header("Location: paginaCadastro.html?success=true");
        exit();
    } else {
        header("Location: paginaCadastro.html?error=" . urlencode("Erro ao cadastrar: " . $stmt->error));
        exit();
    }
    
    $stmt->close();
    $conn->close();
    
} else {
    header("Location: paginaCadastro.html");
    exit();
}
?>