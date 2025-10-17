<?php
session_start();
include 'conexao.php';

// Verificar se o admin está logado
if (!isset($_SESSION['admin_logado']) || $_SESSION['admin_logado'] !== true) {
    header("Location: loginAdm.html");
    exit();
}

// Se for POST, processar alteração de senha
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $senha_atual = $_POST['senha_atual'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    
    // Validações
    if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_senha)) {
        $erro = "Todos os campos são obrigatórios.";
    } elseif ($nova_senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    } elseif (strlen($nova_senha) < 6) {
        $erro = "A nova senha deve ter pelo menos 6 caracteres.";
    } else {
        // Verificar senha atual
        $sql = "SELECT SENHA_ADM FROM ADMIN WHERE ID_ADM = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION['admin_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            if (password_verify($senha_atual, $admin['SENHA_ADM'])) {
                // Atualizar senha
                $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $update_sql = "UPDATE ADMIN SET SENHA_ADM = ? WHERE ID_ADM = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("si", $nova_senha_hash, $_SESSION['admin_id']);
                
                if ($update_stmt->execute()) {
                    $sucesso = "Senha alterada com sucesso!";
                } else {
                    $erro = "Erro ao alterar senha.";
                }
                $update_stmt->close();
            } else {
                $erro = "Senha atual incorreta.";
            }
        } else {
            $erro = "Erro interno do sistema.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Senha - Administração</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input:focus {
            outline: none;
            border-color: #007bff;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: #dc3545;
            text-align: center;
            margin-top: 10px;
            padding: 10px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            display: none;
        }
        .success {
            color: #155724;
            text-align: center;
            margin-top: 10px;
            padding: 10px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            display: none;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Alterar Senha</h2>
        <form method="POST">
            <div class="form-group">
                <label for="senha_atual">Senha Atual:</label>
                <input type="password" id="senha_atual" name="senha_atual" required>
            </div>
            
            <div class="form-group">
                <label for="nova_senha">Nova Senha:</label>
                <input type="password" id="nova_senha" name="nova_senha" required>
            </div>
            
            <div class="form-group">
                <label for="confirmar_senha">Confirmar Nova Senha:</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" required>
            </div>
            
            <button type="submit">Alterar Senha</button>
            
            <?php if (isset($erro)): ?>
                <div class="error" style="display: block;"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>
            
            <?php if (isset($sucesso)): ?>
                <div class="success" style="display: block;"><?php echo htmlspecialchars($sucesso); ?></div>
            <?php endif; ?>
        </form>
        
        <div class="back-link">
            <a href="dashboard_admin.php">← Voltar para Dashboard</a>
        </div>
    </div>
</body>
</html>
