<?php
session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/conexao.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>TechFit - Agendar Curso</title>
    <link rel="icon" href="data:,">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            min-height: 100vh;
            overflow-x: hidden;
        }

        body {
            background: radial-gradient(circle at center, #1a1a1a 0%, #0d0d0d 100%);
            font-family: 'Poppins', sans-serif;
            color: #fff;
        }

        .header {
            background: rgba(25, 25, 25, 0.95);
            backdrop-filter: blur(15px);
            box-shadow: 0 2px 20px rgba(220, 20, 60, 0.15);
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 2px solid rgba(220, 20, 60, 0.2);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            color: inherit;
        }

        .logo i {
            font-size: 32px;
            color: #ff4444;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(-5deg); }
            50% { transform: translateY(-5px) rotate(5deg); }
        }

        .logo h1 {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #ff4444;
            margin: 0;
        }

        .back-button {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #bbb;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .back-button:hover {
            background: rgba(255, 68, 68, 0.1);
            color: #ff4444;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        .page-title {
            font-size: 36px;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #ff4444 0%, #ff6b6b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            color: #888;
            margin-bottom: 40px;
            font-size: 16px;
        }

        .filters-section {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-group label {
            color: #aaa;
            font-size: 13px;
            font-weight: 500;
        }

        .filter-group select, .filter-group input {
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .filter-group select:focus, .filter-group input:focus {
            outline: none;
            border-color: rgba(255, 68, 68, 0.5);
            background: rgba(255, 255, 255, 0.08);
        }

        /* Custom styling for select dropdown options */
        .filter-group select option {
            background: #1a1a1a;
            color: #fff;
            padding: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }

        /* For better visibility on different browsers */
        .filter-group select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ff4444' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
            padding-right: 40px;
        }

        .turmas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .turma-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .turma-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff4444, #ff6b6b);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .turma-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(255, 68, 68, 0.2);
            border-color: rgba(255, 68, 68, 0.3);
        }

        .turma-card:hover::before {
            opacity: 1;
        }

        .turma-card.matriculado {
            border-color: rgba(46, 204, 113, 0.5);
            background: rgba(46, 204, 113, 0.05);
        }

        .turma-card.matriculado::before {
            background: linear-gradient(90deg, #2ecc71, #27ae60);
            opacity: 1;
        }

        .categoria-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 15px;
        }

        .cat-condicionamento { background: rgba(52, 152, 219, 0.2); color: #3498db; }
        .cat-saude { background: rgba(46, 204, 113, 0.2); color: #2ecc71; }
        .cat-especializado { background: rgba(155, 89, 182, 0.2); color: #9b59b6; }

        .curso-nome {
            font-size: 20px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .turma-nome {
            font-size: 14px;
            color: #ff4444;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .curso-descricao {
            font-size: 13px;
            color: #aaa;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .turma-info {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: #ccc;
        }

        .info-item i {
            color: #ff4444;
            width: 16px;
        }

        .info-highlight {
            font-weight: 600;
            color: #fff;
        }

        .turma-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .valor {
            font-size: 24px;
            font-weight: 700;
            color: #ff4444;
        }

        .valor-label {
            font-size: 11px;
            color: #888;
            text-transform: uppercase;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 68, 68, 0.4);
        }

        .btn-success {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid rgba(46, 204, 113, 0.3);
            cursor: default;
        }

        .btn-disabled {
            background: rgba(255, 255, 255, 0.1);
            color: #666;
            cursor: not-allowed;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #888;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: #444;
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #666;
        }

        .loading {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }

        .loading i {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: #1a1a1a;
            border: 1px solid rgba(255, 68, 68, 0.3);
            border-radius: 15px;
            padding: 35px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .modal-icon {
            font-size: 48px;
            color: #ff4444;
            margin-bottom: 15px;
        }

        .modal-title {
            font-size: 24px;
            color: #fff;
            margin-bottom: 10px;
        }

        .modal-subtitle {
            color: #888;
            font-size: 14px;
        }

        .modal-body {
            margin-bottom: 25px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .detail-label {
            color: #888;
            font-size: 14px;
        }

        .detail-value {
            color: #fff;
            font-weight: 500;
            font-size: 14px;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .minhas-matriculas-link {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            box-shadow: 0 5px 20px rgba(255, 68, 68, 0.4);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            z-index: 100;
        }

        .minhas-matriculas-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(255, 68, 68, 0.6);
        }
    </style>
    <link rel="stylesheet" href="assets/css/notifications.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <a href="paginaInicial.php" class="logo">
                <i class="fas fa-dumbbell"></i>
                <h1>TechFit</h1>
            </a>
            <a href="paginaInicial.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="container">
        <h1 class="page-title">Agendar Curso</h1>
        <p class="page-subtitle">Escolha uma turma disponível e garanta sua vaga!</p>

        <div class="filters-section">
            <div class="filters-grid">
                <div class="filter-group">
                    <label>Categoria</label>
                    <select id="filterCategoria">
                        <option value="">Todas as categorias</option>
                        <option value="Condicionamento Físico">Condicionamento Físico</option>
                        <option value="Saúde e Bem-estar">Saúde e Bem-estar</option>
                        <option value="Especializado">Especializado</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Cidade</label>
                    <select id="filterCidade">
                        <option value="">Todas as cidades</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Buscar</label>
                    <input type="text" id="searchInput" placeholder="Nome do curso...">
                </div>
            </div>
        </div>

        <div id="turmasContainer" class="loading">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Carregando turmas disponíveis...</p>
        </div>
    </div>

    <a href="minhas_matriculas.php" class="minhas-matriculas-link">
        <i class="fas fa-calendar-check"></i>
        Minhas Turmas
    </a>

    <!-- Modal de Confirmação -->
    <!-- Modal de Agenda -->
    <div id="agendaModal" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <h2 class="modal-title" id="agendaModalTitle">Agenda de Aulas</h2>
                <p class="modal-subtitle" id="agendaModalSubtitle"></p>
            </div>
            <div class="modal-body" id="agendaModalBody">
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #ff4444;"></i>
                    <p style="margin-top: 10px;">Carregando aulas...</p>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeAgendaModal()">Fechar</button>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h2 class="modal-title">Confirmar Matrícula</h2>
                <p class="modal-subtitle">Revise os detalhes antes de confirmar</p>
            </div>
            <div class="modal-body" id="modalDetails">
            </div>
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                <button class="btn btn-primary" onclick="confirmarMatricula()">
                    <i class="fas fa-check"></i> Confirmar Matrícula
                </button>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="assets/css/notifications.css">
    <script src="assets/js/notifications.js"></script>
    <script>
        let turmas = [];
        let turmaParaMatricula = null;

        async function loadTurmas() {
            try {
                const response = await fetch('turmas_disponiveis_api.php');
                const data = await response.json();
                
                if (data.success) {
                    let allTurmas = data.turmas;
                    
                    const urlParams = new URLSearchParams(window.location.search);
                    const cursoIdFromUrl = urlParams.get('curso_id');
                    let turmaParaAbrirAgenda = null;

                    if (cursoIdFromUrl) {
                        // Tenta encontrar a primeira turma disponível para o curso_id
                        turmaParaAbrirAgenda = allTurmas.find(t => t.curso_id == cursoIdFromUrl);
                        
                        // Filtra as turmas exibidas para mostrar apenas as do curso_id
                        turmas = allTurmas.filter(t => t.curso_id == cursoIdFromUrl);
                    } else {
                        turmas = allTurmas;
                    }

                    populateCidadesFilter();
                    renderTurmas(turmas);

                    if (turmaParaAbrirAgenda) {
                        // Abrir o modal de agenda para a primeira turma encontrada
                        showAgenda(turmaParaAbrirAgenda.id);
                    }
                } else {
                    showError('Erro ao carregar turmas');
                }
            } catch (error) {
                showError('Erro ao carregar turmas: ' + error.message);
            }
        }

        function populateCidadesFilter() {
            const cidades = [...new Set(turmas.map(t => t.cidade))].sort();
            const select = document.getElementById('filterCidade');
            
            cidades.forEach(cidade => {
                const option = document.createElement('option');
                option.value = cidade;
                option.textContent = cidade;
                select.appendChild(option);
            });
        }

        function renderTurmas(turmasToRender) {
            const container = document.getElementById('turmasContainer');
            
            if (turmasToRender.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>Nenhuma turma disponível</h3>
                        <p>No momento não há turmas disponíveis com os filtros selecionados.</p>
                    </div>
                `;
                container.className = '';
                return;
            }

            container.className = 'turmas-grid';
            container.innerHTML = turmasToRender.map(turma => {
                const categoriaCss = turma.categoria.toLowerCase().includes('condicionamento') ? 'condicionamento' :
                                   turma.categoria.toLowerCase().includes('saúde') ? 'saude' : 'especializado';
                
                const jaMatriculado = turma.ja_matriculado;
                const semVagas = turma.vagas_disponiveis <= 0;
                
                return `
                    <div class="turma-card ${jaMatriculado ? 'matriculado' : ''}">
                        <span class="categoria-badge cat-${categoriaCss}">${turma.categoria}</span>
                        
                        <div class="curso-nome">${turma.curso_nome}</div>
                        <div class="turma-nome">${turma.nome_turma}</div>
                        <div class="curso-descricao">${turma.curso_descricao}</div>
                        
                        <div class="turma-info">
                            <div class="info-item">
                                <i class="fas fa-building"></i>
                                <span>${turma.unidade_nome} - ${turma.cidade}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-calendar"></i>
                                <span>${formatDate(turma.data_inicio)} a ${formatDate(turma.data_fim)}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-clock"></i>
                                <span class="info-highlight">${turma.duracao} semanas</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-book"></i>
                                <span>${turma.total_aulas} aulas agendadas</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-users"></i>
                                <span>${turma.vagas_disponiveis} vagas disponíveis</span>
                            </div>
                            ${turma.instrutor ? `
                            <div class="info-item">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <span>${turma.instrutor}</span>
                            </div>
                            ` : ''}
                        </div>
                        
                        <div class="turma-footer">
                            <div>
                                <div class="valor-label">Valor Total</div>
                                <div class="valor">R$ ${parseFloat(turma.valor_total).toFixed(2)}</div>
                            </div>
                            ${jaMatriculado ? `
                                <button class="btn btn-success">
                                    <i class="fas fa-check-circle"></i> Matriculado
                                </button>
                            ` : semVagas ? `
                                <button class="btn btn-disabled" disabled>
                                    <i class="fas fa-times-circle"></i> Sem Vagas
                                </button>
                            ` : `
                                <button class="btn btn-secondary" onclick="showAgenda(${turma.id})">
                                    <i class="fas fa-calendar-alt"></i> Ver Agenda
                                </button>
                                <button class="btn btn-primary" onclick="openConfirmModal(${turma.id})">
                                    <i class="fas fa-calendar-plus"></i> Matricular
                                </button>
                            `}
                        </div>
                    </div>
                `;
            }).join('');
        }

        function formatDate(dateStr) {
            const date = new Date(dateStr + 'T00:00:00');
            return date.toLocaleDateString('pt-BR');
        }

        function closeAgendaModal() {
            document.getElementById('agendaModal').classList.remove('active');
        }

        async function showAgenda(turmaId) {
            const turma = turmas.find(t => t.id == turmaId);
            if (!turma) return;

            document.getElementById('agendaModalTitle').textContent = `Agenda: ${turma.curso_nome}`;
            document.getElementById('agendaModalSubtitle').textContent = turma.nome_turma;
            document.getElementById('agendaModalBody').innerHTML = `
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #ff4444;"></i>
                    <p style="margin-top: 10px;">Carregando aulas...</p>
                </div>
            `;
            document.getElementById('agendaModal').classList.add('active');

            try {
                const response = await fetch(`aulas_turma_api.php?turma_id=${turmaId}`);
                const data = await response.json();

                if (data.success) {
                    renderAgenda(data.aulas);
                } else {
                    document.getElementById('agendaModalBody').innerHTML = `<p style="color: #e74c3c; text-align: center;">Erro ao carregar agenda: ${data.message}</p>`;
                }
            } catch (error) {
                document.getElementById('agendaModalBody').innerHTML = `<p style="color: #e74c3c; text-align: center;">Erro de rede ao carregar agenda.</p>`;
            }
        }

        function renderAgenda(aulas) {
            if (aulas.length === 0) {
                document.getElementById('agendaModalBody').innerHTML = `<p style="color: #888; text-align: center; padding: 20px;">Nenhuma aula futura agendada para esta turma.</p>`;
                return;
            }

            let html = '<ul style="list-style: none; padding: 0;">';
            aulas.forEach(aula => {
                const dataFormatada = formatDate(aula.data_aula);
                const horaInicio = aula.hora_inicio.substring(0, 5);
                const horaFim = aula.hora_fim.substring(0, 5);
                const sala = aula.sala ? ` - ${aula.sala}` : '';

                html += `
                    <li style="padding: 12px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.05); display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-weight: 600; color: #fff;">
                            <i class="fas fa-calendar-day" style="color: #ff4444; margin-right: 8px;"></i> ${dataFormatada}
                        </div>
                        <div style="color: #ccc; font-size: 14px;">
                            <i class="fas fa-clock" style="color: #ff4444; margin-right: 8px;"></i> ${horaInicio} - ${horaFim} ${sala}
                        </div>
                    </li>
                `;
            });
            html += '</ul>';
            document.getElementById('agendaModalBody').innerHTML = html;
        }

        function openConfirmModal(turmaId) {
            const turma = turmas.find(t => t.id == turmaId);
            if (!turma) return;
            
            turmaParaMatricula = turma;
            
            const modalDetails = document.getElementById('modalDetails');
            modalDetails.innerHTML = `
                <div class="detail-item">
                    <span class="detail-label">Curso</span>
                    <span class="detail-value">${turma.curso_nome}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Turma</span>
                    <span class="detail-value">${turma.nome_turma}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Unidade</span>
                    <span class="detail-value">${turma.unidade_nome} - ${turma.cidade}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Período</span>
                    <span class="detail-value">${formatDate(turma.data_inicio)} a ${formatDate(turma.data_fim)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Duração</span>
                    <span class="detail-value">${turma.duracao} semanas (${turma.total_aulas} aulas)</span>
                </div>
                <div class="detail-item" style="border-bottom: none;">
                    <span class="detail-label">Valor Total</span>
                    <span class="detail-value" style="color: #ff4444; font-size: 20px; font-weight: 700;">R$ ${parseFloat(turma.valor_total).toFixed(2)}</span>
                </div>
            `;
            
            document.getElementById('confirmModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('confirmModal').classList.remove('active');
            turmaParaMatricula = null;
        }

        async function confirmarMatricula() {
            if (!turmaParaMatricula) return;
            
            try {
                const response = await fetch('matriculas_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        turma_id: turmaParaMatricula.id,
                        forma_pagamento: 'a_definir'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeModal();
                    loadTurmas(); // Recarregar para atualizar status
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Erro ao realizar matrícula: ' + error.message, 'error');
            }
        }

        // Filtros
        document.getElementById('filterCategoria').addEventListener('change', filterTurmas);
        document.getElementById('filterCidade').addEventListener('change', filterTurmas);
        document.getElementById('searchInput').addEventListener('input', filterTurmas);

        function filterTurmas() {
            // Se houver filtro por curso_id na URL, ele já foi aplicado no carregamento inicial.
            const urlParams = new URLSearchParams(window.location.search);
            const cursoIdFromUrl = urlParams.get('curso_id');
            
            let turmasFiltradas = turmas;

            if (cursoIdFromUrl) {
                // Se o filtro inicial já foi aplicado, a variável 'turmas' já está filtrada.
                // Não precisamos refiltrar pelo curso_id, apenas pelos outros filtros.
            } else {
                // Se não houver filtro inicial, usamos todas as turmas
                turmasFiltradas = turmas;
            }
            
            const categoria = document.getElementById('filterCategoria').value;
            const cidade = document.getElementById('filterCidade').value;
            const search = document.getElementById('searchInput').value.toLowerCase();
            
            const filtered = turmasFiltradas.filter(turma => {
                const matchCategoria = !categoria || turma.categoria === categoria;
                const matchCidade = !cidade || turma.cidade === cidade;
                const matchSearch = !search || 
                    turma.curso_nome.toLowerCase().includes(search) ||
                    turma.nome_turma.toLowerCase().includes(search) ||
                    turma.curso_descricao.toLowerCase().includes(search);
                
                return matchCategoria && matchCidade && matchSearch;
            });
            
            renderTurmas(filtered);
        }
            const categoria = document.getElementById('filterCategoria').value;
            const cidade = document.getElementById('filterCidade').value;
            const search = document.getElementById('searchInput').value.toLowerCase();
            
            const filtered = turmas.filter(turma => {
                const matchCategoria = !categoria || turma.categoria === categoria;
                const matchCidade = !cidade || turma.cidade === cidade;
                const matchSearch = !search || 
                    turma.curso_nome.toLowerCase().includes(search) ||
                    turma.nome_turma.toLowerCase().includes(search) ||
                    turma.curso_descricao.toLowerCase().includes(search);
                
                return matchCategoria && matchCidade && matchSearch;
            });
            
            renderTurmas(filtered);
        }

        function showError(message) {
            const container = document.getElementById('turmasContainer');
            container.className = '';
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Erro ao carregar</h3>
                    <p>${message}</p>
                </div>
            `;
        }

        // Inicializar
        loadTurmas();
    </script>

    <!-- Footer -->
    <footer class="footer" style="background: rgba(25, 25, 25, 0.95); border-top: 2px solid rgba(255, 68, 68, 0.2); padding: 30px 0; margin-top: 80px; text-align: center;">
      <div style="max-width: 1200px; margin: 0 auto; padding: 0 30px;">
        <p style="color: #888; font-size: 14px; margin: 0; display: flex; align-items: center; justify-content: center; gap: 10px; flex-wrap: wrap;">
          <i class="fas fa-code" style="color: #ff4444;"></i>
          Desenvolvido por <strong style="color: #bbb; font-weight: 600;">Gabriel de Almeida Ramos</strong> e <strong style="color: #bbb; font-weight: 600;">Julio Sena</strong>
        </p>
      </div>
    </footer>
</body>
</html>
