<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    // Redirecionar para login se não estiver logado
    header('Location: login.html');
    exit;
}

require_once 'includes/conexao.php';

// Dados do usuário da sessão
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$usuario_email = $_SESSION['usuario_email'] ?? '';
$tipo_usuario = $_SESSION['tipo_usuario'] ?? 'usuario';

// Verificar se o usuário tem matrículas ativas
$tem_matriculas = false;
try {
    $pdo = getConexao();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM matriculas 
        WHERE usuario_id = ? AND status != 'cancelada'
    ");
    $stmt->execute([$_SESSION['usuario_id']]);
    $result = $stmt->fetch();
    $tem_matriculas = ($result && $result['total'] > 0);
} catch (Exception $e) {
    // Em caso de erro, não mostrar o link
    $tem_matriculas = false;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TechFit - Página Inicial</title>
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
    /* Hero Section */
    .hero-section {
      position: relative;
      background: linear-gradient(135deg, rgba(220, 20, 60, 0.15) 0%, rgba(139, 0, 0, 0.1) 100%);
      padding: 60px 0;
      margin-bottom: 50px;
      overflow: hidden;
    }

    .hero-content {
      max-width: var(--container-max-width);
      margin: 0 auto;
      padding: 0 var(--spacing-2xl);
      text-align: center;
      position: relative;
      z-index: 2;
    }

    .hero-title {
      font-size: var(--font-size-5xl);
      font-weight: var(--font-bold);
      color: var(--color-text-white);
      margin-bottom: var(--spacing-md);
      line-height: 1.2;
    }

    .hero-name {
      color: var(--color-primary);
      text-transform: uppercase;
    }

    .hero-subtitle {
      font-size: var(--font-size-xl);
      color: var(--color-primary);
      font-weight: var(--font-semibold);
      margin-bottom: var(--spacing-md);
    }

    .hero-description {
      font-size: var(--font-size-md);
      color: var(--color-text-light);
      max-width: 700px;
      margin: 0 auto;
      line-height: 1.6;
    }

    .hero-decoration {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      z-index: 1;
      pointer-events: none;
    }

    .hero-circle {
      position: absolute;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(255, 68, 68, 0.1) 0%, transparent 70%);
      animation: float 6s ease-in-out infinite;
    }

    .hero-circle:nth-child(1) {
      width: 300px;
      height: 300px;
      top: -100px;
      right: 10%;
      animation-delay: 0s;
    }

    .hero-circle:nth-child(2) {
      width: 200px;
      height: 200px;
      bottom: -50px;
      left: 15%;
      animation-delay: 2s;
    }

    .hero-circle:nth-child(3) {
      width: 150px;
      height: 150px;
      top: 50%;
      right: 5%;
      animation-delay: 4s;
    }

    /* Sections */
    .highlights-section,
    .history-section,
    .features-section,
    .quote-section {
      margin-bottom: 80px;
    }

    .section-header {
      text-align: center;
      margin-bottom: 40px;
    }

    .section-title {
      font-size: var(--font-size-4xl);
      font-weight: var(--font-bold);
      color: var(--color-text-white);
      margin-bottom: var(--spacing-sm);
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .section-subtitle {
      font-size: var(--font-size-lg);
      color: var(--color-text-muted);
    }

    /* Cards Destaque */
    .highlight-card {
      position: relative;
      transition: all 0.4s ease;
      border: 2px solid var(--color-primary-alpha-2);
    }

    .highlight-card:hover {
      transform: translateY(-10px) scale(1.02);
      border-color: var(--color-primary);
      box-shadow: 0 15px 40px rgba(255, 68, 68, 0.3);
    }

    .card-badge {
      position: absolute;
      top: 12px;
      right: 12px;
      background: linear-gradient(135deg, var(--color-primary-dark), var(--color-primary));
      color: #fff;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 10px;
      font-weight: var(--font-bold);
      text-transform: uppercase;
      letter-spacing: 0.3px;
      box-shadow: 0 4px 12px rgba(255, 68, 68, 0.4);
      white-space: nowrap;
      z-index: 10;
      line-height: 1.2;
      max-width: 140px;
      overflow: visible;
      text-overflow: clip;
    }

    /* Badge para textos longos */
    .card-badge.badge-long {
      font-size: 8.5px;
      padding: 6px 14px;
      max-width: none;
      width: auto;
      min-width: 150px;
      letter-spacing: 0.2px;
      line-height: 1.4;
      white-space: nowrap;
      text-align: center;
      right: 8px;
    }

    /* Garantir que o badge não sobreponha o ícone */
    .highlight-card {
      padding-top: 45px;
      position: relative;
    }

    .highlight-card .card-icon {
      position: relative;
      z-index: 5;
    }

    .highlight-card .card-badge {
      z-index: 1;
      top: 10px;
      right: 10px;
    }

    .highlight-card .card-badge.badge-long {
      top: 8px;
      right: 8px;
    }

    /* História */
    .history-section {
      background: rgba(40, 40, 40, 0.3);
      border-radius: var(--radius-xl);
      padding: 50px;
      border: 2px solid var(--color-primary-alpha-2);
    }

    .history-content {
      display: grid;
      grid-template-columns: 1.5fr 1fr;
      gap: 50px;
      align-items: center;
    }

    .history-text {
      color: var(--color-text-light);
    }

    .history-paragraph {
      font-size: var(--font-size-md);
      line-height: 1.8;
      margin-bottom: var(--spacing-lg);
      color: var(--color-text-light);
    }

    .history-paragraph strong {
      color: var(--color-primary);
      font-weight: var(--font-bold);
    }

    .history-stats {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: var(--spacing-xl);
      margin-top: var(--spacing-2xl);
    }

    .stat-item {
      text-align: center;
      padding: var(--spacing-lg);
      background: rgba(255, 68, 68, 0.1);
      border-radius: var(--radius-md);
      border: 1px solid var(--color-primary-alpha-2);
    }

    .stat-number {
      font-size: var(--font-size-4xl);
      font-weight: var(--font-bold);
      color: var(--color-primary);
      margin-bottom: var(--spacing-xs);
    }

    .stat-label {
      font-size: var(--font-size-sm);
      color: var(--color-text-muted);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .history-image {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .history-icon-wrapper {
      width: 200px;
      height: 200px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--color-primary-dark), var(--color-primary));
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 20px 60px rgba(255, 68, 68, 0.4);
      animation: pulse 3s ease-in-out infinite;
    }

    .history-icon-wrapper i {
      font-size: 80px;
      color: #fff;
    }

    @keyframes pulse {
      0%, 100% {
        transform: scale(1);
        box-shadow: 0 20px 60px rgba(255, 68, 68, 0.4);
      }
      50% {
        transform: scale(1.05);
        box-shadow: 0 25px 70px rgba(255, 68, 68, 0.6);
      }
    }

    /* Features */
    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: var(--spacing-xl);
    }

    .feature-item {
      text-align: center;
      padding: var(--spacing-2xl);
      background: rgba(40, 40, 40, 0.3);
      border-radius: var(--radius-lg);
      border: 2px solid var(--color-primary-alpha-2);
      transition: all 0.3s ease;
    }

    .feature-item:hover {
      transform: translateY(-5px);
      border-color: var(--color-primary);
      background: rgba(40, 40, 40, 0.5);
    }

    .feature-icon {
      width: 70px;
      height: 70px;
      margin: 0 auto var(--spacing-lg);
      background: linear-gradient(135deg, var(--color-primary-dark), var(--color-primary));
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 10px 30px rgba(255, 68, 68, 0.3);
    }

    .feature-icon i {
      font-size: 30px;
      color: #fff;
    }

    .feature-item h3 {
      font-size: var(--font-size-lg);
      font-weight: var(--font-bold);
      color: var(--color-text-white);
      margin-bottom: var(--spacing-sm);
    }

    .feature-item p {
      font-size: var(--font-size-sm);
      color: var(--color-text-muted);
      line-height: 1.6;
    }

    /* Quote Section */
    .quote-section {
      background: linear-gradient(135deg, rgba(220, 20, 60, 0.1) 0%, rgba(139, 0, 0, 0.05) 100%);
      border-radius: var(--radius-xl);
      padding: 60px var(--spacing-2xl);
      border: 2px solid var(--color-primary-alpha-2);
      text-align: center;
    }

    .quote-content {
      max-width: 800px;
      margin: 0 auto;
    }

    .quote-icon {
      font-size: 50px;
      color: var(--color-primary);
      opacity: 0.3;
      margin-bottom: var(--spacing-lg);
    }

    .quote-text {
      font-size: var(--font-size-2xl);
      font-weight: var(--font-medium);
      color: var(--color-text-white);
      font-style: italic;
      line-height: 1.6;
      margin-bottom: var(--spacing-lg);
    }

    .quote-author {
      font-size: var(--font-size-md);
      color: var(--color-primary);
      font-weight: var(--font-semibold);
    }

    /* Responsive */
    @media (max-width: 968px) {
      .history-content {
        grid-template-columns: 1fr;
        gap: 30px;
      }

      .history-stats {
        grid-template-columns: repeat(3, 1fr);
        gap: var(--spacing-md);
      }

      .hero-title {
        font-size: var(--font-size-4xl);
      }
    }

    @media (max-width: 768px) {
      .hero-section {
        padding: 40px 0;
      }

      .hero-title {
        font-size: var(--font-size-3xl);
      }

      .hero-subtitle {
        font-size: var(--font-size-lg);
      }

      .section-title {
        font-size: var(--font-size-3xl);
      }

      .history-section {
        padding: 30px 20px;
      }

      .history-stats {
        grid-template-columns: 1fr;
      }

      .features-grid {
        grid-template-columns: 1fr;
      }

      .quote-text {
        font-size: var(--font-size-xl);
      }
    }
  </style>
