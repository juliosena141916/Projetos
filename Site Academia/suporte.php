<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit;
}

// Dados do usuário da sessão
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$tipo_usuario = $_SESSION['tipo_usuario'] ?? 'usuario';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TechFit - Suporte</title>
  <link rel="icon" href="data:,">
  
  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- CSS -->
  <link rel="stylesheet" href="assets/css/variables.css">
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/notifications.css">
  
  <style>
    .support-container {
      max-width: 800px;
      margin: 40px auto;
      padding: 0 var(--spacing-2xl);
    }

    .support-header {
      text-align: center;
      margin-bottom: 40px;
    }

    .support-header h1 {
      font-size: var(--font-size-4xl);
      font-weight: var(--font-bold);
      color: var(--color-primary);
      margin-bottom: var(--spacing-sm);
    }

    .support-header p {
      font-size: var(--font-size-md);
      color: var(--color-text-muted);
    }

    .support-form {
      background: rgba(40, 40, 40, 0.5);
      border: 2px solid var(--color-primary-alpha-2);
      border-radius: var(--radius-xl);
      padding: var(--spacing-3xl);
      margin-bottom: var(--spacing-2xl);
    }

    .form-group {
      margin-bottom: var(--spacing-xl);
    }

    .form-group label {
      display: block;
      color: var(--color-text-white);
      font-weight: var(--font-semibold);
      margin-bottom: var(--spacing-sm);
      font-size: var(--font-size-md);
    }

    .form-group select,
    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 12px 15px;
      background: rgba(40, 40, 40, 0.9);
      border: 2px solid var(--color-primary-alpha-2);
      border-radius: var(--radius-md);
      color: var(--color-text-white);
      font-size: var(--font-size-base);
      font-family: 'Poppins', sans-serif;
      transition: all 0.3s ease;
    }

    .form-group select:focus,
    .form-group input:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: var(--color-primary);
      background: rgba(50, 50, 50, 0.95);
    }

    .form-group textarea {
      min-height: 150px;
      resize: vertical;
    }

    .quick-messages {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: var(--spacing-md);
      margin-bottom: var(--spacing-xl);
    }

    .quick-message-btn {
      padding: 12px 20px;
      background: rgba(255, 68, 68, 0.1);
      border: 2px solid var(--color-primary-alpha-2);
      border-radius: var(--radius-md);
      color: var(--color-text-light);
      cursor: pointer;
      transition: all 0.3s ease;
      text-align: center;
      font-size: var(--font-size-sm);
      font-weight: var(--font-medium);
    }

    .quick-message-btn:hover {
      background: rgba(255, 68, 68, 0.2);
      border-color: var(--color-primary);
      color: var(--color-text-white);
      transform: translateY(-2px);
    }

    .quick-message-btn.active {
      background: linear-gradient(135deg, var(--color-primary-dark), var(--color-primary));
      border-color: var(--color-primary);
      color: #fff;
    }

    .submit-btn {
      width: 100%;
      padding: 15px;
      background: linear-gradient(135deg, var(--color-primary-dark), var(--color-primary));
      color: #fff;
      border: none;
      border-radius: var(--radius-md);
      font-size: var(--font-size-md);
      font-weight: var(--font-bold);
      cursor: pointer;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .submit-btn:hover {
      background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light));
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255, 68, 68, 0.4);
    }

    .submit-btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }

    .info-box {
      background: rgba(255, 68, 68, 0.1);
      border: 2px solid var(--color-primary-alpha-2);
      border-radius: var(--radius-md);
      padding: var(--spacing-lg);
      margin-bottom: var(--spacing-xl);
      display: flex;
      align-items: flex-start;
      gap: var(--spacing-md);
    }

    .info-box i {
      color: var(--color-primary);
      font-size: var(--font-size-xl);
      margin-top: 2px;
    }

    .info-box p {
      color: var(--color-text-light);
      font-size: var(--font-size-sm);
      line-height: 1.6;
      margin: 0;
    }

    /* Histórico de Mensagens */
    .messages-history {
      margin-top: 60px;
    }

    .messages-history .section-title {
      font-size: var(--font-size-2xl);
      font-weight: var(--font-bold);
      color: var(--color-text-white);
      margin-bottom: var(--spacing-xl);
      display: flex;
      align-items: center;
      gap: var(--spacing-md);
    }

    .message-item {
      background: rgba(40, 40, 40, 0.5);
      border: 2px solid var(--color-primary-alpha-2);
      border-radius: var(--radius-lg);
      padding: var(--spacing-xl);
      margin-bottom: var(--spacing-lg);
      transition: all 0.3s ease;
    }

    .message-item:hover {
      border-color: var(--color-primary);
      transform: translateY(-2px);
    }

    .message-item.respondida {
      border-left: 4px solid var(--color-primary);
    }

    .message-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: var(--spacing-md);
      flex-wrap: wrap;
      gap: var(--spacing-sm);
    }

    .message-title {
      font-size: var(--font-size-lg);
      font-weight: var(--font-bold);
      color: var(--color-text-white);
      margin: 0;
    }

    .message-meta {
      display: flex;
      gap: var(--spacing-md);
      flex-wrap: wrap;
      align-items: center;
    }

    .message-badge {
      padding: 4px 12px;
      border-radius: 15px;
      font-size: 11px;
      font-weight: var(--font-bold);
      text-transform: uppercase;
    }

    .badge-tipo {
      background: rgba(255, 68, 68, 0.2);
      color: var(--color-primary);
      border: 1px solid var(--color-primary-alpha-2);
    }

    .badge-status {
      background: rgba(255, 193, 7, 0.2);
      color: #ffc107;
      border: 1px solid rgba(255, 193, 7, 0.3);
    }

    .badge-status.resolvida {
      background: rgba(40, 167, 69, 0.2);
      color: #28a745;
      border-color: rgba(40, 167, 69, 0.3);
    }

    .badge-status.em_atendimento {
      background: rgba(23, 162, 184, 0.2);
      color: #17a2b8;
      border-color: rgba(23, 162, 184, 0.3);
    }

    .badge-status.fechada {
      background: rgba(108, 117, 125, 0.2);
      color: #6c757d;
      border-color: rgba(108, 117, 125, 0.3);
    }

    .message-date {
      color: var(--color-text-muted);
      font-size: var(--font-size-sm);
    }

    .message-content {
      background: rgba(0, 0, 0, 0.3);
      padding: var(--spacing-md);
      border-radius: var(--radius-md);
      margin-bottom: var(--spacing-md);
      color: var(--color-text-light);
      line-height: 1.6;
      white-space: pre-wrap;
    }

    .message-response {
      background: rgba(255, 68, 68, 0.1);
      border: 2px solid var(--color-primary-alpha-3);
      border-radius: var(--radius-md);
      padding: var(--spacing-lg);
      margin-top: var(--spacing-md);
    }

    .message-response-header {
      display: flex;
      align-items: center;
      gap: var(--spacing-sm);
      margin-bottom: var(--spacing-sm);
      color: var(--color-primary);
      font-weight: var(--font-bold);
    }

    .message-response-content {
      color: var(--color-text-light);
      line-height: 1.6;
      white-space: pre-wrap;
    }

    .message-response-date {
      color: var(--color-text-muted);
      font-size: var(--font-size-xs);
      margin-top: var(--spacing-sm);
    }

    .empty-messages {
      text-align: center;
      padding: var(--spacing-3xl);
      color: var(--color-text-muted);
    }

    .empty-messages i {
      font-size: 48px;
      color: var(--color-primary);
      margin-bottom: var(--spacing-md);
    }

    @media (max-width: 768px) {
      .support-container {
        padding: 0 var(--spacing-md);
      }

      .support-form {
        padding: var(--spacing-xl);
      }

      .quick-messages {
        grid-template-columns: 1fr;
      }

      .message-header {
        flex-direction: column;
      }

      .message-meta {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="header">
    <div class="header-content">
      <div class="logo">
        <a href="paginaInicial.php" style="text-decoration: none; display: flex; align-items: center; gap: 15px; color: inherit;">
          <i class="fas fa-dumbbell"></i>
          <h1>TechFit</h1>
        </a>
      </div>
      <div style="display: flex; align-items: center; gap: var(--spacing-md);">
        <?php if ($tipo_usuario === 'admin'): ?>
        <a href="admin/index.php" class="admin-button">
          <i class="fas fa-cog"></i>
          <span>Painel Admin</span>
        </a>
        <?php endif; ?>
        <div class="user-menu">
          <div class="user-info" onclick="toggleUserMenu()">
            <i class="fas fa-user-circle"></i>
            <span id="userName"><?php echo htmlspecialchars($usuario_nome); ?></span>
            <i class="fas fa-chevron-down dropdown-arrow"></i>
          </div>
          <div class="user-dropdown" id="userDropdown">
            <a href="perfil.php" class="dropdown-item">
              <i class="fas fa-user"></i>
              <span>Meu Perfil</span>
            </a>
            <a href="suporte.php" class="dropdown-item active">
              <i class="fas fa-headset"></i>
              <span>Suporte</span>
            </a>
            <?php if ($tipo_usuario === 'admin'): ?>
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

  <div class="support-container">
    <div class="support-header">
      <h1><i class="fas fa-headset"></i> Central de Suporte</h1>
      <p>Estamos aqui para ajudar! Entre em contato conosco</p>
    </div>

    <div class="info-box">
      <i class="fas fa-info-circle"></i>
      <p>
        <strong>Como podemos ajudar?</strong><br>
        Selecione uma mensagem rápida abaixo ou escreva sua própria mensagem. 
        Nossa equipe responderá o mais rápido possível!
      </p>
    </div>

    <form id="supportForm" class="support-form">
      <div class="form-group">
        <label for="tipo">Tipo de Solicitação</label>
        <select id="tipo" name="tipo" required>
          <option value="">Selecione o tipo</option>
          <option value="duvida">Dúvida</option>
          <option value="problema">Problema Técnico</option>
          <option value="sugestao">Sugestão</option>
          <option value="outro">Outro</option>
        </select>
      </div>

      <div class="form-group">
        <label>Mensagens Rápidas (Clique para usar)</label>
        <div class="quick-messages">
          <button type="button" class="quick-message-btn" data-message="Gostaria de mais informações sobre os planos disponíveis.">
            <i class="fas fa-tags"></i> Informações sobre Planos
          </button>
          <button type="button" class="quick-message-btn" data-message="Preciso de ajuda para me inscrever em uma turma.">
            <i class="fas fa-graduation-cap"></i> Ajuda com Inscrição
          </button>
          <button type="button" class="quick-message-btn" data-message="Estou com problemas para acessar minha conta.">
            <i class="fas fa-user-lock"></i> Problema de Acesso
          </button>
          <button type="button" class="quick-message-btn" data-message="Gostaria de cancelar minha matrícula.">
            <i class="fas fa-times-circle"></i> Cancelar Matrícula
          </button>
          <button type="button" class="quick-message-btn" data-message="Tenho uma dúvida sobre o calendário de aulas.">
            <i class="fas fa-calendar"></i> Dúvida sobre Calendário
          </button>
          <button type="button" class="quick-message-btn" data-message="Gostaria de sugerir uma melhoria no sistema.">
            <i class="fas fa-lightbulb"></i> Sugestão de Melhoria
          </button>
        </div>
      </div>

      <div class="form-group">
        <label for="assunto">Assunto</label>
        <input type="text" id="assunto" name="assunto" placeholder="Digite o assunto da sua mensagem" required>
      </div>

      <div class="form-group">
        <label for="mensagem">Mensagem</label>
        <textarea id="mensagem" name="mensagem" placeholder="Descreva sua dúvida, problema ou sugestão..." required></textarea>
      </div>

      <button type="submit" class="submit-btn" id="submitBtn">
        <i class="fas fa-paper-plane"></i> Enviar Mensagem
      </button>
    </form>

    <!-- Histórico de Mensagens -->
    <section class="messages-history">
      <h2 class="section-title">
        <i class="fas fa-history"></i> Minhas Mensagens
      </h2>
      <div id="mensagensHistorico">
        <div class="text-center">
          <i class="fas fa-spinner fa-spin fa-2x"></i>
          <p>Carregando mensagens...</p>
        </div>
      </div>
    </section>
  </div>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-content">
      <p class="footer-text">
        <i class="fas fa-code"></i>
        Desenvolvido por <strong>Gabriel de Almeida Ramos</strong> e <strong>Julio Sena</strong>
      </p>
    </div>
  </footer>

  <!-- JavaScript -->
  <script src="assets/js/notifications.js"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script>
    // Toggle do menu dropdown
    function toggleUserMenu() {
      const dropdown = document.getElementById('userDropdown');
      dropdown.classList.toggle('show');
    }

    // Fechar menu ao clicar fora
    window.addEventListener('click', function(event) {
      const userMenu = document.querySelector('.user-menu');
      const dropdown = document.getElementById('userDropdown');
      if (userMenu && dropdown && !userMenu.contains(event.target)) {
        dropdown.classList.remove('show');
      }
    });

    async function logout() {
      const confirmed = await showConfirm('Tem certeza que deseja sair?', 'Confirmar Saída', 'warning');
      if (confirmed) {
        window.location.href = 'logout.php';
      }
    }

    // Mensagens rápidas
    document.querySelectorAll('.quick-message-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        // Remover active de todos
        document.querySelectorAll('.quick-message-btn').forEach(b => b.classList.remove('active'));
        // Adicionar active no clicado
        this.classList.add('active');
        // Preencher mensagem
        document.getElementById('mensagem').value = this.dataset.message;
        // Focar no textarea
        document.getElementById('mensagem').focus();
      });
    });

    // Carregar histórico de mensagens
    function carregarHistorico() {
      $.ajax({
        url: 'suporte_api.php?action=list',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            renderizarHistorico(response.data);
          } else {
            $('#mensagensHistorico').html('<div class="empty-messages"><i class="fas fa-exclamation-circle"></i><p>Erro ao carregar mensagens</p></div>');
          }
        },
        error: function() {
          $('#mensagensHistorico').html('<div class="empty-messages"><i class="fas fa-exclamation-circle"></i><p>Erro ao conectar ao servidor</p></div>');
        }
      });
    }

    // Renderizar histórico
    function renderizarHistorico(mensagens) {
      if (mensagens.length === 0) {
        $('#mensagensHistorico').html(`
          <div class="empty-messages">
            <i class="fas fa-inbox"></i>
            <p>Você ainda não enviou nenhuma mensagem.</p>
          </div>
        `);
        return;
      }

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

      let html = '';
      mensagens.forEach(function(msg) {
        const dataFormatada = new Date(msg.data_criacao).toLocaleString('pt-BR');
        const respostaHtml = msg.resposta 
          ? `<div class="message-response">
              <div class="message-response-header">
                <i class="fas fa-reply"></i>
                <span>Resposta do Suporte</span>
              </div>
              <div class="message-response-content">${escapeHtml(msg.resposta).replace(/\n/g, '<br>')}</div>
              <div class="message-response-date">
                Respondido em: ${msg.data_resposta ? new Date(msg.data_resposta).toLocaleString('pt-BR') : 'N/A'}
              </div>
            </div>`
          : '';

        html += `
          <div class="message-item ${msg.resposta ? 'respondida' : ''}">
            <div class="message-header">
              <h3 class="message-title">${escapeHtml(msg.assunto)}</h3>
              <div class="message-meta">
                <span class="message-badge badge-tipo">${tipoLabels[msg.tipo] || msg.tipo}</span>
                <span class="message-badge badge-status ${msg.status}">${statusLabels[msg.status] || msg.status}</span>
                <span class="message-date">${dataFormatada}</span>
              </div>
            </div>
            <div class="message-content">${escapeHtml(msg.mensagem).replace(/\n/g, '<br>')}</div>
            ${respostaHtml}
          </div>
        `;
      });
      $('#mensagensHistorico').html(html);
    }

    // Função para escapar HTML
    function escapeHtml(text) {
      const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      };
      return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
    }

    // Enviar formulário
    $('#supportForm').on('submit', function(e) {
      e.preventDefault();

      const submitBtn = $('#submitBtn');
      const originalText = submitBtn.html();
      
      submitBtn.prop('disabled', true);
      submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Enviando...');

      const formData = {
        tipo: $('#tipo').val(),
        assunto: $('#assunto').val(),
        mensagem: $('#mensagem').val()
      };

      $.ajax({
        url: 'suporte_api.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            showNotification(response.message, 'success');
            $('#supportForm')[0].reset();
            document.querySelectorAll('.quick-message-btn').forEach(b => b.classList.remove('active'));
            // Recarregar histórico
            carregarHistorico();
            // Scroll para o histórico
            setTimeout(() => {
              $('html, body').animate({
                scrollTop: $('.messages-history').offset().top - 100
              }, 500);
            }, 500);
            submitBtn.prop('disabled', false);
            submitBtn.html(originalText);
          } else {
            showNotification(response.message, 'error');
            submitBtn.prop('disabled', false);
            submitBtn.html(originalText);
          }
        },
        error: function(xhr) {
          const response = xhr.responseJSON;
          showNotification(response ? response.message : 'Erro ao enviar mensagem', 'error');
          submitBtn.prop('disabled', false);
          submitBtn.html(originalText);
        }
      });
    });

    // Carregar histórico ao iniciar
    $(document).ready(function() {
      carregarHistorico();
    });
  </script>
</body>
</html>

