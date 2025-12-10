<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    // Redirecionar para login se não estiver logado
    header('Location: login.html');
    exit;
}

// Dados do usuário da sessão
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$tipo_usuario = $_SESSION['tipo_usuario'] ?? 'usuario';

// Conectar ao banco de dados
require_once 'includes/conexao.php';

try {
    $pdo = getConexao();
    
    // Buscar todos os planos ativos, ordenados por valor
    $stmt = $pdo->query("SELECT * FROM planos WHERE ativo = 1 ORDER BY valor_mensal ASC");
    $planos = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Erro ao buscar planos: " . $e->getMessage());
    $planos = [];
}

// Função para verificar se benefício está incluído
function hasBenefit($plano, $benefit) {
    return isset($plano[$benefit]) && $plano[$benefit] == 1;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title>TechFit - Nossos Planos</title>
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

    /* Header */
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

    .back-button i {
      font-size: 18px;
    }

    .admin-button {
      display: flex;
      align-items: center;
      gap: 8px;
      background: linear-gradient(135deg, #ffd700, #ffed4e);
      color: #1a1a1a;
      padding: 10px 20px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
    }

    .admin-button:hover {
      background: linear-gradient(135deg, #ffed4e, #ffd700);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(255, 215, 0, 0.4);
      color: #1a1a1a;
    }

    .admin-button i {
      font-size: 18px;
    }

    /* Container */
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px 30px;
    }

    /* Page Header */
    .page-header {
      text-align: center;
      margin-bottom: 50px;
      animation: fadeIn 0.8s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .page-header h1 {
      font-size: 42px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 10px;
      letter-spacing: 1px;
    }

    .page-header p {
      font-size: 18px;
      color: #bbb;
      font-weight: 300;
    }

    /* Plans Grid */
    .plans-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 30px;
      margin-top: 40px;
    }

    /* Plan Card */
    .plan-card {
      background: rgba(25, 25, 25, 0.95);
      border: 2px solid rgba(255, 68, 68, 0.2);
      border-radius: 20px;
      padding: 40px 30px;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(10px);
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
      display: flex;
      flex-direction: column;
      animation: slideUp 0.6s ease;
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .plan-card:nth-child(1) {
      animation-delay: 0.1s;
    }

    .plan-card:nth-child(2) {
      animation-delay: 0.2s;
      border-color: rgba(255, 68, 68, 0.4);
    }

    .plan-card:nth-child(3) {
      animation-delay: 0.3s;
      border-color: #ff4444;
      background: rgba(255, 68, 68, 0.05);
    }

    .plan-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 68, 68, 0.1), transparent);
      transition: left 0.5s ease;
    }

    .plan-card:hover::before {
      left: 100%;
    }

    .plan-card:hover {
      transform: translateY(-10px);
      border-color: #ff4444;
      box-shadow: 0 8px 30px rgba(255, 68, 68, 0.4);
    }

    .plan-badge {
      position: absolute;
      top: 20px;
      right: 20px;
      background: linear-gradient(135deg, #b22222, #ff4444);
      color: #fff;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .plan-name {
      font-size: 28px;
      font-weight: 700;
      color: #ff4444;
      margin-bottom: 10px;
    }

    .plan-description {
      color: #bbb;
      font-size: 14px;
      margin-bottom: 30px;
    }

    .plan-price {
      display: flex;
      align-items: baseline;
      gap: 10px;
      margin-bottom: 30px;
      padding-bottom: 30px;
      border-bottom: 2px solid rgba(255, 68, 68, 0.2);
    }

    .plan-price-label {
      font-size: 16px;
      color: #bbb;
    }

    .plan-price-value {
      font-size: 42px;
      font-weight: 700;
      color: #ff4444;
    }

    .plan-price-period {
      font-size: 16px;
      color: #888;
    }

    .plan-benefits {
      flex: 1;
      margin-bottom: 30px;
    }

    .plan-benefits-title {
      font-size: 18px;
      font-weight: 600;
      color: #fff;
      margin-bottom: 20px;
    }

    .benefit-item {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 15px;
      font-size: 14px;
      color: #bbb;
    }

    .benefit-item i {
      font-size: 18px;
      color: #ff4444;
      min-width: 20px;
    }

    .benefit-item.included {
      color: #fff;
    }

    .benefit-item.not-included {
      color: #555;
    }

    .benefit-item.not-included i {
      color: #555;
    }

    .plan-button {
      background: linear-gradient(135deg, #b22222, #ff4444);
      color: #fff;
      border: none;
      padding: 15px 30px;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .plan-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255, 68, 68, 0.4);
    }

    .plan-button:active {
      transform: translateY(0);
    }

    /* Responsive */
    @media (max-width: 768px) {
      .header-content {
        flex-direction: column;
        gap: 15px;
        padding: 0 20px;
      }

      .logo h1 {
        font-size: 24px;
      }

      .container {
        padding: 30px 20px;
      }

      .page-header h1 {
        font-size: 32px;
      }

      .page-header p {
        font-size: 16px;
      }

      .plans-grid {
        grid-template-columns: 1fr;
        gap: 25px;
      }

      .plan-card {
        padding: 30px 25px;
      }

      .plan-price-value {
        font-size: 36px;
      }
    }

    @media (max-width: 480px) {
      .logo h1 {
        font-size: 20px;
      }

      .page-header h1 {
        font-size: 28px;
      }

      .plan-card {
        padding: 25px 20px;
      }

      .plan-name {
        font-size: 24px;
      }

      .plan-price-value {
        font-size: 32px;
      }
    }
  </style>
</head>
<body>
  <div class="header">
    <div class="header-content">
      <a href="paginaInicial.php" class="logo">
        <i class="fas fa-dumbbell"></i>
        <h1>TechFit</h1>
      </a>
      <div style="display: flex; align-items: center; gap: 15px;">
        <?php if ($tipo_usuario === 'admin'): ?>
        <a href="admin/index.php" class="admin-button">
          <i class="fas fa-cog"></i>
          <span>Painel Admin</span>
        </a>
        <?php endif; ?>
      <a href="paginaInicial.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        <span>Voltar</span>
      </a>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="page-header">
      <h1>Nossos Planos</h1>
      <p>Escolha o plano ideal para seus objetivos</p>
    </div>

    <?php if (empty($planos)): ?>
      <div style="text-align: center; padding: 40px; color: #bbb;">
        <i class="fas fa-info-circle" style="font-size: 48px; color: #ff4444; margin-bottom: 20px;"></i>
        <p>Nenhum plano encontrado no momento.</p>
      </div>
    <?php else: ?>
      <div class="plans-grid">
        <?php 
        $badges = ['', 'Popular', 'VIP'];
        $badge_index = 0;
        foreach ($planos as $plano): 
          $badge = $badges[$badge_index] ?? '';
          $badge_index++;
        ?>
          <div class="plan-card">
            <?php if (!empty($badge)): ?>
              <div class="plan-badge"><?php echo htmlspecialchars($badge); ?></div>
            <?php endif; ?>
            <div class="plan-name"><?php echo htmlspecialchars($plano['nome']); ?></div>
            <?php if (!empty($plano['descricao'])): ?>
              <p class="plan-description"><?php echo htmlspecialchars($plano['descricao']); ?></p>
            <?php endif; ?>
            <div class="plan-price">
              <span class="plan-price-label">R$</span>
              <span class="plan-price-value"><?php echo number_format($plano['valor_mensal'], 2, ',', '.'); ?></span>
              <span class="plan-price-period">/mês</span>
            </div>
            <div class="plan-benefits">
              <div class="plan-benefits-title">Benefícios:</div>
              <div class="benefit-item <?php echo hasBenefit($plano, 'acesso_academia') ? 'included' : 'not-included'; ?>">
                <i class="fas <?php echo hasBenefit($plano, 'acesso_academia') ? 'fa-check' : 'fa-times'; ?>"></i>
                <span>Acesso à academia</span>
              </div>
              <div class="benefit-item <?php echo hasBenefit($plano, 'acesso_musculacao') ? 'included' : 'not-included'; ?>">
                <i class="fas <?php echo hasBenefit($plano, 'acesso_musculacao') ? 'fa-check' : 'fa-times'; ?>"></i>
                <span>Acesso à área de musculação</span>
              </div>
              <div class="benefit-item <?php echo hasBenefit($plano, 'aulas_grupais_ilimitadas') ? 'included' : 'not-included'; ?>">
                <i class="fas <?php echo hasBenefit($plano, 'aulas_grupais_ilimitadas') ? 'fa-check' : 'fa-times'; ?>"></i>
                <span>Aulas em grupo ilimitadas</span>
              </div>
              <div class="benefit-item <?php echo hasBenefit($plano, 'avaliacao_fisica') ? 'included' : 'not-included'; ?>">
                <i class="fas <?php echo hasBenefit($plano, 'avaliacao_fisica') ? 'fa-check' : 'fa-times'; ?>"></i>
                <span>Avaliação física</span>
              </div>
              <?php if (hasBenefit($plano, 'acesso_todos_cursos')): ?>
                <div class="benefit-item included">
                  <i class="fas fa-check"></i>
                  <span>Acesso a todos os cursos</span>
                </div>
              <?php elseif (isset($plano['quantidade_cursos']) && $plano['quantidade_cursos'] > 0): ?>
                <div class="benefit-item included">
                  <i class="fas fa-check"></i>
                  <span>Acesso a <?php echo $plano['quantidade_cursos']; ?> <?php echo $plano['quantidade_cursos'] == 1 ? 'curso' : 'cursos'; ?></span>
                </div>
              <?php else: ?>
                <div class="benefit-item not-included">
                  <i class="fas fa-times"></i>
                  <span>Acesso a cursos</span>
                </div>
              <?php endif; ?>
              <div class="benefit-item <?php echo hasBenefit($plano, 'acesso_todas_unidades') ? 'included' : 'not-included'; ?>">
                <i class="fas <?php echo hasBenefit($plano, 'acesso_todas_unidades') ? 'fa-check' : 'fa-times'; ?>"></i>
                <span>Acesso a todas as unidades</span>
              </div>
              <div class="benefit-item <?php echo hasBenefit($plano, 'personal_trainer') ? 'included' : 'not-included'; ?>">
                <i class="fas <?php echo hasBenefit($plano, 'personal_trainer') ? 'fa-check' : 'fa-times'; ?>"></i>
                <span>Personal Trainer</span>
              </div>
              <div class="benefit-item <?php echo hasBenefit($plano, 'nutricionista') ? 'included' : 'not-included'; ?>">
                <i class="fas <?php echo hasBenefit($plano, 'nutricionista') ? 'fa-check' : 'fa-times'; ?>"></i>
                <span>Nutricionista</span>
              </div>
              <div class="benefit-item <?php echo hasBenefit($plano, 'app_exclusivo') ? 'included' : 'not-included'; ?>">
                <i class="fas <?php echo hasBenefit($plano, 'app_exclusivo') ? 'fa-check' : 'fa-times'; ?>"></i>
                <span>App exclusivo</span>
              </div>
              <div class="benefit-item <?php echo hasBenefit($plano, 'desconto_loja') ? 'included' : 'not-included'; ?>">
                <i class="fas <?php echo hasBenefit($plano, 'desconto_loja') ? 'fa-check' : 'fa-times'; ?>"></i>
                <span>Desconto na loja</span>
              </div>
            </div>
            <button class="plan-button">Assinar Agora</button>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

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

