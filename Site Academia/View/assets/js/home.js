/**
 * TechFit - Página Inicial
 * Funcionalidades da página inicial
 */

/**
 * Abrir página de cursos
 */
function openCursos() {
  window.location.href = 'cursos.php';
}

/**
 * Abrir página de unidades
 */
function openUnidades() {
  window.location.href = 'unidades.php';
}

/**
 * Abrir página de planos
 */
function openPlanos() {
  window.location.href = 'planos.php';
}

// Carregar notificações
function carregarNotificacoes() {
  if (typeof $ === 'undefined') return;
  
  $.ajax({
    url: 'notificacoes_api.php?action=minhas_notificacoes',
    method: 'GET',
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        const nao_lidas = response.nao_lidas || 0;
        const badge = $('#notificacaoBadge');
        
        if (nao_lidas > 0) {
          badge.text(nao_lidas > 99 ? '99+' : nao_lidas);
          badge.show();
        } else {
          badge.hide();
        }
      }
    },
    error: function() {
      // Silenciar erro, não é crítico
    }
  });
}

// Carregar notificações ao iniciar
if (typeof $ !== 'undefined') {
  $(document).ready(function() {
    carregarNotificacoes();
    // Atualizar a cada 30 segundos
    setInterval(carregarNotificacoes, 30000);
  });
}

