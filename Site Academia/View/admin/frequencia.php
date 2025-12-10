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
    <title>Gerenciar Frequência - TechFit Admin</title>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/notifications.css">
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
        .table-responsive {
            background: white;
            border-radius: 8px;
            padding: 20px;
        }
        .badge-presente {
            background-color: #28a745;
        }
        .badge-ausente {
            background-color: #dc3545;
        }
        .badge-justificado {
            background-color: #ffc107;
            color: #000;
        }
        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .btn-frequencia {
            min-width: 120px;
            margin: 2px;
            transition: all 0.3s;
        }
        .btn-frequencia.active {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .btn-frequencia.presente.active {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
        }
        .btn-frequencia.ausente.active {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        .btn-frequencia.justificado.active {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
        }
        .status-badge {
            font-size: 0.9em;
            padding: 5px 10px;
        }
        #cardTabelaFrequencia {
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
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
                <a class="nav-link active" href="frequencia.php">
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
            <h1 class="h2 mb-0">Gerenciar Frequência</h1>
            <p class="text-muted mb-0">Registre a frequência dos alunos de forma rápida e interativa</p>
        </div>

        <!-- Seletor de Turma e Aula -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Selecionar Turma e Aula</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label><strong>Selecionar Turma *</strong></label>
                        <select id="seletorTurma" class="form-control form-control-lg">
                            <option value="">-- Selecione uma turma --</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label><strong>Selecionar Aula *</strong></label>
                        <select id="seletorAula" class="form-control form-control-lg" disabled>
                            <option value="">-- Selecione primeiro a turma --</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela Interativa de Frequência -->
        <div class="card" id="cardTabelaFrequencia" style="display: none;">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users"></i> Registrar Frequência</h5>
                <button class="btn btn-light btn-sm" onclick="salvarTodasFrequencias()">
                    <i class="fas fa-save"></i> Salvar Todas as Marcações
                </button>
            </div>
            <div class="card-body">
                <div id="infoAula" class="alert alert-info mb-3">
                    <strong>Informações da Aula:</strong>
                    <span id="infoAulaTexto"></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="tabelaFrequenciaInterativa">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 30%;">Aluno</th>
                                <th style="width: 20%;">Email</th>
                                <th style="width: 45%;" class="text-center">Status de Frequência</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyFrequencia">
                            <tr>
                                <td colspan="4" class="text-center">
                                    <i class="fas fa-spinner fa-spin"></i> Carregando alunos...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tabela de Histórico (abaixo) -->
        <div class="card mt-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-history"></i> Histórico de Frequência</h5>
            </div>
            <div class="card-body">
                <div class="filters mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Filtrar por Turma</label>
                            <select id="turmaFilter" class="form-control">
                                <option value="">Todas as turmas</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Filtrar por Data</label>
                            <input type="date" id="dataFilter" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <div>
                                <button class="btn btn-primary" onclick="carregarFrequencia()">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <button class="btn btn-secondary" onclick="limparFiltros()">
                                    <i class="fas fa-times"></i> Limpar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="tabelaFrequencia">
                        <thead>
                            <tr>
                                <th>Aluno</th>
                                <th>Turma</th>
                                <th>Data da Aula</th>
                                <th>Horário</th>
                                <th>Status</th>
                                <th>Data Registro</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <i class="fas fa-info-circle"></i> Selecione uma turma e aula acima para registrar frequência
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Registrar Frequência -->
    <div class="modal fade" id="modalRegistrarFrequencia" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Registrar Frequência</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formRegistrarFrequencia">
                        <div class="form-group">
                            <label>Selecionar Turma *</label>
                            <select id="modalTurmaId" class="form-control" required>
                                <option value="">Selecione uma turma</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Selecionar Aula *</label>
                            <select id="modalAulaId" class="form-control" required>
                                <option value="">Selecione uma aula</option>
                            </select>
                            <small class="form-text text-muted">Selecione primeiro a turma para carregar as aulas</small>
                        </div>
                        <div class="form-group">
                            <label>Selecionar Aluno *</label>
                            <select id="modalUsuarioId" class="form-control" required>
                                <option value="">Selecione um aluno</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status *</label>
                            <select id="modalStatus" class="form-control" required>
                                <option value="presente">Presente</option>
                                <option value="ausente">Ausente</option>
                                <option value="justificado">Justificado</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Observações</label>
                            <textarea id="modalObservacoes" class="form-control" rows="3" placeholder="Opcional"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarFrequencia()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/notifications.js"></script>
    <script>
        let turmas = [];
        let aulas = [];
        let alunosTurma = [];
        let aulaSelecionada = null;
        let frequenciasRegistradas = {}; // {usuario_id: {status, id}}
        let frequenciasMarcadas = {}; // {usuario_id: status} - mudanças não salvas

        // Carregar turmas nos seletores
        function carregarTurmas() {
            $.ajax({
                url: 'turmas_api.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.turmas) {
                        turmas = response.turmas;
                        const selectPrincipal = $('#seletorTurma');
                        const selectFiltro = $('#turmaFilter');
                        
                        selectPrincipal.html('<option value="">-- Selecione uma turma --</option>');
                        selectFiltro.html('<option value="">Todas as turmas</option>');
                        
                        turmas.forEach(turma => {
                            const option = `<option value="${turma.id}">${turma.nome_turma}</option>`;
                            selectPrincipal.append(option);
                            selectFiltro.append(option);
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Erro ao carregar turmas:', xhr);
                    showNotification('Erro ao carregar turmas', 'error');
                }
            });
        }

        // Quando turma principal for selecionada
        $('#seletorTurma').on('change', function() {
            const turmaId = $(this).val();
            const selectAula = $('#seletorAula');
            
            if (turmaId) {
                selectAula.prop('disabled', false);
                carregarAulasTurma(turmaId, '#seletorAula');
            } else {
                selectAula.prop('disabled', true).html('<option value="">-- Selecione primeiro a turma --</option>');
                $('#cardTabelaFrequencia').hide();
            }
        });

        // Quando aula principal for selecionada
        $('#seletorAula').on('change', function() {
            const aulaId = $(this).val();
            if (aulaId) {
                carregarAlunosParaFrequencia(aulaId);
            } else {
                $('#cardTabelaFrequencia').hide();
            }
        });

        // Carregar alunos e frequências para a tabela interativa
        function carregarAlunosParaFrequencia(aulaId) {
            $('#tbodyFrequencia').html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Carregando alunos...</td></tr>');
            
            // Buscar informações da aula
            $.ajax({
                url: `../aulas_turma_api.php?id=${aulaId}`,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.aula) {
                        aulaSelecionada = response.aula;
                        
                        // Atualizar informações da aula
                        const dataAula = new Date(response.aula.data_aula).toLocaleDateString('pt-BR');
                        $('#infoAulaTexto').html(`
                            <strong>Turma:</strong> ${response.aula.nome_turma} | 
                            <strong>Data:</strong> ${dataAula} | 
                            <strong>Horário:</strong> ${response.aula.hora_inicio} - ${response.aula.hora_fim || 'N/A'}
                        `);
                        
                        // Buscar alunos matriculados na turma
                        $.ajax({
                            url: `../matriculas_api.php?todas=1`,
                            method: 'GET',
                            dataType: 'json',
                            success: function(matResponse) {
                                if (matResponse.success && matResponse.matriculas) {
                                    const turmaId = response.aula.turma_id;
                                    alunosTurma = matResponse.matriculas.filter(m => 
                                        m.turma_id == turmaId && m.status === 'confirmada'
                                    );
                                    
                                    // Buscar frequências já registradas para esta aula
                                    $.ajax({
                                        url: `../frequencia_api.php?action=aula&aula_id=${aulaId}`,
                                        method: 'GET',
                                        dataType: 'json',
                                        success: function(freqResponse) {
                                            frequenciasRegistradas = {};
                                            if (freqResponse.success && freqResponse.data) {
                                                freqResponse.data.forEach(freq => {
                                                    frequenciasRegistradas[freq.usuario_id] = {
                                                        status: freq.status,
                                                        id: freq.id
                                                    };
                                                });
                                            }
                                            
                                            // Inicializar frequenciasMarcadas com valores já registrados
                                            frequenciasMarcadas = {};
                                            Object.keys(frequenciasRegistradas).forEach(uid => {
                                                frequenciasMarcadas[uid] = frequenciasRegistradas[uid].status;
                                            });
                                            
                                            renderizarTabelaInterativa();
                                        },
                                        error: function() {
                                            frequenciasRegistradas = {};
                                            frequenciasMarcadas = {};
                                            renderizarTabelaInterativa();
                                        }
                                    });
                                } else {
                                    $('#tbodyFrequencia').html('<tr><td colspan="4" class="text-center text-danger">Nenhum aluno matriculado nesta turma</td></tr>');
                                }
                            },
                            error: function() {
                                showNotification('Erro ao carregar alunos', 'error');
                                $('#tbodyFrequencia').html('<tr><td colspan="4" class="text-center text-danger">Erro ao carregar alunos</td></tr>');
                            }
                        });
                    }
                },
                error: function() {
                    showNotification('Erro ao carregar informações da aula', 'error');
                }
            });
        }

        // Renderizar tabela interativa
        function renderizarTabelaInterativa() {
            const tbody = $('#tbodyFrequencia');
            
            if (!alunosTurma || alunosTurma.length === 0) {
                tbody.html('<tr><td colspan="4" class="text-center">Nenhum aluno matriculado nesta turma</td></tr>');
                $('#cardTabelaFrequencia').show();
                return;
            }
            
            let html = '';
            alunosTurma.forEach((aluno, index) => {
                const statusAtual = frequenciasMarcadas[aluno.usuario_id] || null;
                const jaRegistrado = frequenciasRegistradas[aluno.usuario_id] ? true : false;
                
                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td><strong>${aluno.usuario_nome}</strong></td>
                        <td>${aluno.email || 'N/A'}</td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <button type="button" 
                                    class="btn btn-frequencia presente ${statusAtual === 'presente' ? 'active' : ''}" 
                                    onclick="marcarFrequencia(${aluno.usuario_id}, 'presente')">
                                    <i class="fas fa-check"></i> Presente
                                </button>
                                <button type="button" 
                                    class="btn btn-frequencia ausente ${statusAtual === 'ausente' ? 'active' : ''}" 
                                    onclick="marcarFrequencia(${aluno.usuario_id}, 'ausente')">
                                    <i class="fas fa-times"></i> Ausente
                                </button>
                                <button type="button" 
                                    class="btn btn-frequencia justificado ${statusAtual === 'justificado' ? 'active' : ''}" 
                                    onclick="marcarFrequencia(${aluno.usuario_id}, 'justificado')">
                                    <i class="fas fa-exclamation-triangle"></i> Justificado
                                </button>
                            </div>
                            ${jaRegistrado ? '<br><small class="text-muted"><i class="fas fa-info-circle"></i> Já registrado</small>' : ''}
                        </td>
                    </tr>
                `;
            });
            
            tbody.html(html);
            $('#cardTabelaFrequencia').show();
        }

        // Marcar frequência de um aluno
        window.marcarFrequencia = function(usuarioId, status) {
            frequenciasMarcadas[usuarioId] = status;
            
            // Atualizar visual dos botões
            const row = $(`button[onclick*="marcarFrequencia(${usuarioId}"]`).closest('tr');
            row.find('.btn-frequencia').removeClass('active');
            row.find(`.btn-frequencia.${status}`).addClass('active');
        };

        // Salvar todas as frequências
        window.salvarTodasFrequencias = function() {
            if (!aulaSelecionada || !aulaSelecionada.id) {
                showNotification('Selecione uma aula primeiro', 'warning');
                return;
            }
            
            if (Object.keys(frequenciasMarcadas).length === 0) {
                showNotification('Nenhuma frequência marcada', 'warning');
                return;
            }
            
            const promises = [];
            let sucesso = 0;
            let erros = 0;
            
            // Salvar cada frequência
            Object.keys(frequenciasMarcadas).forEach(usuarioId => {
                const status = frequenciasMarcadas[usuarioId];
                const jaRegistrado = frequenciasRegistradas[usuarioId];
                
                if (jaRegistrado && frequenciasRegistradas[usuarioId].status === status) {
                    // Já está registrado com o mesmo status, pular
                    sucesso++;
                    return;
                }
                
                const data = {
                    aula_id: parseInt(aulaSelecionada.id),
                    usuario_id: parseInt(usuarioId),
                    status: status
                };
                
                const promise = $.ajax({
                    url: '../frequencia_api.php',
                    method: jaRegistrado ? 'PUT' : 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(jaRegistrado ? {
                        id: frequenciasRegistradas[usuarioId].id,
                        status: status
                    } : data),
                    dataType: 'json'
                }).then(function(response) {
                    if (response.success) {
                        sucesso++;
                    } else {
                        erros++;
                        console.error('Erro ao salvar frequência:', response.message);
                    }
                }).catch(function(xhr) {
                    erros++;
                    console.error('Erro na requisição:', xhr);
                });
                
                promises.push(promise);
            });
            
            // Aguardar todas as requisições
            Promise.all(promises).then(function() {
                if (erros === 0) {
                    showNotification(`Frequência de ${sucesso} aluno(s) registrada com sucesso!`, 'success');
                    // Recarregar dados
                    setTimeout(() => {
                        carregarAlunosParaFrequencia(aulaSelecionada.id);
                        carregarFrequencia(); // Atualizar histórico
                    }, 500);
                } else {
                    showNotification(`Salvo com alguns erros: ${sucesso} sucesso(s), ${erros} erro(s)`, 'warning');
                    // Recarregar mesmo com erros
                    setTimeout(() => {
                        carregarAlunosParaFrequencia(aulaSelecionada.id);
                    }, 500);
                }
            });
        };

        // Carregar aulas quando turma do filtro for selecionada
        $('#turmaFilter').on('change', function() {
            const turmaId = $(this).val();
            if (turmaId) {
                carregarAulasTurma(turmaId, '#aulaFilter');
            } else {
                $('#aulaFilter').html('<option value="">Todas as aulas</option>');
            }
        });

        // Função para carregar aulas de uma turma
        function carregarAulasTurma(turmaId, selectId) {
            $.ajax({
                url: `../aulas_turma_api.php?turma_id=${turmaId}`,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    const select = $(selectId);
                    if (response.success && response.aulas) {
                        aulas = response.aulas;
                        const isSeletorPrincipal = selectId === '#seletorAula';
                        select.html(isSeletorPrincipal ? '<option value="">-- Selecione uma aula --</option>' : '<option value="">Todas as aulas</option>');
                        
                        if (aulas && Array.isArray(aulas) && aulas.length > 0) {
                            aulas.forEach(aula => {
                                const data = new Date(aula.data_aula).toLocaleDateString('pt-BR');
                                select.append(`<option value="${aula.id}">${data} - ${aula.hora_inicio}</option>`);
                            });
                        } else {
                            select.html(isSeletorPrincipal ? '<option value="">Nenhuma aula disponível</option>' : '<option value="">Nenhuma aula</option>');
                            if (isSeletorPrincipal) {
                                showNotification('Nenhuma aula encontrada para esta turma', 'info');
                            }
                        }
                    } else {
                        select.html('<option value="">Erro ao carregar aulas</option>');
                        showNotification(response.message || 'Erro ao carregar aulas', 'error');
                    }
                },
                error: function(xhr) {
                    console.error('Erro ao carregar aulas:', xhr);
                    const select = $(selectId);
                    select.html('<option value="">Erro ao carregar aulas</option>');
                    showNotification('Erro ao carregar aulas. Tente novamente.', 'error');
                }
            });
        }

        // Carregar alunos matriculados quando aula for selecionada
        $('#modalAulaId').on('change', function() {
            const aulaId = $(this).val();
            if (aulaId) {
                carregarAlunosAula(aulaId);
            } else {
                $('#modalUsuarioId').html('<option value="">Selecione um aluno</option>');
            }
        });

        function carregarAlunosAula(aulaId) {
            $.ajax({
                url: `../aulas_turma_api.php?id=${aulaId}`,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.aula) {
                        const turmaId = response.aula.turma_id;
                        // Buscar alunos matriculados na turma (admin pode ver todas)
                        $.ajax({
                            url: `../matriculas_api.php?todas=1`,
                            method: 'GET',
                            dataType: 'json',
                            success: function(matResponse) {
                                if (matResponse.success && matResponse.matriculas) {
                                    const select = $('#modalUsuarioId');
                                    select.html('<option value="">Selecione um aluno</option>');
                                    // Filtrar apenas alunos desta turma com status confirmada
                                    const alunosTurma = matResponse.matriculas.filter(m => 
                                        m.turma_id == turmaId && m.status === 'confirmada'
                                    );
                                    alunosTurma.forEach(matricula => {
                                        select.append(`<option value="${matricula.usuario_id}">${matricula.usuario_nome}</option>`);
                                    });
                                    if (alunosTurma.length === 0) {
                                        select.html('<option value="">Nenhum aluno matriculado nesta turma</option>');
                                    }
                                }
                            },
                            error: function() {
                                showNotification('Erro ao carregar alunos', 'error');
                            }
                        });
                    }
                }
            });
        }

        // Carregar frequência
        function carregarFrequencia() {
            const turmaId = $('#turmaFilter').val();
            const aulaId = $('#aulaFilter').val();
            const data = $('#dataFilter').val();

            let url = '../frequencia_api.php?action=all';
            const params = [];
            if (turmaId) params.push(`turma_id=${turmaId}`);
            if (aulaId) params.push(`aula_id=${aulaId}`);
            if (data) params.push(`data=${data}`);
            if (params.length > 0) url += '&' + params.join('&');

            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        renderizarTabela(Array.isArray(response.data) ? response.data : []);
                    } else {
                        showNotification(response.message || 'Erro ao carregar frequência', 'error');
                        renderizarTabela([]);
                    }
                },
                error: function() {
                    showNotification('Erro ao conectar com o servidor', 'error');
                }
            });
        }

        function renderizarTabela(dados) {
            const tbody = $('#tabelaFrequencia tbody');
            if (dados.length === 0) {
                tbody.html('<tr><td colspan="7" class="text-center">Nenhum registro encontrado</td></tr>');
                return;
            }

            let html = '';
            dados.forEach(freq => {
                const statusClass = freq.status === 'presente' ? 'badge-presente' : 
                                  freq.status === 'ausente' ? 'badge-ausente' : 'badge-justificado';
                const statusLabel = freq.status === 'presente' ? 'Presente' : 
                                   freq.status === 'ausente' ? 'Ausente' : 'Justificado';
                const dataAula = new Date(freq.data_aula).toLocaleDateString('pt-BR');
                const dataRegistro = new Date(freq.data_presenca).toLocaleDateString('pt-BR');

                html += `
                    <tr>
                        <td>${freq.usuario_nome || 'N/A'}</td>
                        <td>${freq.nome_turma || 'N/A'}</td>
                        <td>${dataAula}</td>
                        <td>${freq.hora_inicio || 'N/A'}</td>
                        <td><span class="badge ${statusClass}">${statusLabel}</span></td>
                        <td>${dataRegistro}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editarFrequencia(${freq.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deletarFrequencia(${freq.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            tbody.html(html);
        }

        function limparFiltros() {
            $('#turmaFilter').val('');
            $('#aulaFilter').val('');
            $('#dataFilter').val('');
            carregarFrequencia();
        }

        // Funções no escopo global para serem acessíveis via onclick
        window.abrirModalRegistrarFrequencia = function() {
            // Limpar campos primeiro
            $('#modalTurmaId').val('');
            $('#modalAulaId').val('').html('<option value="">Selecione uma aula</option>');
            $('#modalUsuarioId').val('').html('<option value="">Selecione um aluno</option>');
            $('#modalStatus').val('presente');
            $('#modalObservacoes').val('');

            // Carregar turmas no select do modal - sempre recarregar para garantir que está atualizado
            const selectTurma = $('#modalTurmaId');
            selectTurma.html('<option value="">Carregando turmas...</option>');
            
            // Sempre carregar turmas quando abrir o modal
            $.ajax({
                url: 'turmas_api.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    selectTurma.html('<option value="">Selecione uma turma</option>');
                    if (response.success && response.turmas && response.turmas.length > 0) {
                        turmas = response.turmas; // Atualizar variável global
                        response.turmas.forEach(turma => {
                            selectTurma.append(`<option value="${turma.id}">${turma.nome_turma}</option>`);
                        });
                    } else {
                        selectTurma.html('<option value="">Nenhuma turma disponível</option>');
                        showNotification('Nenhuma turma encontrada', 'warning');
                    }
                },
                error: function(xhr) {
                    console.error('Erro ao carregar turmas:', xhr);
                    selectTurma.html('<option value="">Erro ao carregar turmas</option>');
                    showNotification('Erro ao carregar turmas. Tente novamente.', 'error');
                }
            });

            // Carregar aulas quando turma for selecionada no modal
            $('#modalTurmaId').off('change').on('change', function() {
                const turmaId = $(this).val();
                if (turmaId) {
                    carregarAulasTurma(turmaId, '#modalAulaId');
                    $('#modalUsuarioId').html('<option value="">Selecione um aluno</option>');
                } else {
                    $('#modalAulaId').html('<option value="">Selecione uma aula</option>');
                    $('#modalUsuarioId').html('<option value="">Selecione um aluno</option>');
                }
            });

            // Mostrar modal
            $('#modalRegistrarFrequencia').modal('show');
        };

        window.salvarFrequencia = function() {
            const aulaId = $('#modalAulaId').val();
            const usuarioId = $('#modalUsuarioId').val();
            const status = $('#modalStatus').val();
            const observacoes = $('#modalObservacoes').val();

            if (!aulaId || !usuarioId) {
                showNotification('Preencha todos os campos obrigatórios', 'error');
                return;
            }

            const data = {
                aula_id: parseInt(aulaId),
                usuario_id: parseInt(usuarioId),
                status: status,
                observacoes: observacoes || null
            };

            $.ajax({
                url: '../frequencia_api.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification(response.message, 'success');
                        $('#modalRegistrarFrequencia').modal('hide');
                        carregarFrequencia();
                    } else {
                        showNotification(response.message, 'error');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    showNotification(response?.message || 'Erro ao registrar frequência', 'error');
                }
            });
        };

        function editarFrequencia(id) {
            // Buscar dados da frequência
            $.ajax({
                url: `../frequencia_api.php?action=all`,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data && Array.isArray(response.data)) {
                        const freq = response.data.find(f => f.id == id);
                        if (freq) {
                            // Preencher modal com dados existentes
                            $('#modalAulaId').val(freq.aula_id);
                            $('#modalUsuarioId').val(freq.usuario_id);
                            $('#modalStatus').val(freq.status);
                            $('#modalObservacoes').val(freq.observacoes || '');
                            
                            // Carregar alunos da turma
                            carregarAlunosAula(freq.aula_id);
                            
                            // Atualizar frequência ao salvar
                            const originalSalvar = window.salvarFrequencia;
                            window.salvarFrequencia = function() {
                                const data = {
                                    id: id,
                                    status: $('#modalStatus').val(),
                                    observacoes: $('#modalObservacoes').val() || null
                                };

                                $.ajax({
                                    url: '../frequencia_api.php',
                                    method: 'PUT',
                                    contentType: 'application/json',
                                    data: JSON.stringify(data),
                                    dataType: 'json',
                                    success: function(response) {
                                        if (response.success) {
                                            showNotification(response.message, 'success');
                                            $('#modalRegistrarFrequencia').modal('hide');
                                            carregarFrequencia();
                                            window.salvarFrequencia = originalSalvar;
                                        } else {
                                            showNotification(response.message, 'error');
                                        }
                                    },
                                    error: function() {
                                        showNotification('Erro ao atualizar frequência', 'error');
                                    }
                                });
                            };

                            abrirModalRegistrarFrequencia();
                        } else {
                            showNotification('Frequência não encontrada', 'error');
                        }
                    } else {
                        showNotification(response.message || 'Erro ao carregar dados da frequência', 'error');
                    }
                },
                error: function() {
                    showNotification('Erro ao conectar com o servidor', 'error');
                }
            });
        }

        async function deletarFrequencia(id) {
            const confirmed = await showConfirm('Tem certeza que deseja remover este registro de frequência?', 'Confirmar Exclusão', 'warning');
            if (!confirmed) return;

            $.ajax({
                url: `../frequencia_api.php?id=${id}`,
                method: 'DELETE',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification(response.message, 'success');
                        carregarFrequencia();
                    } else {
                        showNotification(response.message, 'error');
                    }
                },
                error: function() {
                    showNotification('Erro ao remover frequência', 'error');
                }
            });
        }

        $(document).ready(function() {
            carregarTurmas();
            carregarFrequencia();
        });
    </script>
</body>
</html>