</head>
<body>
  <div class="header">
    <div class="header-content">
      <div class="logo">
        <i class="fas fa-dumbbell"></i>
        <h1>TechFit</h1>
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
                    <span id="notificacaoBadge" class="notification-badge" style="display: none;">0</span>
                    <i class="fas fa-chevron-down dropdown-arrow"></i>
                </div>
          <div class="user-dropdown" id="userDropdown">
            <a href="perfil.php" class="dropdown-item">
              <i class="fas fa-user"></i>
              <span>Meu Perfil</span>
            </a>
            <a href="suporte.php" class="dropdown-item">
              <i class="fas fa-headset"></i>
              <span>Suporte</span>
            </a>
            <?php if ($tem_matriculas): ?>
            <a href="minhas_matriculas.php" class="dropdown-item">
              <i class="fas fa-graduation-cap"></i>
              <span>Minhas Turmas</span>
            </a>
            <?php endif; ?>
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

  <!-- Hero Section -->
  <section class="hero-section">
    <div class="hero-content">
      <h1 class="hero-title">Bem-vindo, <span class="hero-name"><?php echo htmlspecialchars($usuario_nome); ?></span>!</h1>
      <p class="hero-subtitle">Transforme seu corpo, mude sua vida</p>
      <p class="hero-description">Na TechFit, acreditamos que cada pessoa tem o potencial de alcançar seus objetivos. Junte-se a nós nessa jornada de transformação!</p>
    </div>
    <div class="hero-decoration">
      <div class="hero-circle"></div>
      <div class="hero-circle"></div>
      <div class="hero-circle"></div>
    </div>
  </section>

  <!-- Main Content -->
  <div class="container">
    <!-- Destaques Principais -->
    <section class="highlights-section">
      <div class="section-header">
        <h2 class="section-title">Explore o TechFit</h2>
        <p class="section-subtitle">Descubra tudo que temos para oferecer</p>
      </div>
      <div class="cards-container">
        <div class="card highlight-card" onclick="openCursos()">
          <div class="card-icon">
            <i class="fas fa-graduation-cap"></i>
          </div>
          <div class="card-content">
            <h3>Cursos</h3>
            <p>Explore nossos cursos especializados e aprenda as melhores técnicas de treinamento</p>
          </div>
          <div class="card-arrow">
            <i class="fas fa-chevron-right"></i>
          </div>
          <div class="card-badge">Popular</div>
        </div>

        <div class="card highlight-card" onclick="openUnidades()">
          <div class="card-icon">
            <i class="fas fa-building"></i>
          </div>
          <div class="card-content">
            <h3>Unidades</h3>
            <p>Encontre a unidade TechFit mais próxima de você e conheça nossa estrutura</p>
          </div>
          <div class="card-arrow">
            <i class="fas fa-chevron-right"></i>
          </div>
          <div class="card-badge badge-long">Múltiplas Localizações</div>
        </div>

        <div class="card highlight-card" onclick="openPlanos()">
          <div class="card-icon">
            <i class="fas fa-tags"></i>
          </div>
          <div class="card-content">
            <h3>Planos</h3>
            <p>Conheça nossos planos flexíveis e escolha o ideal para seus objetivos</p>
          </div>
          <div class="card-arrow">
            <i class="fas fa-chevron-right"></i>
          </div>
          <div class="card-badge">Ofertas Especiais</div>
        </div>
      </div>
    </section>

    <!-- História da Academia -->
    <section class="history-section">
      <div class="history-content">
        <div class="history-text">
          <h2 class="section-title">Nossa História</h2>
          <p class="history-paragraph">
            A <strong>TechFit</strong> nasceu da paixão por transformar vidas através do fitness e do bem-estar. 
            Fundada com a missão de democratizar o acesso a treinamentos de qualidade, nossa academia combina 
            tecnologia de ponta com metodologias comprovadas.
          </p>
          <p class="history-paragraph">
            Ao longo dos anos, desenvolvemos uma comunidade forte e engajada, onde cada membro é parte essencial 
            da nossa jornada. Acreditamos que o sucesso não é apenas sobre resultados físicos, mas sobre 
            construir confiança, disciplina e uma mentalidade vencedora.
          </p>
          <div class="history-stats">
            <div class="stat-item">
              <div class="stat-number">10+</div>
              <div class="stat-label">Anos de Experiência</div>
            </div>
            <div class="stat-item">
              <div class="stat-number">5000+</div>
              <div class="stat-label">Alunos Transformados</div>
            </div>
            <div class="stat-item">
              <div class="stat-number">50+</div>
              <div class="stat-label">Instrutores Certificados</div>
            </div>
          </div>
        </div>
        <div class="history-image">
          <div class="history-icon-wrapper">
            <i class="fas fa-dumbbell"></i>
          </div>
        </div>
      </div>
    </section>

    <!-- Diferenciais -->
    <section class="features-section">
      <div class="section-header">
        <h2 class="section-title">Por que escolher a TechFit?</h2>
        <p class="section-subtitle">Nossos diferenciais que fazem a diferença</p>
      </div>
      <div class="features-grid">
        <div class="feature-item">
          <div class="feature-icon">
            <i class="fas fa-chart-line"></i>
          </div>
          <h3>Resultados Comprovados</h3>
          <p>Metodologia baseada em ciência e resultados reais</p>
        </div>
        <div class="feature-item">
          <div class="feature-icon">
            <i class="fas fa-users"></i>
          </div>
          <h3>Comunidade Ativa</h3>
          <p>Junte-se a uma comunidade motivada e apoiadora</p>
        </div>
        <div class="feature-item">
          <div class="feature-icon">
            <i class="fas fa-clock"></i>
          </div>
          <h3>Flexibilidade Total</h3>
          <p>Horários flexíveis que se adaptam à sua rotina</p>
        </div>
        <div class="feature-item">
          <div class="feature-icon">
            <i class="fas fa-trophy"></i>
          </div>
          <h3>Instrutores Especializados</h3>
          <p>Equipe qualificada pronta para te ajudar</p>
        </div>
        <div class="feature-item">
          <div class="feature-icon">
            <i class="fas fa-mobile-alt"></i>
          </div>
          <h3>App Exclusivo</h3>
          <p>Acompanhe seu progresso de qualquer lugar</p>
        </div>
        <div class="feature-item">
          <div class="feature-icon">
            <i class="fas fa-heart"></i>
          </div>
          <h3>Bem-estar Completo</h3>
          <p>Além do físico, cuidamos da sua saúde mental</p>
        </div>
      </div>
    </section>

    <!-- Frase de Impacto -->
    <section class="quote-section">
      <div class="quote-content">
        <i class="fas fa-quote-left quote-icon"></i>
        <p class="quote-text">"O único treino ruim é aquele que você não fez. Cada dia é uma nova oportunidade de ser melhor do que ontem."</p>
        <p class="quote-author">— TechFit</p>
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
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="assets/js/notifications.js"></script>
  <script src="assets/js/utils.js"></script>
  <script src="assets/js/home.js"></script>
  <style>
    .notification-badge {
      background: #ff4444;
      color: white;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
      font-weight: bold;
      position: absolute;
      top: -5px;
      right: -5px;
      min-width: 20px;
    }
    .user-info {
      position: relative;
    }
  </style>
</body>
</html>
