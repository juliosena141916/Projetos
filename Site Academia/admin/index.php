<?php
require_once 'check_admin.php';

// Incluir arquivo de conexão
$conexao_path = dirname(__FILE__) . '/../includes/conexao.php';
require_once $conexao_path;

// Buscar contagens do banco de dados
try {
    $pdo = getConexao();
    
    // Contar usuários
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $total_usuarios = $stmt->fetch()['total'];
    
    // Contar unidades
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM unidades WHERE ativo = 1");
    $total_unidades = $stmt->fetch()['total'];
    
    // Contar cursos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cursos WHERE ativo = 1");
    $total_cursos = $stmt->fetch()['total'];
    
} catch (Exception $e) {
    error_log("Erro ao buscar contagens do dashboard: " . $e->getMessage());
    $total_usuarios = 0;
    $total_unidades = 0;
    $total_cursos = 0;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - TechFit</title>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/notifications.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            padding-top: 20px;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            overflow-y: auto;
        }
        .sidebar h4 {
            color: #fff;
            text-align: center;
            margin-bottom: 30px;
        }
        .sidebar a {
            color: #adb5bd;
            padding: 10px 15px;
            text-decoration: none;
            display: block;
            transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #495057;
            color: #fff;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        .page-header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #007bff;
            color: white;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            font-size: 2.5rem;
            margin: 0;
        }
        .stat-card p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <h4>Admin TechFit</h4>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="index.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="usuarios.php">
                    <i class="fas fa-users"></i> Gerenciar Usuários
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="unidades.php">
                    <i class="fas fa-building"></i> Gerenciar Unidades
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="cursos.php">
                    <i class="fas fa-graduation-cap"></i> Gerenciar Cursos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="planos.php">
                    <i class="fas fa-tags"></i> Gerenciar Planos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="turmas.php">
                    <i class="fas fa-calendar-alt"></i> Gerenciar Turmas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="agendamento_aulas.php">
                    <i class="fas fa-calendar-day"></i> Agendar Aulas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="frequencia.php">
                    <i class="fas fa-check-circle"></i> Gerenciar Frequência
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="avaliacoes.php">
                    <i class="fas fa-heartbeat"></i> Avaliações Físicas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="suporte.php">
                    <i class="fas fa-headset"></i> Mensagens de Suporte
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../paginaInicial.php">
                    <i class="fas fa-globe"></i> Ver Site
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </li>
        </ul>
    </nav>

            <!-- Main Content -->
            <main class="content">
                <div class="page-header">
                    <h1 class="h2 mb-0">Dashboard Administrativo</h1>
                    <p class="text-muted mb-0">Bem-vindo(a), <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!</p>
                </div>

                <!-- Cards de Estatísticas Principais -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-header">Usuários Cadastrados</div>
                            <div class="card-body">
                                <h5 class="card-title">Total: <span id="total-usuarios"><?php echo $total_usuarios; ?></span></h5>
                                <p class="card-text">Gerencie todos os usuários do sistema.</p>
                                <a href="usuarios.php" class="btn btn-light btn-sm">Ver Detalhes</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-header">Unidades</div>
                            <div class="card-body">
                                <h5 class="card-title">Total: <span id="total-unidades"><?php echo $total_unidades; ?></span></h5>
                                <p class="card-text">Adicione e edite as unidades da academia.</p>
                                <a href="unidades.php" class="btn btn-light btn-sm">Ver Detalhes</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-header">Cursos</div>
                            <div class="card-body">
                                <h5 class="card-title">Total: <span id="total-cursos"><?php echo $total_cursos; ?></span></h5>
                                <p class="card-text">Gerencie os cursos e treinamentos oferecidos.</p>
                                <a href="cursos.php" class="btn btn-light btn-sm">Ver Detalhes</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning mb-3">
                            <div class="card-header">Planos</div>
                            <div class="card-body">
                                <h5 class="card-title">Total: <span id="total-planos">-</span></h5>
                                <p class="card-text">Gerencie os planos de assinatura.</p>
                                <a href="planos.php" class="btn btn-light btn-sm">Ver Detalhes</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção de Frequência -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="page-header">
                            <h2 class="h3 mb-0"><i class="fas fa-check-circle"></i> Frequência dos Alunos</h2>
                            <p class="text-muted mb-0">Estatísticas e análises de presença</p>
                            <a href="frequencia.php" class="btn btn-primary btn-sm mt-2">
                                <i class="fas fa-edit"></i> Gerenciar Frequência
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Cards de Frequência -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card text-white mb-3" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                            <div class="card-body">
                                <h3 class="card-title" id="totalPresentes">-</h3>
                                <p class="card-text"><i class="fas fa-check"></i> Total de Presenças</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white mb-3" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                            <div class="card-body">
                                <h3 class="card-title" id="totalAusentes">-</h3>
                                <p class="card-text"><i class="fas fa-times"></i> Total de Ausências</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white mb-3" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);">
                            <div class="card-body">
                                <h3 class="card-title" id="totalJustificados">-</h3>
                                <p class="card-text"><i class="fas fa-exclamation-triangle"></i> Total Justificados</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white mb-3" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                            <div class="card-body">
                                <h3 class="card-title" id="taxaFrequenciaCard">-</h3>
                                <p class="card-text"><i class="fas fa-percentage"></i> Taxa de Frequência</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos de Frequência -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Distribuição de Frequência</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="graficoFrequenciaPizza"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Frequência por Turma</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="graficoFrequenciaTurma"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Frequência por Turma -->
                <div class="card mt-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-table"></i> Frequência Detalhada por Turma</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tabelaFrequenciaTurma">
                                <thead>
                                    <tr>
                                        <th>Turma</th>
                                        <th>Curso</th>
                                        <th>Presentes</th>
                                        <th>Ausentes</th>
                                        <th>Justificados</th>
                                        <th>Total</th>
                                        <th>Taxa de Frequência</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <i class="fas fa-spinner fa-spin"></i> Carregando...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Ações Rápidas -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                Ações Rápidas
                            </div>
                            <div class="card-body">
                                <a href="usuarios.php?action=add" class="btn btn-outline-primary mr-2">Novo Usuário</a>
                                <a href="unidades.php?action=add" class="btn btn-outline-success mr-2">Nova Unidade</a>
                                <a href="cursos.php?action=add" class="btn btn-outline-info mr-2">Novo Curso</a>
                                <a href="planos.php?action=add" class="btn btn-outline-warning">Novo Plano</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção de Relatórios -->
                <div id="relatorios" style="scroll-margin-top: 20px;">
                    <div class="page-header mt-4">
                        <h2 class="h3 mb-0">Relatórios e Estatísticas</h2>
                        <p class="text-muted mb-0">Análises e métricas do sistema</p>
                    </div>

                    <!-- Cards de Estatísticas Avançadas -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <h3 id="totalUsuariosRel">-</h3>
                                <p><i class="fas fa-users"></i> Total de Usuários</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                <h3 id="totalMatriculas">-</h3>
                                <p><i class="fas fa-graduation-cap"></i> Matrículas Ativas</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                <h3 id="taxaOcupacao">-</h3>
                                <p><i class="fas fa-chart-line"></i> Taxa de Ocupação</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                                <h3 id="taxaFrequencia">-</h3>
                                <p><i class="fas fa-check-circle"></i> Taxa de Frequência</p>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfico de Ocupação por Turma -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Ocupação por Turma</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="graficoOcupacao"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela de Ocupação Detalhada -->
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-table"></i> Ocupação Detalhada por Turma</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="tabelaOcupacao">
                                    <thead>
                                        <tr>
                                            <th>Turma</th>
                                            <th>Curso</th>
                                            <th>Unidade</th>
                                            <th>Vagas Totais</th>
                                            <th>Vagas Ocupadas</th>
                                            <th>Vagas Disponíveis</th>
                                            <th>% Ocupação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                <i class="fas fa-spinner fa-spin"></i> Carregando...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção de Avaliações Físicas -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="page-header">
                            <h2 class="h3 mb-0"><i class="fas fa-heartbeat"></i> Avaliações Físicas</h2>
                            <p class="text-muted mb-0">Estatísticas e análises das avaliações físicas</p>
                            <a href="avaliacoes.php" class="btn btn-primary btn-sm mt-2">
                                <i class="fas fa-edit"></i> Gerenciar Avaliações
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Cards de Estatísticas de Avaliações -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="card text-white mb-3" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                            <div class="card-body">
                                <h3 class="card-title" id="totalAvaliacoes">-</h3>
                                <p class="card-text"><i class="fas fa-clipboard-list"></i> Total de Avaliações</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white mb-3" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);">
                            <div class="card-body">
                                <h3 class="card-title" id="avaliacoesMes">-</h3>
                                <p class="card-text"><i class="fas fa-calendar-alt"></i> Avaliações Este Mês</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white mb-3" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                            <div class="card-body">
                                <h3 class="card-title" id="proximasAvaliacoes">-</h3>
                                <p class="card-text"><i class="fas fa-clock"></i> Próximas Avaliações</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de Avaliações por Mês -->
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line"></i> Avaliações por Mês</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="graficoAvaliacoesMes"></canvas>
                        </div>
                    </div>
                </div>
            </main>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../assets/js/notifications.js"></script>
    <script>
        let chartOcupacao, chartFrequenciaPizza, chartFrequenciaTurma, chartAvaliacoesMes;

        // Carregar estatísticas de frequência detalhada
        function carregarFrequenciaDetalhada() {
            $.ajax({
                url: 'relatorios_api.php?action=frequencia_detalhada',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        $('#totalPresentes').text(data.presentes || 0);
                        $('#totalAusentes').text(data.ausentes || 0);
                        $('#totalJustificados').text(data.justificados || 0);
                        $('#taxaFrequenciaCard').text((data.taxa_frequencia || 0) + '%');
                        
                        // Renderizar gráfico de pizza
                        renderizarGraficoFrequenciaPizza(data);
                    }
                }
            });
        }

        // Carregar frequência por turma
        function carregarFrequenciaPorTurma() {
            $.ajax({
                url: 'relatorios_api.php?action=frequencia_por_turma',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        renderizarGraficoFrequenciaTurma(response.data);
                        renderizarTabelaFrequenciaTurma(response.data);
                    }
                }
            });
        }

        // Renderizar gráfico de pizza de frequência
        function renderizarGraficoFrequenciaPizza(data) {
            const ctx = document.getElementById('graficoFrequenciaPizza');
            if (!ctx) return;
            
            if (chartFrequenciaPizza) {
                chartFrequenciaPizza.destroy();
            }

            chartFrequenciaPizza = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Presentes', 'Ausentes', 'Justificados'],
                    datasets: [{
                        data: [data.presentes || 0, data.ausentes || 0, data.justificados || 0],
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.8)',
                            'rgba(220, 53, 69, 0.8)',
                            'rgba(255, 193, 7, 0.8)'
                        ],
                        borderColor: [
                            'rgba(40, 167, 69, 1)',
                            'rgba(220, 53, 69, 1)',
                            'rgba(255, 193, 7, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Renderizar gráfico de frequência por turma
        function renderizarGraficoFrequenciaTurma(dados) {
            const ctx = document.getElementById('graficoFrequenciaTurma');
            if (!ctx) return;
            
            if (chartFrequenciaTurma) {
                chartFrequenciaTurma.destroy();
            }

            const labels = dados.map(d => d.nome_turma);
            const presentes = dados.map(d => d.presentes);
            const ausentes = dados.map(d => d.ausentes);
            const justificados = dados.map(d => d.justificados);

            chartFrequenciaTurma = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Presentes',
                        data: presentes,
                        backgroundColor: 'rgba(40, 167, 69, 0.6)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Ausentes',
                        data: ausentes,
                        backgroundColor: 'rgba(220, 53, 69, 0.6)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Justificados',
                        data: justificados,
                        backgroundColor: 'rgba(255, 193, 7, 0.6)',
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Renderizar tabela de frequência por turma
        function renderizarTabelaFrequenciaTurma(dados) {
            const tbody = $('#tabelaFrequenciaTurma tbody');
            if (!tbody.length) return;
            
            if (dados.length === 0) {
                tbody.html('<tr><td colspan="7" class="text-center">Nenhum dado de frequência disponível</td></tr>');
                return;
            }

            let html = '';
            dados.forEach(turma => {
                const taxa = turma.taxa_frequencia || 0;
                const corTaxa = taxa >= 80 ? 'text-success' : 
                               taxa >= 50 ? 'text-warning' : 'text-danger';

                html += `
                    <tr>
                        <td><strong>${turma.nome_turma}</strong></td>
                        <td>${turma.curso_nome}</td>
                        <td><span class="badge badge-success">${turma.presentes}</span></td>
                        <td><span class="badge badge-danger">${turma.ausentes}</span></td>
                        <td><span class="badge badge-warning">${turma.justificados}</span></td>
                        <td><strong>${turma.total}</strong></td>
                        <td class="${corTaxa}"><strong>${taxa}%</strong></td>
                    </tr>
                `;
            });
            tbody.html(html);
        }

        // Carregar estatísticas
        function carregarEstatisticas() {
            $.ajax({
                url: 'relatorios_api.php?action=estatisticas',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#totalUsuariosRel').text(response.data.total_usuarios || 0);
                        $('#totalMatriculas').text(response.data.total_matriculas || 0);
                        $('#taxaOcupacao').text((response.data.taxa_ocupacao || 0) + '%');
                        $('#taxaFrequencia').text((response.data.taxa_frequencia || 0) + '%');
                    }
                }
            });

            // Carregar total de planos
            $.ajax({
                url: 'planos_api.php?action=list',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const totalPlanos = response.data ? response.data.length : 0;
                        $('#total-planos').text(totalPlanos);
                    }
                }
            });
        }

        // Carregar ocupação
        function carregarOcupacao() {
            $.ajax({
                url: 'relatorios_api.php?action=ocupacao',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        renderizarGraficoOcupacao(response.data);
                        renderizarTabelaOcupacao(response.data);
                    }
                }
            });
        }

        function renderizarGraficoOcupacao(dados) {
            const ctx = document.getElementById('graficoOcupacao').getContext('2d');
            
            if (chartOcupacao) {
                chartOcupacao.destroy();
            }

            const labels = dados.map(d => d.nome_turma);
            const ocupacao = dados.map(d => {
                const total = d.vagas_totais;
                const ocupadas = total - d.vagas_disponiveis;
                return total > 0 ? Math.round((ocupadas / total) * 100) : 0;
            });

            chartOcupacao = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '% de Ocupação',
                        data: ocupacao,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }

        function renderizarTabelaOcupacao(dados) {
            const tbody = $('#tabelaOcupacao tbody');
            if (dados.length === 0) {
                tbody.html('<tr><td colspan="7" class="text-center">Nenhum dado disponível</td></tr>');
                return;
            }

            let html = '';
            dados.forEach(turma => {
                const ocupadas = turma.vagas_totais - turma.vagas_disponiveis;
                const percentual = turma.vagas_totais > 0 ? 
                    Math.round((ocupadas / turma.vagas_totais) * 100) : 0;
                const corPercentual = percentual >= 80 ? 'text-danger' : 
                                     percentual >= 50 ? 'text-warning' : 'text-success';

                html += `
                    <tr>
                        <td>${turma.nome_turma}</td>
                        <td>${turma.curso_nome}</td>
                        <td>${turma.unidade_nome}</td>
                        <td>${turma.vagas_totais}</td>
                        <td>${ocupadas}</td>
                        <td>${turma.vagas_disponiveis}</td>
                        <td class="${corPercentual}"><strong>${percentual}%</strong></td>
                    </tr>
                `;
            });
            tbody.html(html);
        }

        // Carregar estatísticas de avaliações
        function carregarEstatisticasAvaliacoes() {
            $.ajax({
                url: 'relatorios_api.php?action=avaliacoes_estatisticas',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#totalAvaliacoes').text(response.data.total_avaliacoes || 0);
                        $('#avaliacoesMes').text(response.data.avaliacoes_mes || 0);
                        $('#proximasAvaliacoes').text(response.data.proximas_avaliacoes || 0);
                    }
                }
            });
        }

        // Carregar gráfico de avaliações por mês
        function carregarAvaliacoesMensal() {
            $.ajax({
                url: 'relatorios_api.php?action=avaliacoes_mensal',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        renderizarGraficoAvaliacoesMes(response.data);
                    }
                }
            });
        }

        // Renderizar gráfico de avaliações por mês
        function renderizarGraficoAvaliacoesMes(dados) {
            const ctx = document.getElementById('graficoAvaliacoesMes');
            if (!ctx) return;
            
            if (chartAvaliacoesMes) {
                chartAvaliacoesMes.destroy();
            }

            const labels = dados.map(d => d.mes);
            const totais = dados.map(d => d.total);

            chartAvaliacoesMes = new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Avaliações',
                        data: totais,
                        backgroundColor: 'rgba(231, 76, 60, 0.6)',
                        borderColor: 'rgba(231, 76, 60, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        $(document).ready(function() {
            carregarEstatisticas();
            carregarOcupacao();
            carregarFrequenciaDetalhada();
            carregarFrequenciaPorTurma();
            carregarEstatisticasAvaliacoes();
            carregarAvaliacoesMensal();

            // Scroll suave para relatórios se vier do link do menu
            if (window.location.hash === '#relatorios') {
                setTimeout(function() {
                    $('html, body').animate({
                        scrollTop: $('#relatorios').offset().top - 20
                    }, 500);
                }, 100);
            }
        });
    </script>
</body>
</html>
