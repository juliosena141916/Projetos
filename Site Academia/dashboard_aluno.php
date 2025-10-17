<?php
session_start();

// Verificar se o aluno está logado
if (!isset($_SESSION['aluno_logado']) || $_SESSION['aluno_logado'] !== true) {
    header("Location: paginaLogin.html");
    exit();
}

include 'conexao.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Aluno</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .welcome-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .menu-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        .menu-card:hover {
            transform: translateY(-5px);
        }
        .menu-card h3 {
            color: #333;
            margin-bottom: 15px;
        }
        .menu-card p {
            color: #666;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #545b62;
        }
        .logout-section {
            text-align: center;
            margin-top: 30px;
        }
        .user-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Academia Fitness</h1>
        <p>Área do Aluno</p>
    </div>

    <div class="container">
        <div class="welcome-card">
            <h2>Bem-vindo, <?php echo htmlspecialchars($_SESSION['aluno_usuario']); ?>!</h2>
            <p>Gerencie sua conta e atividades na academia</p>
        </div>

        <div class="user-info">
            <h3>Informações da Conta</h3>
            <p><strong>Usuário:</strong> <?php echo htmlspecialchars($_SESSION['aluno_usuario']); ?></p>
            <p><strong>E-mail:</strong> <?php echo htmlspecialchars($_SESSION['aluno_email']); ?></p>
        </div>

        <div class="menu-grid">
            <div class="menu-card">
                <h3>Meu Perfil</h3>
                <p>Visualizar e editar suas informações pessoais</p>
                <a href="#" class="btn">Ver Perfil</a>
            </div>

            <div class="menu-card">
                <h3>Treinos</h3>
                <p>Consultar seus treinos e exercícios</p>
                <a href="#" class="btn">Ver Treinos</a>
            </div>

            <div class="menu-card">
                <h3>Pagamentos</h3>
                <p>Histórico de pagamentos e mensalidades</p>
                <a href="#" class="btn">Ver Pagamentos</a>
            </div>

            <div class="menu-card">
                <h3>Agendamentos</h3>
                <p>Agendar aulas e consultas</p>
                <a href="#" class="btn">Agendar</a>
            </div>

            <div class="menu-card">
                <h3>Contato</h3>
                <p>Entre em contato com a academia</p>
                <a href="#" class="btn">Contatar</a>
            </div>

            <div class="menu-card">
                <h3>Configurações</h3>
                <p>Alterar senha e configurações</p>
                <a href="#" class="btn btn-secondary">Configurar</a>
            </div>
        </div>

        <div class="logout-section">
            <a href="logout_aluno.php" class="btn btn-secondary">Sair</a>
        </div>
    </div>
</body>
</html>
