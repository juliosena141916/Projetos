<?php

require_once 'check_admin.php';

// Incluir arquivo de conexão de forma segura
$conexao_path = dirname(dirname(__FILE__)) . '/includes/conexao.php';
if (!file_exists($conexao_path)) {
    die('Erro: Arquivo de conexão não encontrado em ' . $conexao_path);
}
require_once $conexao_path;

try {
    $pdo = getConexao();

    // Buscar cursos ativos
    $stmt = $pdo->query("SELECT id, nome, categoria FROM cursos WHERE ativo = 1 ORDER BY categoria, nome");
    $cursos = $stmt->fetchAll();

    // Buscar unidades para filtro
    $stmt = $pdo->query("SELECT id, nome, cidade FROM unidades WHERE ativo = 1 ORDER BY cidade, nome");
    $unidades = $stmt->fetchAll();

    // Buscar turmas ativas (incluindo todas as turmas ativas, não apenas planejadas e em andamento)
    $stmt = $pdo->query("SELECT tc.*, c.nome as curso_nome, u.nome as unidade_nome, u.cidade
                        FROM turmas_cursos tc
                        JOIN cursos c ON tc.curso_id = c.id
                        JOIN unidades u ON tc.unidade_id = u.id
                        WHERE tc.ativo = 1
                        ORDER BY tc.data_inicio DESC, tc.nome_turma ASC");
    $turmas = $stmt->fetchAll();

} catch (Exception $e) {
    error_log("Erro ao buscar dados: " . $e->getMessage());
    $cursos = [];
    $unidades = [];
    $turmas = [];
}

// Verificar se há turma selecionada via GET
$turma_selecionada_id = isset($_GET['turma_id']) ? intval($_GET['turma_id']) : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento de Aulas - Admin TechFit</title>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
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
        .turma-info-card {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .calendar-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .info-badge {
            display: inline-block;
            padding: 5px 10px;
            background: #007bff;
            color: white;
            border-radius: 4px;
            margin-right: 10px;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        .modal-header {
            background-color: #007bff;
            color: white;
        }
        /* Customização mínima do FullCalendar para manter consistência */
        .fc {
            font-family: inherit;
        }
        .fc-button {
            background-color: #007bff !important;
            border-color: #007bff !important;
        }
        .fc-button:hover {
            background-color: #0056b3 !important;
            border-color: #0056b3 !important;
        }
        .fc-button-active {
            background-color: #0056b3 !important;
        }
        .fc-event {
            cursor: pointer;
            font-weight: 500;
            padding: 2px 4px;
            border-radius: 3px;
        }
        .fc-event-title {
            font-weight: 600;
        }
        .fc-daygrid-event {
            white-space: normal;
            line-height: 1.3;
        }
        .fc-timegrid-event {
            font-size: 0.85rem;
        }
        .fc-event-time {
            font-weight: 600;
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
                <a class="nav-link" href="turmas.php">
                    <i class="fas fa-calendar-alt"></i> Gerenciar Turmas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="agendamento_aulas.php">
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
            <h1 class="h2 mb-0">Agendamento de Aulas</h1>
            <p class="text-muted mb-0">Gerencie o calendário de aulas das turmas</p>
        </div>

        <!-- Seleção de Turma -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="fas fa-filter"></i> Selecionar Turma
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label>Curso</label>
                        <select class="form-control" id="filtroCurso">
                            <option value="">Todos os cursos</option>
                            <?php foreach ($cursos as $curso): ?>
                                <option value="<?= $curso['id'] ?>"><?= htmlspecialchars($curso['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Unidade</label>
                        <select class="form-control" id="filtroUnidade">
                            <option value="">Todas as unidades</option>
                            <?php foreach ($unidades as $unidade): ?>
                                <option value="<?= $unidade['id'] ?>"><?= htmlspecialchars($unidade['nome']) ?> - <?= htmlspecialchars($unidade['cidade']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Turma</label>
                        <div class="input-group">
                            <select class="form-control" id="turmaSelect">
                                <option value="">Selecione uma turma</option>
                                <?php foreach ($turmas as $turma): ?>
                                    <option value="<?= $turma['id'] ?>" 
                                            data-curso="<?= $turma['curso_id'] ?>"
                                            data-unidade="<?= $turma['unidade_id'] ?>"
                                            <?= ($turma_selecionada_id == $turma['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($turma['nome_turma']) ?> - <?= htmlspecialchars($turma['curso_nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="recarregarListaTurmas()" title="Atualizar lista de turmas">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações da Turma Selecionada -->
        <div id="turmaInfo" style="display: none;">
            <div class="turma-info-card">
                <h5><i class="fas fa-info-circle"></i> Informações da Turma</h5>
                <div id="turmaInfoContent"></div>
            </div>
        </div>

        <!-- Calendário -->
        <div class="calendar-container">
            <div id="calendar"></div>
        </div>
    </main>

    <!-- Modal Nova/Editar Aula -->
    <div class="modal fade" id="modalAula" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAulaTitle">Nova Aula</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formAula">
                        <input type="hidden" id="aulaId">
                        <input type="hidden" id="aulaTurmaId">
                        
                        <div class="form-group">
                            <label>Data da Aula *</label>
                            <input type="date" class="form-control" id="dataAula" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Horário Início *</label>
                                    <input type="time" class="form-control" id="horaInicioAula" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Horário Fim *</label>
                                    <input type="time" class="form-control" id="horaFimAula" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Sala</label>
                            <input type="text" class="form-control" id="salaAula" placeholder="Ex: Sala 1">
                        </div>

                        <div class="form-group">
                            <label>Observações</label>
                            <textarea class="form-control" id="observacoesAula" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" id="statusAula">
                                <option value="agendada">Agendada</option>
                                <option value="realizada">Realizada</option>
                                <option value="cancelada">Cancelada</option>
                                <option value="remarcada">Remarcada</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="btnExcluirAula" onclick="excluirAula()" style="display: none;">
                        <i class="fas fa-trash"></i> Excluir
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarAula()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales/pt-br.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        let calendar;
        let turmaSelecionada = null;
        let todasTurmas = <?= json_encode($turmas) ?>;

        $(document).ready(function() {
            // Inicializar calendário
            const calendarEl = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'pt-br',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                buttonText: {
                    today: 'Hoje',
                    month: 'Mês',
                    week: 'Semana',
                    day: 'Dia'
                },
                events: [],
                eventClick: function(info) {
                    editarAula(info.event.id);
                },
                eventDidMount: function(info) {
                    // Adicionar tooltip com informações completas
                    if (info.event.extendedProps && info.event.extendedProps.tooltip) {
                        $(info.el).attr('title', info.event.extendedProps.tooltip);
                    }
                },
                dateClick: function(info) {
                    if (turmaSelecionada) {
                        abrirModalNovaAula(info.dateStr);
                    } else {
                        toastr.warning('Selecione uma turma primeiro');
                    }
                },
                eventDisplay: 'block',
                displayEventTime: true,
                displayEventEnd: true
            });
            calendar.render();

            // Filtros
            $('#filtroCurso, #filtroUnidade').on('change', function() {
                filtrarTurmas();
            });

            $('#turmaSelect').on('change', function() {
                const turmaId = $(this).val();
                if (turmaId) {
                    carregarTurma(turmaId);
                } else {
                    $('#turmaInfo').hide();
                    turmaSelecionada = null;
                    calendar.removeAllEvents();
                }
            });

            // Se há turma pré-selecionada, carregar
            <?php if ($turma_selecionada_id): ?>
                carregarTurma(<?= $turma_selecionada_id ?>);
            <?php endif; ?>
        });

        function filtrarTurmas() {
            const cursoId = $('#filtroCurso').val();
            const unidadeId = $('#filtroUnidade').val();

            $('#turmaSelect option').each(function() {
                if ($(this).val() === '') return;
                
                const turmaCurso = $(this).data('curso');
                const turmaUnidade = $(this).data('unidade');
                
                const mostrar = (!cursoId || turmaCurso == cursoId) && 
                               (!unidadeId || turmaUnidade == unidadeId);
                
                $(this).toggle(mostrar);
            });

            // Resetar seleção se a turma atual foi filtrada
            if ($('#turmaSelect option:selected:visible').length === 0) {
                $('#turmaSelect').val('');
                $('#turmaInfo').hide();
                turmaSelecionada = null;
                calendar.removeAllEvents();
            }
        }

        // Função para recarregar lista de turmas (útil após criar nova turma)
        function recarregarListaTurmas() {
            const turmaSelecionadaId = $('#turmaSelect').val();
            
            $.get('turmas_api.php', function(response) {
                if (response.success) {
                    todasTurmas = response.turmas || [];
                    
                    // Atualizar select de turmas
                    let options = '<option value="">Selecione uma turma</option>';
                    todasTurmas.forEach(turma => {
                        const selected = turma.id == turmaSelecionadaId ? 'selected' : '';
                        options += `<option value="${turma.id}" 
                                        data-curso="${turma.curso_id}"
                                        data-unidade="${turma.unidade_id}"
                                        ${selected}>
                                    ${turma.nome_turma} - ${turma.curso_nome}
                                </option>`;
                    });
                    $('#turmaSelect').html(options);
                    
                    // Aplicar filtros atuais
                    filtrarTurmas();
                    
                    // Se havia uma turma selecionada, manter selecionada
                    if (turmaSelecionadaId) {
                        $('#turmaSelect').val(turmaSelecionadaId);
                        if (!$('#turmaSelect option:selected').length) {
                            // Se a turma não existe mais, limpar seleção
                            $('#turmaSelect').val('');
                            $('#turmaInfo').hide();
                            turmaSelecionada = null;
                            calendar.removeAllEvents();
                        }
                    }
                    
                    toastr.success('Lista de turmas atualizada');
                } else {
                    toastr.error('Erro ao atualizar lista de turmas');
                }
            }).fail(function() {
                toastr.error('Erro ao atualizar lista de turmas');
            });
        }

        function carregarTurma(turmaId) {
            $.get('turmas_api.php?id=' + turmaId, function(response) {
                if (response.success) {
                    turmaSelecionada = response.turma;
                    exibirInfoTurma();
                    // Carregar aulas automaticamente após carregar a turma
                    carregarAulas();
                } else {
                    toastr.error('Erro ao carregar turma: ' + (response.message || 'Erro desconhecido'));
                }
            }).fail(function(xhr) {
                console.error('Erro ao carregar turma:', xhr);
                toastr.error('Erro ao carregar informações da turma. Verifique a conexão.');
            });
        }

        function exibirInfoTurma() {
            if (!turmaSelecionada) return;

            const diasSemana = turmaSelecionada.dias_semana ? formatarDiasSemana(turmaSelecionada.dias_semana) : 'Não definido';
            const horario = turmaSelecionada.hora_inicio && turmaSelecionada.hora_fim ? 
                `${turmaSelecionada.hora_inicio.substring(0,5)} - ${turmaSelecionada.hora_fim.substring(0,5)}` : 
                'Não definido';

            const html = `
                <div class="row">
                    <div class="col-md-6">
                        <span class="info-badge"><i class="fas fa-graduation-cap"></i> ${turmaSelecionada.curso_nome}</span>
                        <span class="info-badge"><i class="fas fa-building"></i> ${turmaSelecionada.unidade_nome}</span>
                        <span class="info-badge"><i class="fas fa-user"></i> ${turmaSelecionada.instrutor || 'Sem instrutor'}</span>
                    </div>
                    <div class="col-md-6">
                        <span class="info-badge"><i class="fas fa-calendar-week"></i> ${diasSemana}</span>
                        <span class="info-badge"><i class="fas fa-clock"></i> ${horario}</span>
                        <span class="info-badge"><i class="fas fa-door-open"></i> ${turmaSelecionada.sala_padrao || 'Sem sala'}</span>
                    </div>
                </div>
            `;

            $('#turmaInfoContent').html(html);
            $('#turmaInfo').show();
        }

        function formatarDiasSemana(diasStr) {
            if (!diasStr) return 'Não definido';
            const dias = diasStr.split(',');
            const nomes = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
            return dias.map(d => nomes[parseInt(d)]).join(', ');
        }

        function carregarAulas() {
            if (!turmaSelecionada) {
                calendar.removeAllEvents();
                return;
            }

            console.log('Carregando aulas para turma ID:', turmaSelecionada.id);
            
            $.get('aulas_api.php?turma_id=' + turmaSelecionada.id, function(response) {
                console.log('Resposta da API:', response);
                
                if (!response) {
                    console.error('Resposta vazia da API');
                    toastr.error('Resposta inválida do servidor');
                    return;
                }
                
                if (response.success && response.aulas) {
                    console.log('Aulas recebidas:', response.aulas.length);
                    calendar.removeAllEvents();
                    
                    if (response.aulas.length === 0) {
                        toastr.info('Nenhuma aula agendada para esta turma ainda.');
                        return;
                    }
                    
                    const events = response.aulas
                        .filter(aula => {
                            // Filtrar apenas aulas com dados válidos
                            return aula && aula.id && aula.data_aula;
                        })
                        .map(aula => {
                            try {
                                const color = {
                                    'agendada': '#28a745',
                                    'realizada': '#6c757d',
                                    'cancelada': '#dc3545',
                                    'remarcada': '#ffc107'
                                }[aula.status] || '#007bff';

                                // Formatar horários com validação
                                let horaInicio = '';
                                let horaFim = '';
                                
                                if (aula.hora_inicio) {
                                    const horaInicioStr = aula.hora_inicio.toString();
                                    if (horaInicioStr.length >= 5) {
                                        horaInicio = horaInicioStr.substring(0, 5);
                                    } else {
                                        horaInicio = horaInicioStr;
                                    }
                                }
                                
                                if (aula.hora_fim) {
                                    const horaFimStr = aula.hora_fim.toString();
                                    if (horaFimStr.length >= 5) {
                                        horaFim = horaFimStr.substring(0, 5);
                                    } else {
                                        horaFim = horaFimStr;
                                    }
                                }
                                
                                const horario = horaInicio && horaFim ? `${horaInicio} - ${horaFim}` : horaInicio || 'Sem horário';
                                
                                // Criar título mais informativo
                                let title = `${horario}`;
                                if (aula.sala) {
                                    title += ` | Sala: ${aula.sala}`;
                                }
                                
                                // Tooltip com informações completas
                                const tooltip = [
                                    turmaSelecionada.nome_turma || 'Turma',
                                    horario ? `Horário: ${horario}` : '',
                                    aula.sala ? `Sala: ${aula.sala}` : '',
                                    `Status: ${aula.status || 'agendada'}`,
                                    aula.observacoes ? `Obs: ${aula.observacoes}` : ''
                                ].filter(Boolean).join('\n');

                                // Validar e formatar data
                                const dataAula = aula.data_aula ? aula.data_aula.toString() : '';
                                if (!dataAula) {
                                    console.warn('Aula sem data:', aula);
                                    return null;
                                }

                                // Formatar hora para o FullCalendar (HH:MM:SS)
                                const horaInicioFC = horaInicio ? (horaInicio.length === 5 ? horaInicio + ':00' : horaInicio) : '00:00:00';
                                const horaFimFC = horaFim ? (horaFim.length === 5 ? horaFim + ':00' : horaFim) : '23:59:59';

                                return {
                                    id: aula.id.toString(),
                                    title: title,
                                    start: dataAula + 'T' + horaInicioFC,
                                    end: dataAula + 'T' + horaFimFC,
                                    backgroundColor: color,
                                    borderColor: color,
                                    textColor: '#ffffff',
                                    extendedProps: {
                                        ...aula,
                                        turma_nome: turmaSelecionada.nome_turma || 'Turma',
                                        tooltip: tooltip
                                    }
                                };
                            } catch (error) {
                                console.error('Erro ao processar aula:', aula, error);
                                return null;
                            }
                        })
                        .filter(event => event !== null); // Remover eventos nulos

                    calendar.addEventSource(events);
                    
                    // Mostrar mensagem de sucesso
                    toastr.success(`${response.aulas.length} aula(s) carregada(s) no calendário`, '', {timeOut: 2000});
                } else {
                    toastr.error('Erro ao carregar aulas: ' + (response.message || 'Erro desconhecido'));
                    calendar.removeAllEvents();
                }
            }).fail(function(xhr) {
                console.error('Erro na requisição:', xhr);
                console.error('Status:', xhr.status);
                console.error('Response:', xhr.responseText);
                
                let errorMessage = 'Erro ao carregar aulas.';
                if (xhr.status === 404) {
                    errorMessage = 'Arquivo de API não encontrado. Verifique o caminho.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Erro no servidor. Verifique os logs.';
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch (e) {
                        errorMessage = 'Erro ao processar resposta do servidor.';
                    }
                }
                
                toastr.error(errorMessage);
                calendar.removeAllEvents();
            });
        }

        function gerarAulasAutomaticamente() {
            if (!turmaSelecionada) {
                toastr.error('Selecione uma turma primeiro');
                return;
            }

            showConfirm('Deseja gerar automaticamente todas as aulas desta turma baseado nos dias da semana configurados?', 'Gerar Aulas Automaticamente', 'info')
                .then(confirmed => {
                    if (!confirmed) return;
                    
                    // Continuar com a lógica de geração
                    $.ajax({
                        url: 'aulas_api.php',
                        method: 'POST',
                        data: {
                            turma_id: turmaSelecionada.id,
                            gerar_automatico: true
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                loadAulas();
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function() {
                            toastr.error('Erro ao gerar aulas automaticamente');
                        }
                    });
                });
            return;

            $.ajax({
                url: 'aulas_api.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    action: 'gerar_aulas',
                    turma_id: turmaSelecionada.id
                }),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        carregarAulas();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    toastr.error(response ? response.message : 'Erro ao gerar aulas');
                }
            });
        }

        function abrirModalNovaAula(data = null) {
            if (!turmaSelecionada) {
                toastr.error('Selecione uma turma primeiro');
                return;
            }

            $('#modalAulaTitle').text('Nova Aula');
            $('#formAula')[0].reset();
            $('#aulaId').val('');
            $('#aulaTurmaId').val(turmaSelecionada.id);
            $('#btnExcluirAula').hide();

            if (data) {
                $('#dataAula').val(data);
            }

            // Preencher com horários padrão da turma
            if (turmaSelecionada.hora_inicio) {
                $('#horaInicioAula').val(turmaSelecionada.hora_inicio);
            }
            if (turmaSelecionada.hora_fim) {
                $('#horaFimAula').val(turmaSelecionada.hora_fim);
            }
            if (turmaSelecionada.sala_padrao) {
                $('#salaAula').val(turmaSelecionada.sala_padrao);
            }

            $('#modalAula').modal('show');
        }

        function editarAula(aulaId) {
            $.get('aulas_api.php?id=' + aulaId, function(response) {
                if (response.success) {
                    const aula = response.aula;
                    
                    $('#modalAulaTitle').text('Editar Aula');
                    $('#aulaId').val(aula.id);
                    $('#aulaTurmaId').val(aula.turma_id);
                    $('#dataAula').val(aula.data_aula);
                    $('#horaInicioAula').val(aula.hora_inicio);
                    $('#horaFimAula').val(aula.hora_fim);
                    $('#salaAula').val(aula.sala);
                    $('#observacoesAula').val(aula.observacoes);
                    $('#statusAula').val(aula.status);
                    $('#btnExcluirAula').show();

                    $('#modalAula').modal('show');
                }
            });
        }

        function salvarAula() {
            if (!$('#formAula')[0].checkValidity()) {
                $('#formAula')[0].reportValidity();
                return;
            }

            const aulaId = $('#aulaId').val();
            const data = {
                turma_id: $('#aulaTurmaId').val(),
                data_aula: $('#dataAula').val(),
                hora_inicio: $('#horaInicioAula').val(),
                hora_fim: $('#horaFimAula').val(),
                sala: $('#salaAula').val(),
                observacoes: $('#observacoesAula').val(),
                status: $('#statusAula').val()
            };

            const method = aulaId ? 'PUT' : 'POST';
            if (aulaId) {
                data.id = aulaId;
            }

            $.ajax({
                url: 'aulas_api.php',
                method: method,
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#modalAula').modal('hide');
                        carregarAulas();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Erro ao salvar aula');
                }
            });
        }

        function excluirAula() {
            const aulaId = $('#aulaId').val();
            if (!aulaId) return;

            showConfirm('Tem certeza que deseja excluir esta aula?', 'Confirmar Exclusão', 'danger')
                .then(confirmed => {
                    if (!confirmed) return;
                    
                    // Continuar com a lógica de exclusão
                    $.ajax({
                        url: 'aulas_api.php?id=' + aulaId,
                        method: 'DELETE',
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                loadAulas();
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function() {
                            toastr.error('Erro ao excluir aula');
                        }
                    });
                });
            return;

            $.ajax({
                url: 'aulas_api.php?id=' + aulaId,
                method: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#modalAula').modal('hide');
                        carregarAulas();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Erro ao excluir aula');
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
