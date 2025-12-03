/**
 * TechFit - Login
 * Funcionalidades da página de login
 */

document.addEventListener('DOMContentLoaded', function() {
  initLoginForm();
});

/**
 * Inicializar formulário de login
 */
function initLoginForm() {
  const form = document.getElementById('loginForm');
  const loginInput = document.getElementById('login');
  const senhaInput = document.getElementById('senha');

  if (!form || !loginInput || !senhaInput) return;

  // Validação no blur
  loginInput.addEventListener('blur', () => validateLoginField(loginInput));
  senhaInput.addEventListener('blur', () => validateSenhaField(senhaInput));

  // Submit do formulário
  form.addEventListener('submit', handleLoginSubmit);
}

/**
 * Validar campo de login
 */
function validateLoginField(input) {
  const value = input.value.trim();
  if (value.length < 3) {
    showError(input, 'Digite um e-mail válido ou nome de usuário (mín. 3 caracteres)');
    return false;
  }
  clearError(input);
  return true;
}

/**
 * Validar campo de senha
 */
function validateSenhaField(input) {
  if (input.value.length < 1) {
    showError(input, 'Senha é obrigatória');
    return false;
  }
  clearError(input);
  return true;
}

/**
 * Handler do submit do login
 */
async function handleLoginSubmit(e) {
  e.preventDefault();

  const form = e.target;
  const loginInput = document.getElementById('login');
  const senhaInput = document.getElementById('senha');
  const submitBtn = document.getElementById('submitBtn');

  const isLoginValid = validateLoginField(loginInput);
  const isSenhaValid = validateSenhaField(senhaInput);

  if (isLoginValid && isSenhaValid) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Entrando...';

    try {
      const formData = new FormData(form);
      const data = await postForm('login.php', formData);

      if (data.success) {
        // Salvar dados do usuário
        if (data.data) {
          saveUserData(data.data);
        }
        
        // Mostrar modal de sucesso
        document.getElementById('successModal').classList.add('show');
        
        // Redirecionar após 2 segundos
        setTimeout(() => {
          window.location.href = data.data?.redirect || 'paginaInicial.php';
        }, 2000);
      } else {
        handleLoginError(data.message, loginInput, senhaInput);
        resetSubmitButton(submitBtn, 'Entrar');
      }
    } catch (error) {
      console.error('Erro:', error);
      showNotification('Erro ao processar login. Tente novamente mais tarde.', 'error');
      resetSubmitButton(submitBtn, 'Entrar');
    }
  }
}

/**
 * Tratar erro de login
 */
function handleLoginError(message, loginInput, senhaInput) {
  if (message.includes('E-mail') || message.includes('email')) {
    showError(loginInput, message);
  } else if (message.includes('senha') || message.includes('Senha')) {
    showError(senhaInput, message);
  } else {
    showError(loginInput, message);
  }
}

/**
 * Resetar botão de submit
 */
function resetSubmitButton(btn, text) {
  btn.disabled = false;
  btn.innerHTML = '<span>' + text + '</span>';
}

