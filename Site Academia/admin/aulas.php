<?php
// Iniciar sessão apenas se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'check_admin.php';

$turma_id = $_GET['turma_id'] ?? null;
if (!$turma_id) {
    header('Location: turmas.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>TechFit - Gestão de Aulas</title>
    <link rel="icon" href="data:,">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: radial-gradient(circle at center, #1a1a1a 0%, #0d0d0d 100%);
            font-family: 'Poppins', sans-serif;
            color: #fff;
            min-height: 100vh;
        }

        .header {
            background: rgba(25, 25, 25, 0.95);
            backdrop-filter: blur(15px);
            box-shadow: 0 2px 20px rgba(220, 20, 60, 0.15);
            padding: 20px 0;
            border-bottom: 2px solid rgba(220, 20, 60, 0.2);
        }

        .header-content {
            max-width: 1400px;
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
        }

        .logo i {
            font-size: 32px;
            color: #ff4444;
        }

        .logo h1 {
            font-size: 24px;
            color: #ff4444;
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
        }

        .back-button:hover {
            background: rgba(255, 68, 68, 0.1);
            color: #ff4444;
        }

        .container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 30px;
        }

        .page-title {
            font-size: 32px;
            margin-bottom: 10px;
            color: #ff4444;
        }

        .page-subtitle {
            color: #888;
            margin-bottom: 30px;
        }

        .turma-info-box {
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid rgba(255, 68, 68, 0.3);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .turma-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-item i {
            color: #ff4444;
        }

        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            gap: 20px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 68, 68, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .btn-small {
            padding: 8px 16px;
            font-size: 12px;
        }

        .calendar-container {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 25px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .calendar-title {
            font-size: 20px;
            color: #ff4444;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }

        .day-header {
            text-align: center;
            padding: 10px;
            font-weight: 600;
            color: #ff4444;
            font-size: 14px;
        }

        .day-cell {
            aspect-ratio: 1;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            min-height: 80px;
        }

        .day-cell:hover {
            background: rgba(255, 68, 68, 0.1);
            border-color: rgba(255, 68, 68, 0.3);
        }

        .day-cell.has-class {
            background: rgba(46, 204, 113, 0.2);
            border-color: rgba(46, 204, 113, 0.4);
        }

        .day-cell.disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .day-number {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .day-classes {
            font-size: 11px;
            color: #aaa;
        }

        .aulas-list {
            margin-top: 30px;
        }

        .aula-item {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .aula-info {
            flex: 1;
        }

        .aula-date {
            font-weight: 600;
            color: #ff4444;
            margin-bottom: 5px;
        }

        .aula-time {
            color: #aaa;
            font-size: 14px;
        }

        .aula-actions {
            display: flex;
            gap: 10px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
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
            padding: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .modal-title {
            font-size: 24px;
            color: #ff4444;
        }

        .close-modal {
            background: none;
            border: none;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #ccc;
            font-size: 14px;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            font-family: 'Poppins', sans-serif;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 25px;
        }

        .auto-schedule-section {
            background: rgba(52, 152, 219, 0.1);
            border: 1px solid rgba(52, 152, 219, 0.3);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .auto-schedule-title {
            color: #3498db;
            margin-bottom: 15px;
            font-size: 16px;
        }
    </style>
    <link rel="stylesheet" href="../assets/css/notifications.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-dumbbell"></i>
                <h1>TechFit Admin</h1>
            </div>
            <a href="turmas.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Voltar para Turmas
            </a>
        </div>
    </div>

    <div class="container">
        <h1 class="page-title">Gestão de Aulas</h1>
        <p class="page-subtitle">Agende e gerencie as aulas da turma</p>

        <div class="turma-info-box" id="turmaInfo">
            <div class="turma-info-grid">
                <div class="info-item">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>Carregando informações...</span>
                </div>
            </div>
        </div>

        <div class="actions-bar">
            <div>
                <button class="btn btn-primary" onclick="openAutoScheduleModal()">
                    <i class="fas fa-magic"></i> Agendar Automaticamente
                </button>
                <button class="btn btn-secondary" onclick="openCreateModal()">
                    <i class="fas fa-plus"></i> Adicionar Aula Individual
                </button>
            </div>
        </div>

        <div class="aulas-list" id="aulasList">
            <div style="text-align: center; padding: 40px; color: #888;">
                <i class="fas fa-spinner fa-spin"></i> Carregando aulas...
            </div>
        </div>
    </div>

    <!-- Modal Criar Aula -->
    <div id="aulaModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Adicionar Aula</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <form id="aulaForm">
                <div class="form-group">
                    <label>Data da Aula *</label>
                    <input type="date" id="dataAula" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Hora Início *</label>
                        <input type="time" id="horaInicio" required>
                    </div>
                    <div class="form-group">
                        <label>Hora Fim *</label>
                        <input type="time" id="horaFim" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Sala</label>
                    <input type="text" id="sala" placeholder="Ex: Sala 1, Estúdio A">
                </div>

                <div class="form-group">
                    <label>Observações</label>
                    <textarea id="observacoes" placeholder="Observações sobre a aula"></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Aula</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Agendamento Automático -->
    <div id="autoScheduleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Agendamento Automático de Aulas</h2>
                <button class="close-modal" onclick="closeAutoScheduleModal()">&times;</button>
            </div>
            <form id="autoScheduleForm">
                <div class="auto-schedule-section">
                    <div class="auto-schedule-title">
                        <i class="fas fa-info-circle"></i> Configure os dias e horários das aulas
                    </div>
                    
                    <div class="form-group">
                        <label>Dias da Semana</label>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="diasSemana" value="1"> Segunda-feira
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="diasSemana" value="2"> Terça-feira
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="diasSemana" value="3"> Quarta-feira
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="diasSemana" value="4"> Quinta-feira
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="diasSemana" value="5"> Sexta-feira
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="diasSemana" value="6"> Sábado
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="diasSemana" value="0"> Domingo
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Hora Início *</label>
                            <input type="time" id="autoHoraInicio" value="19:00" required>
                        </div>
                        <div class="form-group">
                            <label>Hora Fim *</label>
                            <input type="time" id="autoHoraFim" value="20:30" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Sala</label>
                        <input type="text" id="autoSala" placeholder="Ex: Sala 1, Estúdio A">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeAutoScheduleModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Gerar Aulas</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/notifications.js"></script>
    <script>
        const turmaId = <?php echo $turma_id; ?>;
        let turma = null;
        let aulas = [];

        async function init() {
            await loadTurmaInfo();
            await loadAulas();
        }

        async function loadTurmaInfo() {
            try {
                const response = await fetch(`turmas_api.php?id=${turmaId}`);
                const data = await response.json();
                
                if (data.success) {
                    turma = data.turma;
                    renderTurmaInfo();
                } else {
                    showNotification('Erro ao carregar informações da turma', 'error');
                    setTimeout(() => window.location.href = 'turmas.php', 2000);
                }
            } catch (error) {
                showNotification('Erro ao carregar turma: ' + error.message, 'error');
            }
        }

        function renderTurmaInfo() {
            const container = document.getElementById('turmaInfo');
            container.innerHTML = `
                <div class="turma-info-grid">
                    <div class="info-item">
                        <i class="fas fa-graduation-cap"></i>
                        <span><strong>${turma.curso_nome}</strong></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-users"></i>
                        <span>${turma.nome_turma}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-building"></i>
                        <span>${turma.unidade_nome}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-calendar"></i>
                        <span>${formatDate(turma.data_inicio)} a ${formatDate(turma.data_fim)}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <span>${turma.duracao} semanas</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>${turma.instrutor || 'Sem instrutor'}</span>
                    </div>
                </div>
            `;
        }

        async function loadAulas() {
            try {
                const response = await fetch(`aulas_api.php?turma_id=${turmaId}`);
                const data = await response.json();
                
                if (data.success) {
                    aulas = data.aulas;
                    renderAulas();
                } else {
                    showError('Erro ao carregar aulas');
                }
            } catch (error) {
                showError('Erro ao carregar aulas: ' + error.message);
            }
        }

        function renderAulas() {
            const container = document.getElementById('aulasList');
            
            if (aulas.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 60px; color: #888;">
                        <i class="fas fa-calendar-times" style="font-size: 48px; margin-bottom: 20px;"></i>
                        <h3>Nenhuma aula agendada</h3>
                        <p>Use o agendamento automático ou adicione aulas individualmente</p>
                    </div>
                `;
                return;
            }

            // Agrupar aulas por data
            const aulasPorData = {};
            aulas.forEach(aula => {
                if (!aulasPorData[aula.data_aula]) {
                    aulasPorData[aula.data_aula] = [];
                }
                aulasPorData[aula.data_aula].push(aula);
            });

            let html = '<h3 style="color: #ff4444; margin-bottom: 20px;">Aulas Agendadas</h3>';
            
            Object.keys(aulasPorData).sort().forEach(data => {
                const aulasData = aulasPorData[data];
                html += `<div style="margin-bottom: 30px;">`;
                html += `<h4 style="color: #aaa; margin-bottom: 15px;">${formatDate(data)} - ${getDayOfWeek(data)}</h4>`;
                
                aulasData.forEach(aula => {
                    html += `
                        <div class="aula-item">
                            <div class="aula-info">
                                <div class="aula-time">
                                    <i class="fas fa-clock"></i> ${aula.hora_inicio.substring(0,5)} - ${aula.hora_fim.substring(0,5)}
                                    ${aula.sala ? `<i class="fas fa-door-open" style="margin-left: 15px;"></i> ${aula.sala}` : ''}
                                </div>
                                ${aula.observacoes ? `<div style="color: #888; font-size: 12px; margin-top: 5px;">${aula.observacoes}</div>` : ''}
                            </div>
                            <div class="aula-actions">
                                <button class="btn btn-secondary btn-small" onclick="deleteAula(${aula.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
                
                html += `</div>`;
            });

            container.innerHTML = html;
        }

        function formatDate(dateStr) {
            const date = new Date(dateStr + 'T00:00:00');
            return date.toLocaleDateString('pt-BR');
        }

        function getDayOfWeek(dateStr) {
            const days = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
            const date = new Date(dateStr + 'T00:00:00');
            return days[date.getDay()];
        }

        function openCreateModal() {
            document.getElementById('aulaForm').reset();
            document.getElementById('aulaModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('aulaModal').classList.remove('active');
        }

        function openAutoScheduleModal() {
            document.getElementById('autoScheduleForm').reset();
            document.getElementById('autoScheduleModal').classList.add('active');
        }

        function closeAutoScheduleModal() {
            document.getElementById('autoScheduleModal').classList.remove('active');
        }

        // Submit aula individual
        document.getElementById('aulaForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const aulaData = {
                turma_id: turmaId,
                data_aula: document.getElementById('dataAula').value,
                hora_inicio: document.getElementById('horaInicio').value,
                hora_fim: document.getElementById('horaFim').value,
                sala: document.getElementById('sala').value,
                observacoes: document.getElementById('observacoes').value
            };
            
            try {
                const response = await fetch('aulas_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(aulaData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeModal();
                    loadAulas();
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Erro ao criar aula: ' + error.message, 'error');
            }
        });

        // Submit agendamento automático
        document.getElementById('autoScheduleForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const diasSelecionados = Array.from(document.querySelectorAll('input[name="diasSemana"]:checked'))
                .map(cb => parseInt(cb.value));
            
            if (diasSelecionados.length === 0) {
                showNotification('Selecione pelo menos um dia da semana', 'warning');
                return;
            }
            
            const horaInicio = document.getElementById('autoHoraInicio').value;
            const horaFim = document.getElementById('autoHoraFim').value;
            const sala = document.getElementById('autoSala').value;
            
            // Gerar aulas
            const aulasParaCriar = [];
            const dataInicio = new Date(turma.data_inicio + 'T00:00:00');
            const dataFim = new Date(turma.data_fim + 'T00:00:00');
            
            for (let d = new Date(dataInicio); d <= dataFim; d.setDate(d.getDate() + 1)) {
                if (diasSelecionados.includes(d.getDay())) {
                    const dataAula = d.toISOString().split('T')[0];
                    aulasParaCriar.push({
                        turma_id: turmaId,
                        data_aula: dataAula,
                        hora_inicio: horaInicio,
                        hora_fim: horaFim,
                        sala: sala
                    });
                }
            }
            
            if (aulasParaCriar.length === 0) {
                showNotification('Nenhuma aula foi gerada com os critérios selecionados', 'warning');
                return;
            }
            
            const confirmed = await showConfirm(`Serão criadas ${aulasParaCriar.length} aulas. Confirmar?`, 'Confirmar Criação de Aulas', 'info');
            if (!confirmed) {
                return;
            }
            
            try {
                const response = await fetch('aulas_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ aulas: aulasParaCriar })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeAutoScheduleModal();
                    loadAulas();
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Erro ao criar aulas: ' + error.message, 'error');
            }
        });

        async function deleteAula(id) {
            const confirmed = await showConfirm('Tem certeza que deseja excluir esta aula?', 'Confirmar Exclusão', 'danger');
            if (!confirmed) return;
            
            try {
                const response = await fetch('aulas_api.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Aula excluída com sucesso', 'success');
                    loadAulas();
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Erro ao excluir aula: ' + error.message, 'error');
            }
        }

        function showError(message) {
            showNotification(message, 'error');
        }

        // Inicializar
        init();
    </script>
</body>
</html>
