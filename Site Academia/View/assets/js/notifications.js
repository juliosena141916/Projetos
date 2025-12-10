/**
 * TechFit - Sistema de Notificações
 * Substitui alert() e confirm() por notificações estilizadas
 */

// Container de notificações
let notificationContainer = null;

/**
 * Inicializar container de notificações
 */
function initNotificationContainer() {
  if (!notificationContainer) {
    notificationContainer = document.createElement('div');
    notificationContainer.className = 'notification-container';
    document.body.appendChild(notificationContainer);
  }
  return notificationContainer;
}

/**
 * Mostrar notificação
 * @param {string} message - Mensagem a ser exibida
 * @param {string} type - Tipo: 'success', 'error', 'warning', 'info'
 * @param {number} duration - Duração em milissegundos (0 = não fecha automaticamente)
 */
function showNotification(message, type = 'info', duration = 5000) {
  const container = initNotificationContainer();
  
  const notification = document.createElement('div');
  notification.className = `notification ${type}`;
  
  const icons = {
    success: 'fa-check-circle',
    error: 'fa-exclamation-circle',
    warning: 'fa-exclamation-triangle',
    info: 'fa-info-circle'
  };
  
  notification.innerHTML = `
    <i class="fas ${icons[type] || icons.info} notification-icon"></i>
    <div class="notification-content">
      <div class="notification-message">${escapeHtml(message)}</div>
    </div>
    <button class="notification-close" onclick="closeNotification(this)">
      <i class="fas fa-times"></i>
    </button>
  `;
  
  container.appendChild(notification);
  
  // Auto-remover após duração
  if (duration > 0) {
    setTimeout(() => {
      closeNotification(notification.querySelector('.notification-close'));
    }, duration);
  }
  
  return notification;
}

/**
 * Fechar notificação
 */
function closeNotification(button) {
  const notification = button.closest('.notification');
  if (notification) {
    notification.classList.add('hiding');
    setTimeout(() => {
      notification.remove();
    }, 300);
  }
}

/**
 * Mostrar confirmação (substitui confirm())
 * @param {string} message - Mensagem de confirmação
 * @param {string} title - Título do modal
 * @param {string} type - Tipo: 'warning', 'danger', 'info'
 * @returns {Promise<boolean>} - true se confirmado, false se cancelado
 */
function showConfirm(message, title = 'Confirmar', type = 'warning') {
  return new Promise((resolve) => {
    const overlay = document.createElement('div');
    overlay.className = 'confirm-modal-overlay';
    
    const icons = {
      warning: 'fa-exclamation-triangle',
      danger: 'fa-exclamation-circle',
      info: 'fa-info-circle'
    };
    
    overlay.innerHTML = `
      <div class="confirm-modal">
        <div class="confirm-modal-icon ${type}">
          <i class="fas ${icons[type] || icons.warning}"></i>
        </div>
        <h3 class="confirm-modal-title">${escapeHtml(title)}</h3>
        <p class="confirm-modal-message">${escapeHtml(message)}</p>
        <div class="confirm-modal-buttons">
          <button class="confirm-modal-btn confirm-modal-btn-cancel" onclick="closeConfirmModal(this, false)">
            Cancelar
          </button>
          <button class="confirm-modal-btn confirm-modal-btn-${type === 'danger' ? 'danger' : 'confirm'}" onclick="closeConfirmModal(this, true)">
            Confirmar
          </button>
        </div>
      </div>
    `;
    
    document.body.appendChild(overlay);
    
    // Fechar ao clicar fora
    overlay.addEventListener('click', function(e) {
      if (e.target === overlay) {
        closeConfirmModal(overlay.querySelector('.confirm-modal-btn-cancel'), false);
      }
    });
    
    // Resolver promise quando fechar
    overlay._resolve = resolve;
  });
}

/**
 * Fechar modal de confirmação
 */
function closeConfirmModal(button, confirmed) {
  const overlay = button.closest('.confirm-modal-overlay');
  if (overlay) {
    const modal = overlay.querySelector('.confirm-modal');
    modal.classList.add('hiding');
    overlay.classList.add('hiding');
    
    setTimeout(() => {
      if (overlay._resolve) {
        overlay._resolve(confirmed);
      }
      overlay.remove();
    }, 300);
  }
}

/**
 * Escapar HTML para prevenir XSS
 */
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// Inicializar quando o DOM estiver pronto
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initNotificationContainer);
} else {
  initNotificationContainer();
}

