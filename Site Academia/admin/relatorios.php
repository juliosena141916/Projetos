<?php
require_once 'check_admin.php';

// Incluir arquivo de conexão
$conexao_path = dirname(__FILE__) . '/../includes/conexao.php';
require_once $conexao_path;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - TechFit Admin</title>
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
        .card {
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
                <a class="nav-link" href="index.php">
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
                <a class="nav-link active" href="relatorios.php">
                    <i class="fas fa-chart-bar"></i> Relatórios
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
            <h1 class="h2 mb-0">Relatórios Gerenciais</h1>
            <p class="text-muted mb-0">Análises e estatísticas do sistema</p>
        </div>

        <!-- Cards de Estatísticas -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h3 id="totalUsuarios">-</h3>
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

        <!-- Gráfico de Frequência -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Frequência Mensal</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="graficoFrequencia"></canvas>
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
    </main>

    <script src="../assets/js/notifications.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        let chartOcupacao, chartFrequencia;

        function carregarEstatisticas() {
            $.ajax({
                url: 'relatorios_api.php?action=estatisticas',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#totalUsuarios').text(response.data.total_usuarios || 0);
                        $('#totalMatriculas').text(response.data.total_matriculas || 0);
                        $('#taxaOcupacao').text((response.data.taxa_ocupacao || 0) + '%');
                        $('#taxaFrequencia').text((response.data.taxa_frequencia || 0) + '%');
                    }
                }
            });
        }

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

        function carregarFrequencia() {
            $.ajax({
                url: 'relatorios_api.php?action=frequencia_mensal',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        renderizarGraficoFrequencia(response.data);
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

        function renderizarGraficoFrequencia(dados) {
            const ctx = document.getElementById('graficoFrequencia').getContext('2d');
            
            if (chartFrequencia) {
                chartFrequencia.destroy();
            }

            const labels = dados.map(d => d.mes);
            const presentes = dados.map(d => d.presentes);
            const ausentes = dados.map(d => d.ausentes);

            chartFrequencia = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Presentes',
                        data: presentes,
                        borderColor: 'rgba(40, 167, 69, 1)',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Ausentes',
                        data: ausentes,
                        borderColor: 'rgba(220, 53, 69, 1)',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
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

        $(document).ready(function() {
            carregarEstatisticas();
            carregarOcupacao();
            carregarFrequencia();
        });
    </script>
</body>
</html>

