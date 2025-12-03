<?php
require_once 'check_admin.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensagens de Suporte - TechFit</title>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
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
        .message-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #007bff;
        }
        .message-card.aberta {
            border-left-color: #ffc107;
        }
        .message-card.em_atendimento {
            border-left-color: #17a2b8;
        }
        .message-card.resolvida {
            border-left-color: #28a745;
        }
        .message-card.fechada {
            border-left-color: #6c757d;
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        .message-meta {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .badge-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-status.aberta {
            background: #ffc107;
            color: #000;
        }
        .badge-status.em_atendimento {
            background: #17a2b8;
            color: #fff;
        }
        .badge-status.resolvida {
            background: #28a745;
            color: #fff;
        }
        .badge-status.fechada {
            background: #6c757d;
            color: #fff;
        }
        .badge-tipo {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            background: #e9ecef;
            color: #495057;
        }
        .message-body {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .message-actions {
            display: flex;
            gap: 10px;
        }
        .response-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
        .response-form {
            margin-top: 15px;
        }
        .filter-buttons {
            margin-bottom: 20px;
        }
        .filter-buttons .btn {
            margin-right: 10px;
            margin-bottom: 10px;
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
                <a class="nav-link active" href="suporte.php">
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
            <h1 class="h2 mb-0">Mensagens de Suporte</h1>
            <p class="text-muted mb-0">Gerencie todas as mensagens enviadas pelos usuários</p>
        </div>

        <!-- Filtros -->
        <div class="filter-buttons">
            <button class="btn btn-sm btn-outline-primary" onclick="filtrarMensagens('todas')">
                <i class="fas fa-list"></i> Todas
            </button>
            <button class="btn btn-sm btn-outline-warning" onclick="filtrarMensagens('aberta')">
                <i class="fas fa-clock"></i> Abertas
            </button>
            <button class="btn btn-sm btn-outline-info" onclick="filtrarMensagens('em_atendimento')">
                <i class="fas fa-user-check"></i> Em Atendimento
            </button>
            <button class="btn btn-sm btn-outline-success" onclick="filtrarMensagens('resolvida')">
                <i class="fas fa-check-circle"></i> Resolvidas
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="filtrarMensagens('fechada')">
                <i class="fas fa-times-circle"></i> Fechadas
            </button>
        </div>

        <!-- Container de mensagens -->
        <div id="mensagensContainer">
            <div class="text-center">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>Carregando mensagens...</p>
            </div>
        </div>
    </main>

    <!-- Modal para responder -->
    <div class="modal fade" id="modalResponder" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Responder Mensagem</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="mensagemOriginal" class="mb-3"></div>
                    <form id="formResponder">
                        <input type="hidden" id="mensagemId" name="mensagem_id">
                        <div class="form-group">
                            <label for="resposta">Resposta:</label>
                            <textarea class="form-control" id="resposta" name="resposta" rows="5" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="novoStatus">Status:</label>
                            <select class="form-control" id="novoStatus" name="status">
                                <option value="aberta">Aberta</option>
                                <option value="em_atendimento">Em Atendimento</option>
                                <option value="resolvida">Resolvida</option>
                                <option value="fechada">Fechada</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarResposta">Salvar Resposta</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../assets/js/notifications.js"></script>
    <script>
        let todasMensagens = [];
        let filtroAtual = 'todas';

        // Carregar mensagens
        function carregarMensagens() {
            $.ajax({
                url: 'suporte_api.php?action=list',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        todasMensagens = response.data;
                        renderizarMensagens();
                    } else {
                        $('#mensagensContainer').html('<div class="alert alert-danger">Erro ao carregar mensagens</div>');
                    }
                },
                error: function() {
                    $('#mensagensContainer').html('<div class="alert alert-danger">Erro ao conectar ao servidor</div>');
                }
            });
        }

        // Renderizar mensagens
        function renderizarMensagens() {
            let mensagens = todasMensagens;
            
            if (filtroAtual !== 'todas') {
                mensagens = mensagens.filter(m => m.status === filtroAtual);
            }

            if (mensagens.length === 0) {
                $('#mensagensContainer').html('<div class="alert alert-info">Nenhuma mensagem encontrada</div>');
                return;
            }

            let html = '';
            mensagens.forEach(function(msg) {
                const tipoLabels = {
                    'duvida': 'Dúvida',
                    'problema': 'Problema Técnico',
                    'sugestao': 'Sugestão',
                    'outro': 'Outro'
                };

                const statusLabels = {
                    'aberta': 'Aberta',
                    'em_atendimento': 'Em Atendimento',
                    'resolvida': 'Resolvida',
                    'fechada': 'Fechada'
                };

                const dataFormatada = new Date(msg.data_criacao).toLocaleString('pt-BR');
                const respostaHtml = msg.resposta 
                    ? `<div class="response-section">
                        <strong>Resposta:</strong>
                        <div class="message-body">${msg.resposta}</div>
                        <small class="text-muted">Respondido em: ${msg.data_resposta ? new Date(msg.data_resposta).toLocaleString('pt-BR') : 'N/A'}</small>
                       </div>`
                    : '';

                html += `
                    <div class="message-card ${msg.status}">
                        <div class="message-header">
                            <div>
                                <h5>${msg.assunto}</h5>
                                <div class="message-meta">
                                    <span class="badge badge-tipo">${tipoLabels[msg.tipo] || msg.tipo}</span>
                                    <span class="badge badge-status ${msg.status}">${statusLabels[msg.status] || msg.status}</span>
                                    <small class="text-muted">Por: ${msg.usuario_nome}</small>
                                    <small class="text-muted">Data: ${dataFormatada}</small>
                                </div>
                            </div>
                        </div>
                        <div class="message-body">
                            ${msg.mensagem.replace(/\n/g, '<br>')}
                        </div>
                        ${respostaHtml}
                        <div class="message-actions">
                            ${!msg.resposta ? `<button class="btn btn-sm btn-primary" onclick="abrirModalResponder(${msg.id})">
                                <i class="fas fa-reply"></i> Responder
                            </button>` : ''}
                            ${msg.resposta ? `<button class="btn btn-sm btn-info" onclick="abrirModalResponder(${msg.id})">
                                <i class="fas fa-edit"></i> Editar Resposta
                            </button>` : ''}
                        </div>
                    </div>
                `;
            });
            $('#mensagensContainer').html(html);
        }

        // Filtrar mensagens
        function filtrarMensagens(status) {
            filtroAtual = status;
            renderizarMensagens();
        }

        // Abrir modal de resposta
        function abrirModalResponder(id) {
            const mensagem = todasMensagens.find(m => m.id == id);
            if (!mensagem) {
                showNotification('Mensagem não encontrada', 'error');
                return;
            }

            $('#mensagemId').val(mensagem.id);
            $('#resposta').val(mensagem.resposta || '');
            $('#novoStatus').val(mensagem.status);

            $('#mensagemOriginal').html(`
                <div class="alert alert-light">
                    <strong>Mensagem Original:</strong><br>
                    ${mensagem.mensagem.replace(/\n/g, '<br>')}
                </div>
            `);

            $('#modalResponder').modal('show');
        }

        // Salvar resposta
        $('#btnSalvarResposta').on('click', function() {
            const formData = {
                action: 'responder',
                mensagem_id: $('#mensagemId').val(),
                resposta: $('#resposta').val(),
                status: $('#novoStatus').val()
            };

            $.ajax({
                url: 'suporte_api.php',
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification(response.message, 'success');
                        $('#modalResponder').modal('hide');
                        carregarMensagens();
                    } else {
                        showNotification(response.message, 'error');
                    }
                },
                error: function() {
                    showNotification('Erro ao salvar resposta', 'error');
                }
            });
        });

        // Carregar ao iniciar
        $(document).ready(function() {
            carregarMensagens();
        });
    </script>
</body>
</html>

