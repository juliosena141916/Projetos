/**
 * TechFit - Funções Utilitárias
 * Funções comuns usadas em todo o site
 */

// ========================================
// MENU DROPDOWN
// ========================================

/**
 * Toggle do menu dropdown do usuário
 */
function toggleUserMenu() {
  const dropdown = document.getElementById('userDropdown');
  if (dropdown) {
    dropdown.classList.toggle('show');
  }
}

/**
 * Fechar menu ao clicar fora
 */
function initDropdownClose() {
  window.addEventListener('click', function(event) {
    const userMenu = document.querySelector('.user-menu');
    const dropdown = document.getElementById('userDropdown');
    if (userMenu && dropdown && !userMenu.contains(event.target)) {
      dropdown.classList.remove('show');
    }
  });
}

// ========================================
// AUTENTICAÇÃO
// ========================================

/**
 * Carregar nome do usuário do localStorage
 */
function loadUserName() {
  const userName = localStorage.getItem('usuario_nome') || 'Usuário';
  const userNameElement = document.getElementById('userName');
  if (userNameElement) {
    userNameElement.textContent = userName;
  }
}

/**
 * Função de logout
 */
async function logout() {
  const confirmed = await showConfirm('Tem certeza que deseja sair?', 'Confirmar Saída', 'warning');
  if (confirmed) {
    localStorage.clear();
    window.location.href = 'logout.php';
  }
}

/**
 * Salvar dados do usuário no localStorage
 */
function saveUserData(data) {
  if (data.usuario_nome) {
    localStorage.setItem('usuario_nome', data.usuario_nome);
  }
  if (data.usuario_email) {
    localStorage.setItem('usuario_email', data.usuario_email);
  }
}

// ========================================
// VALIDAÇÃO DE FORMULÁRIOS
// ========================================

/**
 * Mostrar erro em um campo
 * @param {HTMLElement} input - Elemento de input
 * @param {string} message - Mensagem de erro
 * @param {Object} errorIdMap - Mapeamento de IDs (opcional)
 */
function showError(input, message, errorIdMap = null) {
  let errorId;
  if (errorIdMap && errorIdMap[input.id]) {
    errorId = errorIdMap[input.id];
  } else {
    errorId = input.id + 'Error';
  }
  
  const errorDiv = document.getElementById(errorId);
  if (errorDiv) {
    errorDiv.textContent = message;
    errorDiv.classList.add('show');
  }
  input.classList.add('error');
  input.classList.remove('success');
}

/**
 * Limpar erro de um campo
 * @param {HTMLElement} input - Elemento de input
 * @param {Object} errorIdMap - Mapeamento de IDs (opcional)
 */
function clearError(input, errorIdMap = null) {
  let errorId;
  if (errorIdMap && errorIdMap[input.id]) {
    errorId = errorIdMap[input.id];
  } else {
    errorId = input.id + 'Error';
  }
  
  const errorDiv = document.getElementById(errorId);
  if (errorDiv) {
    errorDiv.classList.remove('show');
  }
  input.classList.remove('error');
  input.classList.add('success');
}

/**
 * Validar email
 * @param {string} email - Email a ser validado
 * @returns {boolean}
 */
function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

/**
 * Validar força da senha
 * @param {string} password - Senha a ser validada
 * @returns {Object} - {isValid: boolean, strength: number, message: string}
 */
function validatePasswordStrength(password) {
  let strength = 0;
  let message = '';

  if (password.length >= 8) strength++;
  if (password.match(/[a-z]/)) strength++;
  if (password.match(/[A-Z]/)) strength++;
  if (password.match(/[0-9]/)) strength++;
  if (password.match(/[^a-zA-Z0-9]/)) strength++;

  if (password.length < 8) {
    message = 'Senha deve ter pelo menos 8 caracteres';
  } else if (!/[A-Z]/.test(password)) {
    message = 'Senha deve conter pelo menos uma letra maiúscula';
  } else if (!/[a-z]/.test(password)) {
    message = 'Senha deve conter pelo menos uma letra minúscula';
  } else if (!/[0-9]/.test(password)) {
    message = 'Senha deve conter pelo menos um número';
  }

  return {
    isValid: password.length >= 8 && /[A-Z]/.test(password) && /[a-z]/.test(password) && /[0-9]/.test(password),
    strength: strength,
    strengthClass: strength <= 2 ? 'weak' : (strength <= 3 ? 'medium' : 'strong'),
    message: message
  };
}

// ========================================
// TOGGLE DE SENHA
// ========================================

/**
 * Inicializar toggles de senha
 */
function initPasswordToggles() {
  const toggleButtons = document.querySelectorAll('.toggle-password');
  toggleButtons.forEach(btn => {
    btn.addEventListener('click', function() {
      const input = this.previousElementSibling.previousElementSibling;
      const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
      input.setAttribute('type', type);
      this.classList.toggle('fa-eye');
      this.classList.toggle('fa-eye-slash');
    });
  });
}

// ========================================
// REQUISIÇÕES HTTP
// ========================================

/**
 * Fazer requisição POST com FormData
 * @param {string} url - URL da requisição
 * @param {FormData} formData - Dados do formulário
 * @returns {Promise}
 */
async function postForm(url, formData) {
  const response = await fetch(url, {
    method: 'POST',
    body: formData,
    credentials: 'same-origin'
  });
  
  const contentType = response.headers.get('content-type');
  if (contentType && contentType.includes('application/json')) {
    return await response.json();
  }
  
  const text = await response.text();
  try {
    return JSON.parse(text);
  } catch (e) {
    throw new Error('Resposta não é JSON válido: ' + text.substring(0, 200));
  }
}

/**
 * Fazer requisição GET
 * @param {string} url - URL da requisição
 * @returns {Promise}
 */
async function fetchData(url) {
  const response = await fetch(url, {
    credentials: 'same-origin'
  });
  return await response.json();
}

// ========================================
// UTILITÁRIOS
// ========================================

/**
 * Formatar data para exibição
 * @param {string} dateString - String de data
 * @returns {string}
 */
function formatDate(dateString) {
  if (!dateString) return 'Não disponível';
  const date = new Date(dateString);
  return date.toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

/**
 * Formatar moeda para exibição
 * @param {number} value - Valor numérico
 * @returns {string}
 */
function formatCurrency(value) {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value);
}

// ========================================
// INICIALIZAÇÃO
// ========================================

/**
 * Inicializar funcionalidades comuns
 */
function initCommon() {
  loadUserName();
  initDropdownClose();
  initPasswordToggles();
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', initCommon);

