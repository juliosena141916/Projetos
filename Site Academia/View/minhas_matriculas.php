<?php
session_start();
require_once 'includes/conexao.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit;
}

// Verificar se o usuário tem matrículas ativas
$tem_matriculas = false;
try {
    $pdo = getConexao();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM matriculas 
        WHERE usuario_id = ? AND status != 'cancelada'
    ");
    $stmt->execute([$_SESSION['usuario_id']]);
    $result = $stmt->fetch();
    $tem_matriculas = ($result && $result['total'] > 0);
} catch (Exception $e) {
    $tem_matriculas = false;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Turmas - TechFit</title>
    <link rel="icon" href="data:,">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/pages/minhas_matriculas.css">
    <link rel="stylesheet" href="assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <style>
        /* Override FullCalendar para tema escuro */
        .fc {
            background: transparent;
            color: var(--color-text-white);
            font-family: 'Poppins', sans-serif;
        }
        
        .fc-theme-standard td, 
        .fc-theme-standard th {
            border-color: rgba(220, 20, 60, 0.2);
        }
        
        .fc-theme-standard .fc-scrollgrid {
            border-color: rgba(220, 20, 60, 0.3);
            border-radius: 12px;
            overflow: hidden;
        }
        
        /* Botões do calendário */
        .fc-button {
            background: linear-gradient(135deg, #8B0000, #DC143C) !important;
            border: none !important;
            color: #fff !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            padding: 10px 18px !important;
            border-radius: 8px !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 4px 12px rgba(220, 20, 60, 0.3) !important;
            font-size: 0.875rem !important;
        }
        
        .fc-button:hover {
            background: linear-gradient(135deg, #DC143C, #FF4444) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 16px rgba(220, 20, 60, 0.4) !important;
        }
        
        .fc-button:active {
            transform: translateY(0) !important;
        }
        
        .fc-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }
        
        .fc-button-active {
            background: linear-gradient(135deg, #DC143C, #FF4444) !important;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.3) !important;
        }
        
        /* Título do calendário */
        .fc-toolbar-title {
            color: #fff !important;
            font-weight: 700 !important;
            font-size: 1.75rem !important;
            text-transform: uppercase !important;
            letter-spacing: 1px !important;
        }
        
        /* Números dos dias */
        .fc-daygrid-day-number {
            color: #fff !important;
            font-weight: 500 !important;
            padding: 10px !important;
            font-size: 1rem !important;
        }
        
        /* Cabeçalho dos dias da semana */
        .fc-col-header-cell-cushion {
            color: #DC143C !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 1px !important;
            padding: 15px 8px !important;
            font-size: 0.9rem !important;
        }
        
        /* Células dos dias */
        .fc-daygrid-day {
            background: rgba(20, 20, 20, 0.5) !important;
            transition: all 0.3s ease !important;
        }
        
        .fc-daygrid-day:hover {
            background: rgba(220, 20, 60, 0.1) !important;
        }
        
        .fc-daygrid-day.fc-day-today {
            background: rgba(220, 20, 60, 0.15) !important;
            border: 2px solid rgba(220, 20, 60, 0.5) !important;
        }
        
        .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
            color: #DC143C !important;
            font-weight: 700 !important;
            font-size: 1.1rem !important;
        }
        
        /* Eventos do calendário */
        .fc-event {
            border-radius: 8px !important;
            padding: 6px 10px !important;
            font-size: 0.875rem !important;
            font-weight: 600 !important;
            border: none !important;
            box-shadow: 0 3px 10px rgba(0,0,0,0.4) !important;
            transition: all 0.2s ease !important;
            cursor: pointer;
            margin: 2px 0 !important;
        }
        
        .fc-event:hover {
            transform: translateY(-2px) scale(1.02) !important;
            box-shadow: 0 5px 15px rgba(0,0,0,0.5) !important;
            z-index: 10 !important;
        }
        
        .fc-event-title {
            color: #fff !important;
            font-weight: 600 !important;
            text-shadow: 0 1px 3px rgba(0,0,0,0.4) !important;
            line-height: 1.4 !important;
        }
        
        .fc-event-time {
            color: rgba(255,255,255,0.95) !important;
            font-weight: 600 !important;
            margin-right: 5px !important;
        }
        
        .fc-daygrid-event {
            white-space: normal !important;
            line-height: 1.4 !important;
        }
        
        /* Vista de semana (timeGrid) */
        .fc-timegrid-slot {
            border-color: rgba(220, 20, 60, 0.1) !important;
        }
        
        .fc-timegrid-slot-label {
            color: rgba(255,255,255,0.7) !important;
            font-weight: 500 !important;
        }
        
        .fc-timegrid-event {
            font-size: 0.85rem !important;
            border-radius: 6px !important;
            padding: 4px 8px !important;
        }
        
        .fc-timegrid-event:hover {
            transform: scale(1.03) !important;
        }
        
        /* Toolbar responsiva */
        .fc-toolbar {
            flex-wrap: wrap !important;
            gap: 10px !important;
            margin-bottom: 20px !important;
        }
        
        .fc-toolbar-chunk {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Espaçamento entre botões de visualização (Mês e Semana) */
        .fc-toolbar-chunk .fc-button-group {
            display: flex !important;
            gap: 10px !important;
        }
        
        .fc-toolbar-chunk .fc-button-group .fc-button {
            margin: 0 !important;
        }
        
        /* Container do calendário */
        .calendar-container {
            background: rgba(25, 25, 25, 0.95);
            border: 2px solid rgba(220, 20, 60, 0.2);
            border-radius: 20px;
            padding: 25px;
            backdrop-filter: blur(15px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        @media (max-width: 768px) {
            .fc-toolbar-title {
                font-size: 1.2rem !important;
            }
            
            .fc-button {
                padding: 8px 12px !important;
                font-size: 0.75rem !important;
            }
            
            .fc-col-header-cell-cushion {
                font-size: 0.8rem !important;
                padding: 10px 5px !important;
            }
            
            .fc-daygrid-day-number {
                font-size: 0.9rem !important;
                padding: 8px !important;
            }
            
            .fc-event {
                font-size: 0.75rem !important;
                padding: 4px 6px !important;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <a href="paginaInicial.php" class="logo">
                <i class="fas fa-dumbbell"></i>
                <h1>TechFit</h1>
            </a>
            <div style="display: flex; align-items: center; gap: var(--spacing-md);">
                <?php if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin'): ?>
                <a href="admin/index.php" class="admin-button">
                    <i class="fas fa-cog"></i>
                    <span>Painel Admin</span>
                </a>
                <?php endif; ?>
                <div class="user-menu">
                    <div class="user-info" onclick="toggleUserMenu()">
                        <i class="fas fa-user-circle"></i>
                        <span id="userName"><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário'); ?></span>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </div>
                    <div class="user-dropdown" id="userDropdown">
                        <a href="perfil.php" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            <span>Meu Perfil</span>
                        </a>
                        <a href="suporte.php" class="dropdown-item">
                            <i class="fas fa-headset"></i>
                            <span>Suporte</span>
                        </a>
                        <?php if ($tem_matriculas): ?>
                        <a href="minhas_matriculas.php" class="dropdown-item">
                            <i class="fas fa-graduation-cap"></i>
                            <span>Minhas Turmas</span>
                        </a>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin'): ?>
                        <div class="dropdown-divider"></div>
                        <a href="admin/index.php" class="dropdown-item" style="color: #ffd700;">
                            <i class="fas fa-cog"></i>
                            <span>Painel Administrativo</span>
                        </a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a href="#" onclick="logout(); return false;" class="dropdown-item logout-item">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Sair</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container container-main" style="max-width: 1400px; margin: 0 auto;">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-graduation-cap"></i> Minhas Turmas</h1>
                    <p class="text-muted mb-0">Acompanhe suas turmas e calendário de aulas</p>
                </div>
                <a href="turmas_disponiveis.php" class="btn btn-custom">
                    <i class="fas fa-plus"></i> Buscar Novas Turmas
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Lista de Matrículas -->
            <div class="col-md-4">
                <h5 class="text-white mb-3"><i class="fas fa-list"></i> Minhas Turmas</h5>
                <div id="matriculasList">
                    <div class="text-center py-5">
                        <div class="spinner-border text-light" role="status">
                            <span class="sr-only">Carregando...</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendário -->
            <div class="col-md-8">
                <div id="calendarioContainer">
                    <h5 class="text-white mb-3"><i class="fas fa-calendar-alt"></i> Calendário de Aulas</h5>
                    <div class="calendar-container">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales/pt-br.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="assets/js/notifications.js"></script>
    <script src="assets/js/utils.js"></script>
    <script>
        let matriculas = [];
        let calendar;
        let turmaSelecionada = null;
        
        // Verificar se há curso_id na URL para seleção automática
        const urlParams = new URLSearchParams(window.location.search);
        const cursoIdParam = urlParams.get('curso_id');

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
                    week: 'Semana'
                },
                events: [],
                eventDisplay: 'block',
                displayEventTime: true,
                displayEventEnd: true,
                eventDidMount: function(info) {
                    // Adicionar tooltip com informações completas
                    if (info.event.extendedProps && info.event.extendedProps.tooltip) {
                        $(info.el).attr('title', info.event.extendedProps.tooltip);
                    }
                }
            });
            calendar.render();

            carregarMatriculas();
        });
        
        // Função para selecionar automaticamente a primeira turma do curso especificado
        function selecionarTurmaPorCurso(cursoId) {
            if (!cursoId || matriculas.length === 0) return;
            
            // Encontrar a primeira matrícula do curso especificado
            const matricula = matriculas.find(m => m.curso_id == cursoId);
            if (matricula) {
                selecionarTurma(matricula.turma_id);
                // Scroll suave até a matrícula selecionada
                setTimeout(() => {
                    const card = document.querySelector(`[onclick="selecionarTurma(${matricula.turma_id})"]`);
                    if (card) {
                        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }, 500);
            }
        }

        function carregarMatriculas() {
            $.get('matriculas_api.php', function(response) {
                if (response.success) {
                    matriculas = response.matriculas.filter(m => m.status !== 'cancelada');
                    renderizarMatriculas();
                    
                    // Se há curso_id na URL, selecionar automaticamente
                    if (cursoIdParam) {
                        selecionarTurmaPorCurso(cursoIdParam);
                    } else if (matriculas.length > 0) {
                        // Se não há curso_id, selecionar a primeira turma automaticamente
                        selecionarTurma(matriculas[0].turma_id);
                    }
                } else {
                    toastr.error(response.message);
                }
            }).fail(function() {
                toastr.error('Erro ao carregar matrículas');
            });
        }

        function renderizarMatriculas() {
            if (matriculas.length === 0) {
                $('#matriculasList').html(`
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h5>Nenhuma matrícula</h5>
                        <p class="text-muted">Você ainda não está matriculado em nenhuma turma.</p>
                        <a href="turmas_disponiveis.php" class="btn btn-custom mt-3">
                            <i class="fas fa-search"></i> Buscar Turmas
                        </a>
                    </div>
                `);
                return;
            }

            let html = '';
            matriculas.forEach(matricula => {
                const statusClass = {
                    'pendente': 'warning',
                    'confirmada': 'success',
                    'concluida': 'secondary'
                }[matricula.status] || 'info';

                const statusText = {
                    'pendente': 'Pendente',
                    'confirmada': 'Confirmada',
                    'concluida': 'Concluída'
                }[matricula.status] || matricula.status;

                const isSelected = turmaSelecionada && turmaSelecionada.id == matricula.turma_id;

                html += `
                    <div class="matricula-card ${isSelected ? 'selected' : ''}" onclick="selecionarTurma(${matricula.turma_id})">
                        <div class="matricula-header">
                            <h6 class="matricula-title">${matricula.nome_turma}</h6>
                            <small class="text-muted">${matricula.curso_nome}</small>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-building"></i>
                            <span>${matricula.unidade_nome} - ${matricula.cidade}</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-calendar"></i>
                            <span>${formatarData(matricula.data_inicio)} a ${formatarData(matricula.data_fim)}</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span>${matricula.total_aulas || 0} aulas</span>
                        </div>
                        <div class="mt-2">
                            <span class="badge badge-${statusClass} badge-status">${statusText}</span>
                        </div>
                        ${matricula.status === 'confirmada' ? 
                            `<button class="btn btn-sm btn-outline-danger mt-2" onclick="event.stopPropagation(); cancelarMatricula(${matricula.id})">
                                <i class="fas fa-times"></i> Cancelar
                            </button>` : ''
                        }
                    </div>
                `;
            });

            $('#matriculasList').html(html);
        }

        function selecionarTurma(turmaId) {
            $.get('aulas_turma_api.php?turma_id=' + turmaId, function(response) {
                if (response.success) {
                    turmaSelecionada = response.turma;
                    const aulas = response.aulas || [];
                    
                    renderizarMatriculas(); // Atualizar seleção visual
                    
                    // Carregar aulas no calendário
                    calendar.removeAllEvents();
                    
                    if (aulas.length > 0) {
                        const events = aulas
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
                        toastr.success(`${events.length} aula(s) carregada(s) no calendário`, '', {timeOut: 2000});
                    } else {
                        toastr.info('Nenhuma aula agendada para esta turma');
                    }
                } else {
                    toastr.error(response.message || 'Erro ao carregar aulas');
                }
            }).fail(function(xhr) {
                console.error('Erro na requisição:', xhr);
                toastr.error('Erro ao carregar aulas da turma');
            });
        }

        async function cancelarMatricula(matriculaId) {
            const confirmed = await showConfirm('Tem certeza que deseja cancelar esta matrícula?', 'Confirmar Cancelamento', 'danger');
            if (!confirmed) {
                return;
            }

            $.ajax({
                url: 'matriculas_api.php',
                method: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify({
                    id: matriculaId,
                    status: 'cancelada'
                }),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        carregarMatriculas();
                        
                        // Limpar calendário se a turma cancelada estava selecionada
                        if (turmaSelecionada) {
                            const matricula = matriculas.find(m => m.id == matriculaId);
                            if (matricula && matricula.turma_id == turmaSelecionada.id) {
                                turmaSelecionada = null;
                                calendar.removeAllEvents();
                            }
                        }
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    toastr.error(response ? response.message : 'Erro ao cancelar matrícula');
                }
            });
        }

        function formatarData(data) {
            if (!data) return '';
            const d = new Date(data + 'T00:00:00');
            return d.toLocaleDateString('pt-BR');
        }

        // Configurar toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };
    </script>

    <!-- Footer -->
    <footer class="footer">
      <div class="footer-content">
        <p class="footer-text">
          <i class="fas fa-code"></i>
          Desenvolvido por <strong>Gabriel de Almeida Ramos</strong> e <strong>Julio Sena</strong>
        </p>
      </div>
    </footer>
</body>
</html>
