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
    
    // Buscar todas as unidades ativas, ordenadas por cidade e nome
    $stmt = $pdo->query("SELECT DISTINCT cidade FROM unidades WHERE ativo = 1 ORDER BY cidade");
    $cidades = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Buscar todas as unidades ativas
    $stmt = $pdo->query("SELECT * FROM unidades WHERE ativo = 1 ORDER BY cidade, nome");
    $unidades = $stmt->fetchAll();
    
    // Agrupar unidades por cidade
    $unidades_por_cidade = [];
    $contagem_por_cidade = [];
    foreach ($unidades as $unidade) {
        $cidade = $unidade['cidade'];
        if (!isset($unidades_por_cidade[$cidade])) {
            $unidades_por_cidade[$cidade] = [];
            $contagem_por_cidade[$cidade] = 0;
        }
        $unidades_por_cidade[$cidade][] = $unidade;
        $contagem_por_cidade[$cidade]++;
    }
    
} catch (Exception $e) {
    error_log("Erro ao buscar unidades: " . $e->getMessage());
    $cidades = [];
    $unidades_por_cidade = [];
    $contagem_por_cidade = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title>TechFit - Nossas Unidades</title>
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

    /* City Filter Section */
    .city-filter-section {
      display: block;
    }

    .city-filter-section.hidden {
      display: none;
    }

    .cities-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
      margin-top: 40px;
    }

    .city-card {
      background: rgba(25, 25, 25, 0.95);
      border: 2px solid rgba(255, 68, 68, 0.2);
      border-radius: 20px;
      padding: 40px 30px;
      cursor: pointer;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(10px);
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
      text-align: center;
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

    .city-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 68, 68, 0.1), transparent);
      transition: left 0.5s ease;
    }

    .city-card:hover::before {
      left: 100%;
    }

    .city-card:hover {
      transform: translateY(-10px);
      border-color: #ff4444;
      box-shadow: 0 8px 30px rgba(255, 68, 68, 0.4);
    }

    .city-card-icon {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, #b22222, #ff4444);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(255, 68, 68, 0.3);
    }

    .city-card:hover .city-card-icon {
      transform: scale(1.1) rotate(5deg);
      box-shadow: 0 6px 20px rgba(255, 68, 68, 0.5);
    }

    .city-card-icon i {
      font-size: 36px;
      color: #fff;
    }

    .city-card h3 {
      font-size: 24px;
      font-weight: 600;
      color: #fff;
      margin-bottom: 10px;
    }

    .city-card p {
      font-size: 14px;
      color: #bbb;
    }

    /* Units Section */
    .units-section {
      display: none;
    }

    .units-section.show {
      display: block;
      animation: fadeIn 0.5s ease;
    }

    .city-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 30px;
      padding-bottom: 15px;
      border-bottom: 2px solid rgba(255, 68, 68, 0.3);
    }

    .city-header-left {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .city-header i {
      font-size: 32px;
      color: #ff4444;
    }

    .city-header h2 {
      font-size: 32px;
      font-weight: 600;
      color: #fff;
      margin: 0;
    }

    .change-city-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 68, 68, 0.1);
      color: #ff4444;
      border: 2px solid rgba(255, 68, 68, 0.3);
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 14px;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
    }

    .change-city-btn:hover {
      background: rgba(255, 68, 68, 0.2);
      border-color: rgba(255, 68, 68, 0.5);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(255, 68, 68, 0.3);
    }

    /* Units Grid */
    .units-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
      gap: 30px;
    }

    /* Unit Card */
    .unit-card {
      background: rgba(25, 25, 25, 0.95);
      border: 2px solid rgba(255, 68, 68, 0.2);
      border-radius: 20px;
      padding: 30px;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(10px);
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .unit-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 68, 68, 0.1), transparent);
      transition: left 0.5s ease;
    }

    .unit-card:hover::before {
      left: 100%;
    }

    .unit-card:hover {
      transform: translateY(-5px);
      border-color: #ff4444;
      box-shadow: 0 8px 30px rgba(255, 68, 68, 0.4);
    }

    .unit-name {
      font-size: 24px;
      font-weight: 600;
      color: #ff4444;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .unit-name i {
      font-size: 20px;
    }

    .unit-info {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .unit-info-item {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      color: #bbb;
      font-size: 14px;
      line-height: 1.6;
    }

    .unit-info-item i {
      font-size: 18px;
      color: #ff4444;
      margin-top: 2px;
      min-width: 20px;
    }

    .unit-info-item strong {
      color: #fff;
      font-weight: 600;
    }

    .unit-address {
      color: #fff;
    }

    .unit-phone {
      color: #fff;
    }

    .unit-hours {
      color: #bbb;
      font-size: 13px;
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

      .cities-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .city-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }

      .city-header h2 {
        font-size: 26px;
      }

      .units-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .unit-card {
        padding: 25px 20px;
      }

      .unit-name {
        font-size: 20px;
      }
    }

    @media (max-width: 480px) {
      .logo h1 {
        font-size: 20px;
      }

      .page-header h1 {
        font-size: 28px;
      }

      .city-header h2 {
        font-size: 22px;
      }

      .unit-card {
        padding: 20px 15px;
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
      <h1>Nossas Unidades</h1>
      <p>Selecione uma cidade para ver as unidades disponíveis</p>
    </div>

    <!-- City Filter Section -->
    <div class="city-filter-section" id="cityFilterSection">
      <div class="cities-grid">
        <?php foreach ($cidades as $cidade): ?>
          <div class="city-card" onclick="showUnits('<?php echo htmlspecialchars($cidade); ?>')">
            <div class="city-card-icon">
              <i class="fas fa-map-marker-alt"></i>
            </div>
            <h3><?php echo htmlspecialchars($cidade); ?></h3>
            <p><?php echo $contagem_por_cidade[$cidade] ?? 0; ?> <?php echo ($contagem_por_cidade[$cidade] ?? 0) == 1 ? 'unidade disponível' : 'unidades disponíveis'; ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Units Section -->
    <div class="units-section" id="unitsSection">
      <div class="city-header">
        <div class="city-header-left">
          <i class="fas fa-map-marker-alt"></i>
          <h2 id="selectedCity">Cidade</h2>
        </div>
        <button class="change-city-btn" onclick="showCityFilter()">
          <i class="fas fa-arrow-left"></i>
          <span>Trocar Cidade</span>
        </button>
      </div>
      <div class="units-grid" id="unitsGrid">
        <!-- Units will be loaded here -->
      </div>
    </div>
  </div>

  <script>
    // Dados das unidades por cidade (do PHP)
    const unidadesData = <?php echo json_encode($unidades_por_cidade, JSON_UNESCAPED_UNICODE); ?>;

    function showUnits(cidade) {
      const cityFilterSection = document.getElementById('cityFilterSection');
      const unitsSection = document.getElementById('unitsSection');
      const selectedCity = document.getElementById('selectedCity');
      const unitsGrid = document.getElementById('unitsGrid');

      // Esconder filtro de cidade e mostrar unidades
      cityFilterSection.classList.add('hidden');
      unitsSection.classList.add('show');
      selectedCity.textContent = cidade;

      // Limpar grid anterior
      unitsGrid.innerHTML = '';

      // Adicionar unidades da cidade selecionada
      const unidades = unidadesData[cidade] || [];
      unidades.forEach(unidade => {
        const unitCard = document.createElement('div');
        unitCard.className = 'unit-card';
        unitCard.innerHTML = `
          <div class="unit-name">
            <i class="fas fa-dumbbell"></i>
            <span>${escapeHtml(unidade.nome)}</span>
          </div>
          <div class="unit-info">
            <div class="unit-info-item">
              <i class="fas fa-map-pin"></i>
              <span class="unit-address">${escapeHtml(unidade.endereco)}</span>
            </div>
            ${unidade.telefone ? `
            <div class="unit-info-item">
              <i class="fas fa-phone"></i>
              <span class="unit-phone"><strong>Telefone:</strong> ${escapeHtml(unidade.telefone)}</span>
            </div>
            ` : ''}
            ${unidade.horario_funcionamento ? `
            <div class="unit-info-item">
              <i class="fas fa-clock"></i>
              <span class="unit-hours"><strong>Horário:</strong> ${escapeHtml(unidade.horario_funcionamento)}</span>
            </div>
            ` : ''}
          </div>
        `;
        unitsGrid.appendChild(unitCard);
      });
    }

    function showCityFilter() {
      const cityFilterSection = document.getElementById('cityFilterSection');
      const unitsSection = document.getElementById('unitsSection');

      // Esconder unidades e mostrar filtro de cidade
      unitsSection.classList.remove('show');
      cityFilterSection.classList.remove('hidden');
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }
  </script>

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
