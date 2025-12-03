/**
 * TechFit - Cadastro
 * Funcionalidades da página de cadastro
 */

// Mapeamento de IDs de input para IDs de elementos de erro
const errorIdMap = {
  'nome': 'nomeError',
  'email': 'emailError',
  'senha': 'senhaError',
  'confirmar_senha': 'confirmarError'
};

document.addEventListener('DOMContentLoaded', function() {
  initCadastroForm();
  initPasswordStrength();
});

/**
 * Inicializar formulário de cadastro
 */
function initCadastroForm() {
  const form = document.getElementById('cadastroForm');
  const nomeInput = document.getElementById('nome');
  const emailInput = document.getElementById('email');
  const senhaInput = document.getElementById('senha');
  const confirmarInput = document.getElementById('confirmar_senha');

  if (!form) return;

  // Validação no blur
  if (nomeInput) nomeInput.addEventListener('blur', () => validateNome(nomeInput));
  if (emailInput) emailInput.addEventListener('blur', () => validateEmail(emailInput));
  if (senhaInput) senhaInput.addEventListener('blur', () => validateSenha(senhaInput));
  if (confirmarInput) confirmarInput.addEventListener('blur', () => validateConfirmar(confirmarInput, senhaInput));

  // Submit do formulário
  form.addEventListener('submit', handleCadastroSubmit);
}

/**
 * Inicializar indicador de força da senha
 */
function initPasswordStrength() {
  const senhaInput = document.getElementById('senha');
  const strengthBar = document.getElementById('strengthBar');
  const strengthContainer = document.getElementById('passwordStrength');
  const passwordHint = document.getElementById('passwordHint');

  if (!senhaInput || !strengthBar) return;

  senhaInput.addEventListener('focus', () => {
    if (strengthContainer) strengthContainer.classList.add('show');
    if (passwordHint) passwordHint.classList.add('show');
  });

  senhaInput.addEventListener('input', function() {
    const result = validatePasswordStrength(this.value);
    strengthBar.className = 'password-strength-bar ' + result.strengthClass;
  });
}

/**
 * Validar nome
 */
function validateNome(input) {
  if (input.value.trim().length < 3) {
    showError(input, 'Nome deve ter pelo menos 3 caracteres', errorIdMap);
    return false;
  }
  clearError(input, errorIdMap);
  return true;
}

/**
 * Validar email
 */
function validateEmail(input) {
  if (!isValidEmail(input.value)) {
    showError(input, 'E-mail inválido', errorIdMap);
    return false;
  }
  clearError(input, errorIdMap);
  return true;
}

/**
 * Validar senha
 */
function validateSenha(input) {
  const result = validatePasswordStrength(input.value);
  if (!result.isValid) {
    showError(input, result.message, errorIdMap);
    return false;
  }
  clearError(input, errorIdMap);
  return true;
}

/**
 * Validar confirmação de senha
 */
function validateConfirmar(confirmarInput, senhaInput) {
  if (!confirmarInput.value || confirmarInput.value.trim() === '') {
    showError(confirmarInput, 'Confirmação de senha é obrigatória', errorIdMap);
    return false;
  }
  if (confirmarInput.value !== senhaInput.value) {
    showError(confirmarInput, 'As senhas não coincidem', errorIdMap);
    return false;
  }
  clearError(confirmarInput, errorIdMap);
  return true;
}

/**
 * Handler do submit do cadastro
 */
async function handleCadastroSubmit(e) {
  e.preventDefault();

  const form = e.target;
  const nomeInput = document.getElementById('nome');
  const emailInput = document.getElementById('email');
  const senhaInput = document.getElementById('senha');
  const confirmarInput = document.getElementById('confirmar_senha');
  const submitBtn = document.getElementById('submitBtn');

  const isNomeValid = validateNome(nomeInput);
  const isEmailValid = validateEmail(emailInput);
  const isSenhaValid = validateSenha(senhaInput);
  const isConfirmarValid = validateConfirmar(confirmarInput, senhaInput);

  if (isNomeValid && isEmailValid && isSenhaValid && isConfirmarValid) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cadastrando...';

    try {
      const formData = new FormData(form);
      const data = await postForm('cadastro.php', formData);

      if (data.success) {
        // Salvar dados do usuário
        if (data.data) {
          saveUserData(data.data);
        }
        
        // Mostrar modal de sucesso
        document.getElementById('successModal').classList.add('show');
        
        // Redirecionar após 2 segundos
        setTimeout(() => {
          window.location.href = data.data?.redirect || 'login.html';
        }, 2000);
      } else {
        handleCadastroError(data.message, nomeInput, emailInput, senhaInput, confirmarInput);
        resetSubmitButton(submitBtn, 'Começar Treino');
      }
    } catch (error) {
      console.error('Erro:', error);
      showNotification('Erro ao processar cadastro. ' + (error.message || 'Tente novamente mais tarde.'), 'error');
      resetSubmitButton(submitBtn, 'Começar Treino');
    }
  }
}

/**
 * Tratar erro de cadastro
 */
function handleCadastroError(message, nomeInput, emailInput, senhaInput, confirmarInput) {
  const errorMessages = message.split('; ');
  let hasFieldError = false;
  
  errorMessages.forEach(msg => {
    const msgLower = msg.toLowerCase();
    if (msgLower.includes('nome')) {
      showError(nomeInput, msg, errorIdMap);
      hasFieldError = true;
    } else if (msgLower.includes('email') || msgLower.includes('e-mail')) {
      showError(emailInput, msg, errorIdMap);
      hasFieldError = true;
    } else if (msgLower.includes('senha') && !msgLower.includes('confirma')) {
      showError(senhaInput, msg, errorIdMap);
      hasFieldError = true;
    } else if (msgLower.includes('confirma')) {
      showError(confirmarInput, msg, errorIdMap);
      hasFieldError = true;
    }
  });
  
  if (!hasFieldError) {
    showNotification(message, 'error');
  }
}

/**
 * Resetar botão de submit
 */
function resetSubmitButton(btn, text) {
  btn.disabled = false;
  btn.innerHTML = '<span>' + text + '</span>';
}

