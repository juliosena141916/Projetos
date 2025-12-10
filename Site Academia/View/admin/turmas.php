<?php

require_once 'check_admin.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Turmas - Admin TechFit</title>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
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
        .page-header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        .card-header {
            background-color: #007bff;
            color: white;
        }
        .turma-card {
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .turma-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .badge-status {
            font-size: 0.85rem;
            padding: 5px 10px;
        }
        .modal-header {
            background-color: #007bff;
            color: white;
        }
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .checkbox-group .form-check {
            flex: 0 0 auto;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .btn-sm {
            margin: 2px;
        }
    </style>
    <link rel="stylesheet" href="../assets/css/notifications.css">
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
	                <a class="nav-link active" href="turmas.php">
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
            <h1 class="h2 mb-0">Gerenciar Turmas</h1>
            <p class="text-muted mb-0">Adicione, edite e gerencie as turmas de cursos.</p>
            <button class="btn btn-primary mt-2" onclick="abrirModalNovaTurma()">
                        <i class="fas fa-plus"></i> Nova Turma
                    </button>
                </div>

        <!-- Filtros -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <select class="form-control" id="filtroCurso">
                            <option value="">Todos os cursos</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" id="filtroUnidade">
                            <option value="">Todas as unidades</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" id="filtroStatus">
                            <option value="">Todos os status</option>
                            <option value="planejada">Planejada</option>
                            <option value="em_andamento">Em Andamento</option>
                            <option value="concluida">Concluída</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Turmas -->
        <div id="turmasList" class="row">
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Carregando...</span>
                </div>
            </div>
        </div>
            </main>

    <!-- Modal Nova/Editar Turma -->
    <div class="modal fade" id="modalTurma" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTurmaTitle">Nova Turma</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formTurma">
                        <input type="hidden" id="turmaId">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Curso *</label>
                                    <select class="form-control" id="cursoId" required>
                                        <option value="">Selecione um curso</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Unidade *</label>
                                    <select class="form-control" id="unidadeId" required>
                                        <option value="">Selecione uma unidade</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Nome da Turma *</label>
                            <input type="text" class="form-control" id="nomeTurma" required 
                                   placeholder="Ex: Turma Noturna - Janeiro 2025">
                        </div>

                        <div class="form-group">
                            <label>Instrutor</label>
                            <input type="text" class="form-control" id="instrutor" 
                                   placeholder="Nome do instrutor">
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Vagas Totais *</label>
                                    <input type="number" class="form-control" id="vagasTotais" 
                                           value="20" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Data Início *</label>
                                    <input type="date" class="form-control" id="dataInicio" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Data Fim *</label>
                                    <input type="date" class="form-control" id="dataFim" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Dias da Semana *</label>
                            <div class="checkbox-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="0" id="dia0">
                                    <label class="form-check-label" for="dia0">Domingo</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="dia1">
                                    <label class="form-check-label" for="dia1">Segunda</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="2" id="dia2">
                                    <label class="form-check-label" for="dia2">Terça</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="3" id="dia3">
                                    <label class="form-check-label" for="dia3">Quarta</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="4" id="dia4">
                                    <label class="form-check-label" for="dia4">Quinta</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="5" id="dia5">
                                    <label class="form-check-label" for="dia5">Sexta</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="6" id="dia6">
                                    <label class="form-check-label" for="dia6">Sábado</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Horário Início *</label>
                                    <input type="time" class="form-control" id="horaInicio" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Horário Fim *</label>
                                    <input type="time" class="form-control" id="horaFim" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Sala Padrão</label>
                                    <input type="text" class="form-control" id="salaPadrao" 
                                           placeholder="Ex: Sala 1">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" id="status">
                                <option value="planejada">Planejada</option>
                                <option value="em_andamento">Em Andamento</option>
                                <option value="concluida">Concluída</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarTurma()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="../assets/js/notifications.js"></script>
    <script>
        let turmas = [];
        let cursos = [];
        let unidades = [];

        $(document).ready(function() {
            carregarDados();
            
            // Filtros
            $('#filtroCurso, #filtroUnidade, #filtroStatus').on('change', function() {
                renderizarTurmas();
            });
        });

        function carregarDados() {
            // Carregar turmas
            $.get('turmas_api.php', function(response) {
                if (response.success) {
                    turmas = response.turmas || [];
                    renderizarTurmas();
                } else {
                    toastr.error('Erro ao carregar turmas: ' + (response.message || 'Erro desconhecido'));
                }
            }).fail(function(xhr) {
                console.error('Erro na requisição:', xhr);
                toastr.error('Erro ao carregar turmas. Verifique o console para mais detalhes.');
            });

            // Carregar cursos para filtros e formulário
            $.get('cursos_api.php?action=list', function(response) {
                if (response.success) {
                    cursos = response.data;
                    let options = '<option value="">Selecione um curso</option>';
                    let filterOptions = '<option value="">Todos os cursos</option>';
                    cursos.forEach(curso => {
                        if (curso.ativo == 1) {
                            options += `<option value="${curso.id}">${curso.nome}</option>`;
                            filterOptions += `<option value="${curso.id}">${curso.nome}</option>`;
                        }
                    });
                    $('#cursoId').html(options);
                    $('#filtroCurso').html(filterOptions);
                }
            });

            // Carregar unidades
            $.get('unidades_api.php?action=list', function(response) {
                if (response.success) {
                    unidades = response.data;
                    let options = '<option value="">Selecione uma unidade</option>';
                    let filterOptions = '<option value="">Todas as unidades</option>';
                    unidades.forEach(unidade => {
                        if (unidade.ativo == 1) {
                            options += `<option value="${unidade.id}">${unidade.nome} - ${unidade.cidade}</option>`;
                            filterOptions += `<option value="${unidade.id}">${unidade.nome} - ${unidade.cidade}</option>`;
                        }
                    });
                    $('#unidadeId').html(options);
                    $('#filtroUnidade').html(filterOptions);
                }
            });
        }

        function renderizarTurmas() {
            const filtroCurso = $('#filtroCurso').val();
            const filtroUnidade = $('#filtroUnidade').val();
            const filtroStatus = $('#filtroStatus').val();

            let turmasFiltradas = turmas.filter(turma => {
                return (!filtroCurso || turma.curso_id == filtroCurso) &&
                       (!filtroUnidade || turma.unidade_id == filtroUnidade) &&
                       (!filtroStatus || turma.status == filtroStatus);
            });

            if (turmasFiltradas.length === 0) {
                $('#turmasList').html(`
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Nenhuma turma encontrada</p>
                    </div>
                `);
                return;
            }

            let html = '';
            turmasFiltradas.forEach(turma => {
                const statusClass = {
                    'planejada': 'info',
                    'em_andamento': 'success',
                    'concluida': 'secondary',
                    'cancelada': 'danger'
                }[turma.status] || 'secondary';

                const statusText = {
                    'planejada': 'Planejada',
                    'em_andamento': 'Em Andamento',
                    'concluida': 'Concluída',
                    'cancelada': 'Cancelada'
                }[turma.status] || turma.status;

                const diasSemana = turma.dias_semana ? formatarDiasSemana(turma.dias_semana) : 'Não definido';
                const horario = turma.hora_inicio && turma.hora_fim ? 
                    `${turma.hora_inicio.substring(0,5)} - ${turma.hora_fim.substring(0,5)}` : 
                    'Não definido';

                html += `
                    <div class="col-md-6 col-lg-4">
                        <div class="card turma-card">
                            <div class="card-header">
                                <h5 class="mb-0">${turma.nome_turma}</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-2"><strong><i class="fas fa-graduation-cap"></i> Curso:</strong> ${turma.curso_nome}</p>
                                <p class="mb-2"><strong><i class="fas fa-building"></i> Unidade:</strong> ${turma.unidade_nome}</p>
                                <p class="mb-2"><strong><i class="fas fa-user"></i> Instrutor:</strong> ${turma.instrutor || 'Não definido'}</p>
                                <p class="mb-2"><strong><i class="fas fa-calendar-week"></i> Dias:</strong> ${diasSemana}</p>
                                <p class="mb-2"><strong><i class="fas fa-clock"></i> Horário:</strong> ${horario}</p>
                                <p class="mb-2"><strong><i class="fas fa-door-open"></i> Sala:</strong> ${turma.sala_padrao || 'Não definida'}</p>
                                <p class="mb-2"><strong><i class="fas fa-users"></i> Vagas:</strong> ${turma.vagas_disponiveis}/${turma.vagas_totais}</p>
                                <p class="mb-2"><strong><i class="fas fa-calendar"></i> Período:</strong> ${formatarData(turma.data_inicio)} a ${formatarData(turma.data_fim)}</p>
                                <p class="mb-2"><strong><i class="fas fa-chalkboard-teacher"></i> Aulas:</strong> ${turma.total_aulas || 0} agendadas</p>
                                <p class="mb-2"><span class="badge badge-${statusClass} badge-status">${statusText}</span></p>
                            </div>
                            <div class="card-footer">
                                <button class="btn btn-sm btn-info" onclick="editarTurma(${turma.id})">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
	                                <a href="agendamento_aulas.php?turma_id=${turma.id}" class="btn btn-sm btn-success">
                                    <i class="fas fa-calendar-plus"></i> Agendar Aulas
                                </a>
                                <button class="btn btn-sm btn-danger" onclick="excluirTurma(${turma.id})">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            $('#turmasList').html(html);
        }

        function formatarDiasSemana(diasStr) {
            if (!diasStr) return 'Não definido';
            const dias = diasStr.split(',');
            const nomes = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
            return dias.map(d => nomes[parseInt(d)]).join(', ');
        }

        function formatarData(data) {
            if (!data) return '';
            const d = new Date(data + 'T00:00:00');
            return d.toLocaleDateString('pt-BR');
        }

        function abrirModalNovaTurma() {
            $('#modalTurmaTitle').text('Nova Turma');
            $('#formTurma')[0].reset();
            $('#turmaId').val('');
            $('.form-check-input').prop('checked', false);
            $('#modalTurma').modal('show');
        }

        function editarTurma(id) {
            const turma = turmas.find(t => t.id == id);
            if (!turma) return;

            $('#modalTurmaTitle').text('Editar Turma');
            $('#turmaId').val(turma.id);
            $('#cursoId').val(turma.curso_id);
            $('#unidadeId').val(turma.unidade_id);
            $('#nomeTurma').val(turma.nome_turma);
            $('#instrutor').val(turma.instrutor);
            $('#vagasTotais').val(turma.vagas_totais);
            $('#dataInicio').val(turma.data_inicio);
            $('#dataFim').val(turma.data_fim);
            $('#horaInicio').val(turma.hora_inicio);
            $('#horaFim').val(turma.hora_fim);
            $('#salaPadrao').val(turma.sala_padrao);
            $('#status').val(turma.status);

            // Marcar dias da semana
            $('.form-check-input').prop('checked', false);
            if (turma.dias_semana) {
                const dias = turma.dias_semana.split(',');
                dias.forEach(dia => {
                    $(`#dia${dia}`).prop('checked', true);
                });
            }

            $('#modalTurma').modal('show');
        }

        function salvarTurma() {
            // Validar formulário
            if (!$('#formTurma')[0].checkValidity()) {
                $('#formTurma')[0].reportValidity();
                return;
            }

            // Obter dias da semana selecionados
            const diasSelecionados = [];
            $('.form-check-input:checked').each(function() {
                diasSelecionados.push($(this).val());
            });

            // Validar dias da semana - necessário para gerar aulas automaticamente
            if (diasSelecionados.length === 0) {
                toastr.warning('Selecione os dias da semana para gerar as aulas automaticamente. Se não selecionar, você precisará agendar as aulas manualmente depois.');
                // Não bloquear, mas avisar
            }

            const turmaId = $('#turmaId').val();
            
            // Validar campos obrigatórios antes de enviar
            const cursoId = $('#cursoId').val();
            const unidadeId = $('#unidadeId').val();
            const nomeTurma = $('#nomeTurma').val().trim();
            const dataInicio = $('#dataInicio').val();
            const dataFim = $('#dataFim').val();
            const horaInicio = $('#horaInicio').val();
            const horaFim = $('#horaFim').val();
            
            if (!cursoId) {
                toastr.error('Selecione um curso');
                $('#cursoId').focus();
                return;
            }
            
            if (!unidadeId) {
                toastr.error('Selecione uma unidade');
                $('#unidadeId').focus();
                return;
            }
            
            if (!nomeTurma) {
                toastr.error('Digite o nome da turma');
                $('#nomeTurma').focus();
                return;
            }
            
            if (!dataInicio) {
                toastr.error('Selecione a data de início');
                $('#dataInicio').focus();
                return;
            }
            
            if (!dataFim) {
                toastr.error('Selecione a data de fim');
                $('#dataFim').focus();
                return;
            }
            
            if (dataFim <= dataInicio) {
                toastr.error('A data de fim deve ser posterior à data de início');
                $('#dataFim').focus();
                return;
            }
            
            // Validar horários - necessário para gerar aulas automaticamente
            if (!horaInicio || !horaFim) {
                toastr.warning('Preencha os horários de início e fim para gerar as aulas automaticamente. Se não preencher, você precisará agendar as aulas manualmente depois.');
                // Não bloquear, mas avisar
            } else if (horaFim <= horaInicio) {
                toastr.error('O horário de fim deve ser posterior ao horário de início');
                $('#horaFim').focus();
                return;
            }
            
            // Definir método HTTP
            const method = turmaId ? 'PUT' : 'POST';
            
            const data = {
                curso_id: parseInt(cursoId),
                unidade_id: parseInt(unidadeId),
                nome_turma: nomeTurma,
                instrutor: $('#instrutor').val().trim() || null,
                vagas_totais: parseInt($('#vagasTotais').val()) || 20,
                data_inicio: dataInicio,
                data_fim: dataFim,
                dias_semana: diasSelecionados.length > 0 ? diasSelecionados.join(',') : null,
                hora_inicio: horaInicio,
                hora_fim: horaFim,
                sala_padrao: $('#salaPadrao').val().trim() || null,
                status: $('#status').val() || 'planejada'
            };
            
            // Adicionar ID se for edição
            if (turmaId) {
                data.id = parseInt(turmaId);
            }
            
            // Log para debug - VISÍVEL NO CONSOLE (F12)
            console.log('========================================');
            console.log('📤 ENVIANDO DADOS PARA O SERVIDOR');
            console.log('========================================');
            console.log('Dados a serem enviados:', data);
            console.log('Método HTTP:', method);
            console.log('URL:', 'turmas_api.php');
            console.log('========================================');

            // Mostrar loading
            const submitBtn = $('button[onclick="salvarTurma()"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');
            
            // Adicionar timestamp para rastreamento
            const timestamp = new Date().toISOString();
            console.log('🕐 Timestamp da requisição:', timestamp);
            
            $.ajax({
                url: 'turmas_api.php',
                method: method,
                contentType: 'application/json',
                data: JSON.stringify(data),
                timeout: 30000, // 30 segundos de timeout
                beforeSend: function() {
                    console.log('⏳ Enviando requisição...');
                },
                success: function(response) {
                    console.log('🕐 Resposta recebida em:', new Date().toISOString());
                    submitBtn.prop('disabled', false).html(originalText);
                    
                    console.log('========================================');
                    console.log('✅ RESPOSTA DO SERVIDOR (SUCESSO)');
                    console.log('========================================');
                    console.log('Resposta completa:', response);
                    console.log('========================================');
                    
                    if (response.success) {
                        // Mostrar mensagem de sucesso com informações sobre aulas geradas
                        let mensagem = response.message;
                        if (response.aulas_geradas && response.aulas_geradas > 0) {
                            mensagem += ` (${response.aulas_geradas} aulas geradas automaticamente)`;
                            toastr.success(mensagem, 'Turma Criada!', {timeOut: 5000});
                        } else {
                            toastr.success(mensagem);
                        }
                        
                        $('#modalTurma').modal('hide');
                        // Recarregar dados após salvar
                        carregarDados();
                        
                        // Se for uma nova turma e não gerou aulas automaticamente, oferecer opção de agendar
                        if (!turmaId && response.turma_id && (!response.aulas_geradas || response.aulas_geradas === 0)) {
                            setTimeout(async function() {
                                const confirmed = await showConfirm('Deseja ir para a página de agendamento para configurar as aulas desta turma?', 'Configurar Aulas', 'info');
                                if (confirmed) {
                                    window.location.href = 'agendamento_aulas.php?turma_id=' + response.turma_id;
                                }
                            }, 500);
                        } else if (!turmaId && response.turma_id && response.aulas_geradas > 0) {
                            // Se gerou aulas automaticamente, oferecer opção de ver o calendário
                            setTimeout(async function() {
                                const confirmed = await showConfirm('Aulas geradas com sucesso! Deseja visualizar o calendário de aulas?', 'Visualizar Calendário', 'success');
                                if (confirmed) {
                                    window.location.href = 'agendamento_aulas.php?turma_id=' + response.turma_id;
                                }
                            }, 500);
                        }
                    } else {
                        console.warn('⚠️ Servidor retornou success=false');
                        console.warn('Mensagem:', response.message);
                        toastr.error(response.message || 'Erro ao salvar turma');
                    }
                },
                error: function(xhr, status, error) {
                    submitBtn.prop('disabled', false).html(originalText);
                    
                    console.log('🕐 Erro recebido em:', new Date().toISOString());
                    
                    // ERROS VISÍVEIS NO CONSOLE (F12 → Console)
                    console.error('========================================');
                    console.error('❌ ERRO NA REQUISIÇÃO');
                    console.error('========================================');
                    console.error('Status HTTP:', xhr.status || 'N/A');
                    console.error('Status Text:', status || 'N/A');
                    console.error('Erro:', error || 'N/A');
                    console.error('URL:', xhr.responseURL || 'turmas_api.php');
                    console.error('Ready State:', xhr.readyState);
                    console.error('----------------------------------------');
                    
                    // Verificar se há resposta
                    if (xhr.responseText) {
                        console.error('RESPOSTA DO SERVIDOR (TEXTO):');
                        console.error(xhr.responseText);
                        console.error('----------------------------------------');
                        
                        // Tentar parsear JSON da resposta
                        let responseJSON = null;
                        try {
                            responseJSON = JSON.parse(xhr.responseText);
                            console.error('RESPOSTA DO SERVIDOR (JSON):');
                            console.error(responseJSON);
                            console.error('----------------------------------------');
                        } catch (e) {
                            console.error('⚠️ Não foi possível parsear a resposta como JSON');
                            console.error('Erro de parse:', e.message);
                        }
                    } else {
                        console.error('⚠️ NENHUMA RESPOSTA DO SERVIDOR');
                        console.error('Isso pode indicar:');
                        console.error('- Servidor não respondeu');
                        console.error('- Problema de rede');
                        console.error('- Timeout');
                        console.error('- Erro de CORS');
                    }
                    
                    console.error('----------------------------------------');
                    if (xhr.getAllResponseHeaders) {
                        console.error('HEADERS DA RESPOSTA:');
                        console.error(xhr.getAllResponseHeaders());
                    }
                    console.error('========================================');
                    
                    // Mensagem de erro para o usuário
                    let errorMessage = 'Erro ao salvar turma.';
                    
                    if (xhr.responseText) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.message) {
                                errorMessage = response.message;
                            }
                        } catch (e) {
                            if (xhr.status === 0) {
                                errorMessage = 'Erro de conexão. Verifique se o servidor está rodando.';
                            } else if (xhr.status === 404) {
                                errorMessage = 'Arquivo não encontrado. Verifique se turmas_api.php existe.';
                            } else if (xhr.status === 500) {
                                errorMessage = 'Erro interno do servidor. Verifique os logs do PHP.';
                            } else {
                                errorMessage = 'Erro ao processar requisição. Status: ' + xhr.status;
                            }
                        }
                    } else {
                        if (xhr.status === 0) {
                            errorMessage = 'Erro de conexão. Verifique se o servidor está rodando.';
                        } else {
                            errorMessage = 'Servidor não respondeu. Verifique o console para mais detalhes.';
                        }
                    }
                    
                    // Mostrar erro em vermelho no console também
                    console.error('💬 MENSAGEM DE ERRO PARA O USUÁRIO:', errorMessage);
                    
                    toastr.error(errorMessage);
                },
                complete: function(xhr, status) {
                    console.log('🕐 Requisição finalizada em:', new Date().toISOString());
                    console.log('Status final:', status);
                    console.log('Status HTTP final:', xhr.status);
                }
            });
        }

        async function excluirTurma(id) {
            const confirmed = await showConfirm('Tem certeza que deseja excluir esta turma?', 'Confirmar Exclusão', 'danger');
            if (!confirmed) {
                return;
            }

            $.ajax({
                url: 'turmas_api.php',
                method: 'DELETE',
                contentType: 'application/json',
                data: JSON.stringify({ id: id }),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        carregarDados();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Erro ao excluir turma');
                }
            });
        }

        // Configurar toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };
    </script>
</body>
</html>
