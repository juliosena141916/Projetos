<?php
session_start();
include 'conexao.php';

// Verifica se os dados foram enviados via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validação básica
    if (empty($_POST['usuario']) || empty($_POST['senha'])) {
        header("Location: paginaLogin.html?error=" . urlencode("Preencha todos os campos"));
        exit();
    }
    
    // Limpa os dados
    $usuario = trim($_POST['usuario']);
    $senha = $_POST['senha'];
    
    // Prepara a query para buscar o aluno (por usuário ou email)
    $sql = "SELECT ID_ALUNO, USUARIO_ALUNO, EMAIL_ALUNO, SENHA_HASH FROM ALUNO 
            WHERE USUARIO_ALUNO = ? OR EMAIL_ALUNO = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ss", $usuario, $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $aluno = $result->fetch_assoc();
            
            // Verifica a senha
            if (password_verify($senha, $aluno['SENHA_HASH'])) {
                // Login bem-sucedido
                $_SESSION['aluno_id'] = $aluno['ID_ALUNO'];
                $_SESSION['aluno_usuario'] = $aluno['USUARIO_ALUNO'];
                $_SESSION['aluno_email'] = $aluno['EMAIL_ALUNO'];
                $_SESSION['aluno_logado'] = true;
                
                // Redireciona para a dashboard do aluno
                header("Location: dashboard_aluno.php");
                exit();
            }