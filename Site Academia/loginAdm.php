<?php
session_start();
include 'conexao.php';

// Verifica se os dados foram enviados via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validação básica
    if (empty($_POST['usuario']) || empty($_POST['senha'])) {
        header("Location: loginAdm.html?error=" . urlencode("Preencha todos os campos"));
        exit();
    }
    
    // Limpa os dados
    $usuario = trim($_POST['usuario']);
    $senha = $_POST['senha'];
    
    // Prepara a query para buscar o admin
    $sql = "SELECT ID_ADM, USUARIO_ADM, SENHA_ADM FROM ADMIN WHERE USUARIO_ADM = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            // Verifica a senha
            if (password_verify($senha, $admin['SENHA_ADM'])) {
                // Login bem-sucedido
                $_SESSION['admin_id'] = $admin['ID_ADM'];
                $_SESSION['admin_usuario'] = $admin['USUARIO_ADM'];
                $_SESSION['admin_logado'] = true;
                
                // Redireciona para a dashboard
                header("Location: dashboard_admin.php");
                exit();
            } else {
                // Senha incorreta
                header("Location: loginAdm.html?error=" . urlencode("Usuário ou senha incorretos"));
                exit();
            }
        } else {
            // Usuário não encontrado
            header("Location: loginAdm.html?error=" . urlencode("Usuário ou senha incorretos"));
            exit();
        }
        
        $stmt->close();
    } else {
        header("Location: loginAdm.html?error=" . urlencode("Erro no servidor"));
        exit();
    }
    
    $conn->close();
    
} else {
    // Se não foi POST, redireciona
    header("Location: loginAdm.html");
    exit();
}
?>