/**
 * TechFit - Perfil
 * Funcionalidades da página de perfil
 */

document.addEventListener('DOMContentLoaded', function() {
  carregarPerfil();
});

/**
 * Carregar dados do perfil
 */
function carregarPerfil() {
  const loadingState = document.getElementById('loadingState');
  const errorState = document.getElementById('errorState');
  const profileContent = document.getElementById('profileContent');
  
  // Mostrar loading
  loadingState.style.display = 'flex';
  errorState.style.display = 'none';
  profileContent.style.display = 'none';
  
  // Fazer requisição para API
  fetch('perfil_api.php')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Preencher dados do perfil
        preencherPerfil(data.data);
        
        // Mostrar conteúdo
        loadingState.style.display = 'none';
        profileContent.style.display = 'block';
      } else {
        // Mostrar erro
        mostrarErro(data.message, data.data?.redirect);
      }
    })
    .catch(error => {
      console.error('Erro:', error);
      mostrarErro('Erro ao carregar dados do perfil. Verifique sua conexão.');
    });
}

/**
 * Preencher dados do perfil na página
 */
function preencherPerfil(dados) {
  // Header
  document.getElementById('profileNome').textContent = dados.nome;
  document.getElementById('profileEmail').textContent = dados.email;
  
  // Atualizar nome no header do menu
  const userNameElement = document.getElementById('userName');
  if (userNameElement) {
    userNameElement.textContent = dados.nome;
  }
  
  // Informações pessoais
  document.getElementById('infoNome').textContent = dados.nome;
  document.getElementById('infoEmail').textContent = dados.email;
  document.getElementById('infoId').textContent = '#' + dados.id;
  
  // Informações da conta
  document.getElementById('infoDataCadastro').textContent = dados.data_cadastro_formatada || 'Não disponível';
  document.getElementById('infoDataAtualizacao').textContent = dados.data_atualizacao_formatada || 'Não disponível';
  
  // Status
  const statusElement = document.getElementById('infoStatus');
  if (dados.ativo) {
    statusElement.innerHTML = '<i class="fas fa-check-circle"></i> <span>Ativa</span>';
    statusElement.className = 'status-active';
  } else {
    statusElement.innerHTML = '<i class="fas fa-times-circle"></i> <span>Inativa</span>';
    statusElement.className = 'status-inactive';
  }
  
  // Salvar no localStorage
  localStorage.setItem('usuario_nome', dados.nome);
  localStorage.setItem('usuario_email', dados.email);
}

/**
 * Mostrar estado de erro
 */
function mostrarErro(mensagem, redirect = null) {
  const loadingState = document.getElementById('loadingState');
  const errorState = document.getElementById('errorState');
  const errorMessage = document.getElementById('errorMessage');
  
  loadingState.style.display = 'none';
  errorState.style.display = 'flex';
  errorMessage.textContent = mensagem;
  
  // Se houver redirecionamento (usuário não autenticado)
  if (redirect) {
    setTimeout(() => {
      window.location.href = redirect;
    }, 2000);
  }
}

