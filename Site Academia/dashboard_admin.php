<?php
session_start();

// Verificar se o admin está logado
if (!isset($_SESSION['admin_logado']) || $_SESSION['admin_logado'] !== true) {
    header("Location: loginAdm.html");
    exit();
}

include 'conexao.php';

// Buscar estatísticas básicas
$total_alunos = 0;
$alunos_ativos = 0;

try {
    $result = $conn->query("SELECT COUNT(*) as total FROM ALUNO");
    if ($result) {
        $row = $result->fetch_assoc();
        $total_alunos = $row['total'];
    }
} catch (Exception $e) {
    error_log("Erro ao buscar total de alunos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Administração</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #dc3545;
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .stat-label {
            color: #666;
            font-size: 1.1em;
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
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .logout-section {
            text-align: center;
            margin-top: 30px;
        }
        .admin-info {
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
        <p>Painel Administrativo</p>
    </div>

    <div class="container">
        <div class="welcome-card">
            <h2>Bem-vindo, <?php echo htmlspecialchars($_SESSION['admin_usuario']); ?>!</h2>
            <p>Gerencie a academia e seus alunos</p>
        </div>

        <div class="admin-info">
            <h3>Informações do Administrador</h3>
            <p><strong>Usuário:</strong> <?php echo htmlspecialchars($_SESSION['admin_usuario']); ?></p>
            <p><strong>ID:</strong> <?php echo htmlspecialchars($_SESSION['admin_id']); ?></p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_alunos; ?></div>
                <div class="stat-label">Total de Alunos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $alunos_ativos; ?></div>
                <div class="stat-label">Alunos Ativos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Pagamentos Pendentes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Novos Cadastros Hoje</div>
            </div>
        </div>

        <div class="menu-grid">
            <div class="menu-card">
                <h3>Gerenciar Alunos</h3>
                <p>Visualizar, editar e gerenciar cadastros de alunos</p>
                <a href="#" class="btn">Gerenciar Alunos</a>
            </div>

            <div class="menu-card">
                <h3>Relatórios</h3>
                <p>Gerar relatórios de alunos, pagamentos e atividades</p>
                <a href="#" class="btn">Ver Relatórios</a>
            </div>

            <div class="menu-card">
                <h3>Pagamentos</h3>
                <p>Gerenciar mensalidades e pagamentos</p>
                <a href="#" class="btn btn-success">Gerenciar Pagamentos</a>
            </div>

            <div class="menu-card">
                <h3>Treinos</h3>
                <p>Criar e gerenciar treinos para os alunos</p>
                <a href="#" class="btn">Gerenciar Treinos</a>
            </div>

            <div class="menu-card">
                <h3>Agendamentos</h3>
                <p>Gerenciar aulas e agendamentos</p>
                <a href="#" class="btn">Gerenciar Agendamentos</a>
            </div>

            <div class="menu-card">
                <h3>Configurações</h3>
                <p>Configurações do sistema e perfil</p>
                <a href="#" class="btn">Configurações</a>
            </div>
        </div>

        <div class="logout-section">
            <a href="logoutAdm.php" class="btn btn-danger">Sair</a>
        </div>
    </div>
</body>
</html>
