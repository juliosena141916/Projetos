<?php
/**
 * Página de Cursos - Dados dinâmicos do banco de dados
 */
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit;
}

require_once 'includes/conexao.php';

// Dados do usuário da sessão
$tipo_usuario = $_SESSION['tipo_usuario'] ?? 'usuario';

// Prevenir cache
header('Cache-Control: no-cache, no-store, must-revalidate');

try {
    $pdo = getConexao();
    
    // Buscar todos os cursos ativos agrupados por categoria
    $stmt = $pdo->query("SELECT * FROM cursos WHERE ativo = 1 ORDER BY categoria, nome");
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Agrupar por categoria
    $cursosPorCategoria = [];
    foreach ($cursos as $curso) {
        $cursosPorCategoria[$curso['categoria']][] = $curso;
    }
    
} catch (Exception $e) {
    $cursosPorCategoria = [];
    error_log("Erro ao carregar cursos: " . $e->getMessage());
}

// Ícones para cada categoria
$iconesCategorias = [
    'Condicionamento Físico' => 'fas fa-fire',
    'Saúde e Bem-estar' => 'fas fa-heart',
    'Especializado' => 'fas fa-star'
];

// Ícones para cada curso (baseado no ID)
$iconesCursos = [
    1 => 'fas fa-dumbbell',
    2 => 'fas fa-weight',
    3 => 'fas fa-running',
    4 => 'fas fa-spa',
    5 => 'fas fa-user-md',
    6 => 'fas fa-brain',
    7 => 'fas fa-shield-alt',
    8 => 'fas fa-walking',
    9 => 'fas fa-music'
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <title>TechFit - Nossos Cursos</title>
  <link rel="icon" href="data:,">
  
  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- CSS -->
  <link rel="stylesheet" href="assets/css/variables.css">
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/pages/cursos.css">
</head>
<body>
  <div class="header">
    <div class="header-content">
      <a href="paginaInicial.php" class="logo">
        <i class="fas fa-dumbbell"></i>
        <h1>TechFit</h1>
      </a>
      <div style="display: flex; align-items: center; gap: var(--spacing-md);">
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
      <h1>Nossos Cursos</h1>
      <p>Transforme seu corpo e mente com nossos cursos especializados</p>
    </div>

    <?php foreach ($cursosPorCategoria as $categoria => $cursosCategoria): ?>
    <div class="category-section">
      <div class="category-header">
        <i class="<?= $iconesCategorias[$categoria] ?? 'fas fa-book' ?>"></i>
        <h2><?= htmlspecialchars($categoria) ?></h2>
      </div>
      <div class="courses-grid">
        <?php foreach ($cursosCategoria as $curso): ?>
        <div class="course-card">
          <div class="course-name">
            <i class="<?= $iconesCursos[$curso['id']] ?? 'fas fa-graduation-cap' ?>"></i>
            <span><?= htmlspecialchars($curso['nome']) ?></span>
          </div>
          <p class="course-description"><?= htmlspecialchars($curso['descricao']) ?></p>
          <div class="course-info">
            <div class="course-info-item">
              <i class="fas fa-clock"></i>
              <span>Duração: <?= $curso['duracao'] ?> semanas</span>
            </div>
          </div>
          <div class="course-price">
            <span class="course-price-label">Preço</span>
            <span class="course-price-value">R$ <?= number_format($curso['valor_total'], 2, ',', '.') ?></span>
          </div>
          <a href="turmas_disponiveis.php?curso_id=<?= $curso['id'] ?>" class="btn-ver-turmas">
            <i class="fas fa-calendar-check"></i>
            <span>Ver Turmas Disponíveis</span>
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
    
    <?php if (empty($cursosPorCategoria)): ?>
    <div class="empty-state">
      <i class="fas fa-book-open"></i>
      <h3>Nenhum curso disponível</h3>
      <p>Em breve teremos novos cursos!</p>
    </div>
    <?php endif; ?>
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
</body>
</html>
