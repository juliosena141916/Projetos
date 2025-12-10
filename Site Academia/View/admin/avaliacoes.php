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
    <title>Avaliações Físicas - TechFit Admin</title>
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
        .card {
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
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
                <a class="nav-link active" href="avaliacoes.php">
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
            <h1 class="h2 mb-0">Avaliações Físicas</h1>
            <p class="text-muted mb-0">Gerencie as avaliações físicas dos alunos</p>
            <button class="btn btn-primary mt-2" onclick="abrirModalNovaAvaliacao()">
                <i class="fas fa-plus"></i> Nova Avaliação
            </button>
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-list"></i> Avaliações Registradas</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Filtrar por Usuário</label>
                        <select id="usuarioFilter" class="form-control">
                            <option value="">Todos os usuários</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary mt-4" onclick="carregarAvaliacoes()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="tabelaAvaliacoes">
                        <thead>
                            <tr>
                                <th>Aluno</th>
                                <th>Data</th>
                                <th>Peso (kg)</th>
                                <th>Altura (m)</th>
                                <th>IMC</th>
                                <th>% Gordura</th>
                                <th>Próxima Avaliação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center">
                                    <i class="fas fa-spinner fa-spin"></i> Carregando...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Nova Avaliação -->
        <div class="modal fade" id="modalNovaAvaliacao" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nova Avaliação Física</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formNovaAvaliacao">
                            <div class="form-group">
                                <label>Aluno *</label>
                                <select id="avaliacaoUsuario" class="form-control" required>
                                    <option value="">Selecione um aluno</option>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Data da Avaliação *</label>
                                        <input type="date" id="avaliacaoData" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Peso (kg)</label>
                                        <input type="number" step="0.1" id="avaliacaoPeso" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Altura (m)</label>
                                        <input type="number" step="0.01" id="avaliacaoAltura" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>% Gordura Corporal</label>
                                        <input type="number" step="0.1" id="avaliacaoGordura" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>% Massa Magra</label>
                                        <input type="number" step="0.1" id="avaliacaoMassaMagra" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Próxima Avaliação</label>
                                        <input type="date" id="avaliacaoProxima" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Observações</label>
                                <textarea id="avaliacaoObservacoes" class="form-control" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" onclick="salvarAvaliacao()">Salvar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Ver Detalhes -->
        <div class="modal fade" id="modalDetalhesAvaliacao" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-heartbeat"></i> Detalhes da Avaliação Física</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="detalhesAvaliacaoBody">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin"></i> Carregando...
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Editar Avaliação -->
        <div class="modal fade" id="modalEditarAvaliacao" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Editar Avaliação Física</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formEditarAvaliacao">
                            <input type="hidden" id="editarAvaliacaoId">
                            <div class="form-group">
                                <label>Aluno</label>
                                <input type="text" id="editarAvaliacaoUsuario" class="form-control" readonly>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Data da Avaliação *</label>
                                        <input type="date" id="editarAvaliacaoData" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Peso (kg)</label>
                                        <input type="number" step="0.1" id="editarAvaliacaoPeso" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Altura (m)</label>
                                        <input type="number" step="0.01" id="editarAvaliacaoAltura" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>% Gordura Corporal</label>
                                        <input type="number" step="0.1" id="editarAvaliacaoGordura" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>% Massa Magra</label>
                                        <input type="number" step="0.1" id="editarAvaliacaoMassaMagra" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Próxima Avaliação</label>
                                        <input type="date" id="editarAvaliacaoProxima" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Observações</label>
                                <textarea id="editarAvaliacaoObservacoes" class="form-control" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-warning" onclick="salvarEdicaoAvaliacao()">Salvar Alterações</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/notifications.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Carregar usuários
        function carregarUsuarios() {
            $.ajax({
                url: 'usuarios_api.php?action=list',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const selectFilter = $('#usuarioFilter');
                        const selectModal = $('#avaliacaoUsuario');
                        selectFilter.html('<option value="">Todos os usuários</option>');
                        selectModal.html('<option value="">Selecione um aluno</option>');
                        response.data.forEach(usuario => {
                            selectFilter.append(`<option value="${usuario.id}">${usuario.nome}</option>`);
                            selectModal.append(`<option value="${usuario.id}">${usuario.nome}</option>`);
                        });
                    }
                }
            });
        }

        // Carregar avaliações
        function carregarAvaliacoes() {
            const usuarioId = $('#usuarioFilter').val();
            let url = '../avaliacoes_api.php?action=all';
            if (usuarioId) url += `&usuario_id=${usuarioId}`;

            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        renderizarTabela(response.data);
                    } else {
                        showNotification(response.message || 'Erro ao carregar avaliações', 'error');
                    }
                },
                error: function() {
                    showNotification('Erro ao conectar com o servidor', 'error');
                }
            });
        }

        function renderizarTabela(dados) {
            const tbody = $('#tabelaAvaliacoes tbody');
            if (dados.length === 0) {
                tbody.html('<tr><td colspan="8" class="text-center">Nenhuma avaliação encontrada</td></tr>');
                return;
            }

            let html = '';
            dados.forEach(av => {
                const data = new Date(av.data_avaliacao).toLocaleDateString('pt-BR');
                const proxima = av.proxima_avaliacao ? new Date(av.proxima_avaliacao).toLocaleDateString('pt-BR') : 'N/A';
                const imc = (av.imc && !isNaN(parseFloat(av.imc))) ? parseFloat(av.imc).toFixed(1) : 'N/A';
                const peso = (av.peso && !isNaN(parseFloat(av.peso))) ? parseFloat(av.peso).toFixed(1) : 'N/A';
                const altura = (av.altura && !isNaN(parseFloat(av.altura))) ? parseFloat(av.altura).toFixed(2) : 'N/A';
                const gordura = (av.percentual_gordura && !isNaN(parseFloat(av.percentual_gordura))) ? parseFloat(av.percentual_gordura).toFixed(1) + '%' : 'N/A';

                html += `
                    <tr>
                        <td>${av.usuario_nome || 'N/A'}</td>
                        <td>${data}</td>
                        <td>${peso}</td>
                        <td>${altura}</td>
                        <td>${imc}</td>
                        <td>${gordura}</td>
                        <td>${proxima}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="verDetalhes(${av.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="editarAvaliacao(${av.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deletarAvaliacao(${av.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            tbody.html(html);
        }

        function abrirModalNovaAvaliacao() {
            $('#formNovaAvaliacao')[0].reset();
            $('#avaliacaoData').val(new Date().toISOString().split('T')[0]);
            $('#modalNovaAvaliacao').modal('show');
        }

        function salvarAvaliacao() {
            const data = {
                usuario_id: $('#avaliacaoUsuario').val(),
                data_avaliacao: $('#avaliacaoData').val(),
                peso: $('#avaliacaoPeso').val() || null,
                altura: $('#avaliacaoAltura').val() || null,
                percentual_gordura: $('#avaliacaoGordura').val() || null,
                percentual_massa_magra: $('#avaliacaoMassaMagra').val() || null,
                proxima_avaliacao: $('#avaliacaoProxima').val() || null,
                observacoes: $('#avaliacaoObservacoes').val() || null
            };

            if (!data.usuario_id || !data.data_avaliacao) {
                showNotification('Preencha os campos obrigatórios', 'error');
                return;
            }

            $.ajax({
                url: '../avaliacoes_api.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification(response.message, 'success');
                        $('#modalNovaAvaliacao').modal('hide');
                        carregarAvaliacoes();
                    } else {
                        showNotification(response.message, 'error');
                    }
                },
                error: function() {
                    showNotification('Erro ao salvar avaliação', 'error');
                }
            });
        }

        function verDetalhes(id) {
            $('#detalhesAvaliacaoBody').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Carregando...</div>');
            $('#modalDetalhesAvaliacao').modal('show');
            
            // Buscar detalhes da avaliação
            $.ajax({
                url: `../avaliacoes_api.php?action=get&id=${id}`,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const av = response.data;
                        
                        // Buscar todas as avaliações do usuário para o gráfico
                        $.ajax({
                            url: `../avaliacoes_api.php?action=usuario&usuario_id=${av.usuario_id}`,
                            method: 'GET',
                            dataType: 'json',
                            success: function(histResponse) {
                                renderizarDetalhesAvaliacao(av, histResponse.success ? histResponse.data : []);
                            },
                            error: function() {
                                renderizarDetalhesAvaliacao(av, []);
                            }
                        });
                    } else {
                        $('#detalhesAvaliacaoBody').html('<div class="alert alert-danger">Erro ao carregar avaliação</div>');
                    }
                },
                error: function() {
                    $('#detalhesAvaliacaoBody').html('<div class="alert alert-danger">Erro ao conectar com o servidor</div>');
                }
            });
        }

        function renderizarDetalhesAvaliacao(av, historico) {
            const imc = (av.imc && !isNaN(parseFloat(av.imc))) ? parseFloat(av.imc).toFixed(1) : 'N/A';
            const peso = (av.peso && !isNaN(parseFloat(av.peso))) ? parseFloat(av.peso).toFixed(1) : 'N/A';
            const altura = (av.altura && !isNaN(parseFloat(av.altura))) ? parseFloat(av.altura).toFixed(2) : 'N/A';
            const gordura = (av.percentual_gordura && !isNaN(parseFloat(av.percentual_gordura))) ? parseFloat(av.percentual_gordura).toFixed(1) : 'N/A';
            const massaMagra = (av.percentual_massa_magra && !isNaN(parseFloat(av.percentual_massa_magra))) ? parseFloat(av.percentual_massa_magra).toFixed(1) : 'N/A';
            
            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-user"></i> Informações do Aluno</h5>
                        <table class="table table-bordered">
                            <tr><th width="40%">Aluno:</th><td>${av.usuario_nome || 'N/A'}</td></tr>
                            <tr><th>Data da Avaliação:</th><td>${new Date(av.data_avaliacao).toLocaleDateString('pt-BR')}</td></tr>
                            <tr><th>Avaliador:</th><td>${av.avaliador_nome || 'N/A'}</td></tr>
                            ${av.proxima_avaliacao ? `<tr><th>Próxima Avaliação:</th><td>${new Date(av.proxima_avaliacao).toLocaleDateString('pt-BR')}</td></tr>` : ''}
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-chart-line"></i> Medidas Corporais</h5>
                        <table class="table table-bordered">
                            <tr><th width="40%">Peso:</th><td>${peso} kg</td></tr>
                            <tr><th>Altura:</th><td>${altura} m</td></tr>
                            <tr><th>IMC:</th><td><strong>${imc}</strong></td></tr>
                            <tr><th>% Gordura:</th><td>${gordura}%</td></tr>
                            <tr><th>% Massa Magra:</th><td>${massaMagra}%</td></tr>
                        </table>
                    </div>
                </div>
            `;
            
            // Adicionar circunferências se existirem
            const circunferencias = [];
            if (av.circunferencia_peito) circunferencias.push({label: 'Peito', valor: av.circunferencia_peito});
            if (av.circunferencia_cintura) circunferencias.push({label: 'Cintura', valor: av.circunferencia_cintura});
            if (av.circunferencia_quadril) circunferencias.push({label: 'Quadril', valor: av.circunferencia_quadril});
            if (av.circunferencia_braco) circunferencias.push({label: 'Braço', valor: av.circunferencia_braco});
            if (av.circunferencia_coxa) circunferencias.push({label: 'Coxa', valor: av.circunferencia_coxa});
            
            if (circunferencias.length > 0) {
                html += `
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5><i class="fas fa-ruler"></i> Circunferências (cm)</h5>
                            <div class="row">
                `;
                circunferencias.forEach(c => {
                    html += `<div class="col-md-2"><strong>${c.label}:</strong> ${c.valor} cm</div>`;
                });
                html += `</div></div></div>`;
            }
            
            // Adicionar outros dados se existirem
            const outrosDados = [];
            if (av.pressao_arterial_sistolica && av.pressao_arterial_diastolica) {
                outrosDados.push({label: 'Pressão Arterial', valor: `${av.pressao_arterial_sistolica}/${av.pressao_arterial_diastolica} mmHg`});
            }
            if (av.frequencia_cardiaca_repouso) outrosDados.push({label: 'FC Repouso', valor: `${av.frequencia_cardiaca_repouso} bpm`});
            if (av.flexibilidade_cm) outrosDados.push({label: 'Flexibilidade', valor: `${av.flexibilidade_cm} cm`});
            if (av.forca_abdominal) outrosDados.push({label: 'Força Abdominal', valor: `${av.forca_abdominal} repetições`});
            
            if (outrosDados.length > 0) {
                html += `<div class="row mt-3"><div class="col-12"><h5><i class="fas fa-clipboard-list"></i> Outros Dados</h5><div class="row">`;
                outrosDados.forEach(d => {
                    html += `<div class="col-md-3"><strong>${d.label}:</strong> ${d.valor}</div>`;
                });
                html += `</div></div></div>`;
            }
            
            // Observações
            if (av.observacoes) {
                html += `
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5><i class="fas fa-comment"></i> Observações</h5>
                            <div class="alert alert-info">${av.observacoes}</div>
                        </div>
                    </div>
                `;
            }
            
            // Gráfico de evolução se houver histórico
            if (historico && historico.length > 1) {
                html += `
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5><i class="fas fa-chart-line"></i> Evolução do Aluno</h5>
                            <div style="position: relative; height: 300px;">
                                <canvas id="graficoEvolucaoDetalhes"></canvas>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            $('#detalhesAvaliacaoBody').html(html);
            
            // Renderizar gráfico se houver histórico
            if (historico && historico.length > 1) {
                setTimeout(() => renderizarGraficoEvolucaoDetalhes(historico), 100);
            }
        }

        function renderizarGraficoEvolucaoDetalhes(historico) {
            const ctx = document.getElementById('graficoEvolucaoDetalhes');
            if (!ctx) return;
            
            const ordenado = historico.sort((a, b) => new Date(a.data_avaliacao) - new Date(b.data_avaliacao));
            const labels = ordenado.map(av => new Date(av.data_avaliacao).toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' }));
            const dadosPeso = ordenado.map(av => (av.peso && !isNaN(parseFloat(av.peso))) ? parseFloat(av.peso) : null);
            const dadosIMC = ordenado.map(av => (av.imc && !isNaN(parseFloat(av.imc))) ? parseFloat(av.imc) : null);
            const dadosGordura = ordenado.map(av => (av.percentual_gordura && !isNaN(parseFloat(av.percentual_gordura))) ? parseFloat(av.percentual_gordura) : null);
            
            new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        dadosPeso.some(p => p !== null) ? {
                            label: 'Peso (kg)',
                            data: dadosPeso,
                            borderColor: 'rgba(54, 162, 235, 1)',
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            tension: 0.4,
                            yAxisID: 'y'
                        } : null,
                        dadosIMC.some(i => i !== null) ? {
                            label: 'IMC',
                            data: dadosIMC,
                            borderColor: 'rgba(255, 99, 132, 1)',
                            backgroundColor: 'rgba(255, 99, 132, 0.1)',
                            tension: 0.4,
                            yAxisID: 'y1'
                        } : null,
                        dadosGordura.some(g => g !== null) ? {
                            label: '% Gordura',
                            data: dadosGordura,
                            borderColor: 'rgba(255, 206, 86, 1)',
                            backgroundColor: 'rgba(255, 206, 86, 0.1)',
                            tension: 0.4,
                            yAxisID: 'y2'
                        } : null
                    ].filter(d => d !== null)
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left'
                        },
                        y1: {
                            type: 'linear',
                            display: false,
                            position: 'right',
                            grid: { drawOnChartArea: false }
                        },
                        y2: {
                            type: 'linear',
                            display: false,
                            position: 'right',
                            grid: { drawOnChartArea: false }
                        }
                    }
                }
            });
        }

        function editarAvaliacao(id) {
            $.ajax({
                url: `../avaliacoes_api.php?action=get&id=${id}`,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const av = response.data;
                        $('#editarAvaliacaoId').val(av.id);
                        $('#editarAvaliacaoUsuario').val(av.usuario_nome || 'N/A');
                        $('#editarAvaliacaoData').val(av.data_avaliacao.split(' ')[0]);
                        $('#editarAvaliacaoPeso').val(av.peso || '');
                        $('#editarAvaliacaoAltura').val(av.altura || '');
                        $('#editarAvaliacaoGordura').val(av.percentual_gordura || '');
                        $('#editarAvaliacaoMassaMagra').val(av.percentual_massa_magra || '');
                        $('#editarAvaliacaoProxima').val(av.proxima_avaliacao ? av.proxima_avaliacao.split(' ')[0] : '');
                        $('#editarAvaliacaoObservacoes').val(av.observacoes || '');
                        $('#modalEditarAvaliacao').modal('show');
                    } else {
                        showNotification('Erro ao carregar avaliação', 'error');
                    }
                },
                error: function() {
                    showNotification('Erro ao conectar com o servidor', 'error');
                }
            });
        }

        function salvarEdicaoAvaliacao() {
            const data = {
                id: $('#editarAvaliacaoId').val(),
                data_avaliacao: $('#editarAvaliacaoData').val(),
                peso: $('#editarAvaliacaoPeso').val() || null,
                altura: $('#editarAvaliacaoAltura').val() || null,
                percentual_gordura: $('#editarAvaliacaoGordura').val() || null,
                percentual_massa_magra: $('#editarAvaliacaoMassaMagra').val() || null,
                proxima_avaliacao: $('#editarAvaliacaoProxima').val() || null,
                observacoes: $('#editarAvaliacaoObservacoes').val() || null
            };

            if (!data.data_avaliacao) {
                showNotification('Preencha a data da avaliação', 'error');
                return;
            }

            $.ajax({
                url: '../avaliacoes_api.php',
                method: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification(response.message, 'success');
                        $('#modalEditarAvaliacao').modal('hide');
                        carregarAvaliacoes();
                    } else {
                        showNotification(response.message, 'error');
                    }
                },
                error: function() {
                    showNotification('Erro ao salvar alterações', 'error');
                }
            });
        }

        async function deletarAvaliacao(id) {
            const confirmed = await showConfirm('Tem certeza que deseja remover esta avaliação?', 'Confirmar Exclusão', 'warning');
            if (!confirmed) return;

            $.ajax({
                url: `../avaliacoes_api.php?id=${id}`,
                method: 'DELETE',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification(response.message, 'success');
                        carregarAvaliacoes();
                    } else {
                        showNotification(response.message, 'error');
                    }
                },
                error: function() {
                    showNotification('Erro ao remover avaliação', 'error');
                }
            });
        }

        $(document).ready(function() {
            carregarUsuarios();
            carregarAvaliacoes();
        });
    </script>
</body>
</html>

