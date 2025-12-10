<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    // Redirecionar para login se não estiver logado
    header('Location: login.html');
    exit;
}

// Incluir arquivo de conexão
require_once 'includes/conexao.php';

// Buscar dados completos do usuário
try {
    $pdo = getConexao();
    $stmt = $pdo->prepare("SELECT id, nome, email, data_cadastro, data_atualizacao FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        // Se não encontrar o usuário, destruir sessão e redirecionar
        session_destroy();
        header('Location: login.html');
        exit;
    }
    
    // Atualizar dados da sessão
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    
    // Buscar matrículas do usuário
    $stmt = $pdo->prepare("
        SELECT m.*, t.nome_turma, t.data_inicio, t.data_fim,
               c.id as curso_id, c.nome as curso_nome, c.categoria, c.duracao,
               u.nome as unidade_nome, u.cidade
        FROM matriculas m
        JOIN turmas_cursos t ON m.turma_id = t.id
        JOIN cursos c ON t.curso_id = c.id
        JOIN unidades u ON t.unidade_id = u.id
        WHERE m.usuario_id = ? AND m.status != 'cancelada'
        ORDER BY m.data_matricula DESC
    ");
    $stmt->execute([$_SESSION['usuario_id']]);
    $matriculas = $stmt->fetchAll();
    
    // Buscar planos disponíveis (por enquanto, mostrar todos os planos ativos)
    $stmt = $pdo->query("SELECT * FROM planos WHERE ativo = 1 ORDER BY valor_mensal ASC");
    $planos = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Erro ao buscar dados do usuário: " . $e->getMessage());
    $usuario = [
        'id' => $_SESSION['usuario_id'],
        'nome' => $_SESSION['usuario_nome'] ?? 'Usuário',
        'email' => $_SESSION['usuario_email'] ?? 'Não disponível',
        'data_cadastro' => null,
        'data_atualizacao' => null
    ];
    $matriculas = [];
    $planos = [];
}

// Formatar datas
$data_cadastro_formatada = $usuario['data_cadastro'] 
    ? date('d/m/Y H:i', strtotime($usuario['data_cadastro'])) 
    : 'Não disponível';
    
$data_atualizacao_formatada = $usuario['data_atualizacao'] 
    ? date('d/m/Y H:i', strtotime($usuario['data_atualizacao'])) 
    : 'Não disponível';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/variables.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <title>TechFit - Meu Perfil</title>
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

.user-menu {
  position: relative;
  display: flex;
  align-items: center;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #bbb;
  font-size: 14px;
  cursor: pointer;
  padding: 8px 15px;
  border-radius: 8px;
  transition: all 0.3s ease;
  user-select: none;
}

.user-info:hover {
  background: rgba(255, 68, 68, 0.1);
  color: #fff;
}

.user-info i {
  font-size: 24px;
  color: #ff4444;
}

.dropdown-arrow {
  font-size: 12px !important;
  color: #888 !important;
  transition: transform 0.3s ease;
  margin-left: 5px;
}

.user-info:hover .dropdown-arrow {
  color: #ff4444 !important;
}

.user-dropdown.show .dropdown-arrow {
  transform: rotate(180deg);
}

/* Menu Dropdown */
.user-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 10px;
  background: rgba(25, 25, 25, 0.98);
  border: 2px solid rgba(255, 68, 68, 0.3);
  border-radius: 12px;
  min-width: 200px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(15px);
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.3s ease;
  z-index: 1000;
  overflow: hidden;
}

.user-dropdown.show {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  color: #bbb;
  text-decoration: none;
  font-size: 14px;
  transition: all 0.3s ease;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
}

.dropdown-item:hover {
  background: rgba(255, 68, 68, 0.1);
  color: #fff;
}

.dropdown-item i {
  font-size: 16px;
  color: #ff4444;
  width: 20px;
  text-align: center;
}

.dropdown-item.logout-item {
  color: #bbb;
}

.dropdown-item.logout-item:hover {
  background: rgba(255, 68, 68, 0.1);
  color: #fff;
}

.dropdown-item.logout-item i {
  color: #ff4444;
}

.dropdown-item.active {
  background: rgba(255, 68, 68, 0.15);
  color: #ff4444;
  font-weight: 600;
}

.dropdown-divider {
  height: 1px;
  background: rgba(255, 68, 68, 0.2);
  margin: 5px 0;
}

/* Botão Admin */
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
  margin-right: 15px;
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

/* Estilos específicos para a página de perfil */
.profile-container {
  padding: 40px 30px;
  max-width: 900px;
}

/* Loading State */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  text-align: center;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(255, 68, 68, 0.2);
  border-top-color: #ff4444;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 20px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-state p {
  color: #bbb;
  font-size: 16px;
}

/* Error State */
.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  text-align: center;
  background: rgba(25, 25, 25, 0.95);
  border: 2px solid rgba(255, 68, 68, 0.3);
  border-radius: 20px;
  backdrop-filter: blur(15px);
}

.error-state i {
  font-size: 60px;
  color: #ff6b6b;
  margin-bottom: 20px;
}

.error-state h3 {
  font-size: 24px;
  color: #fff;
  margin-bottom: 10px;
}

.error-state p {
  color: #bbb;
  font-size: 16px;
  margin-bottom: 25px;
}

.btn-retry {
  background: linear-gradient(135deg, #b22222, #ff4444);
  color: #fff;
  border: none;
  padding: 12px 25px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-retry:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 68, 68, 0.4);
}

.profile-header {
  text-align: center;
  margin-bottom: 40px;
  padding: 30px;
  background: rgba(25, 25, 25, 0.95);
  border: 2px solid rgba(255, 68, 68, 0.2);
  border-radius: 20px;
  backdrop-filter: blur(15px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  animation: fadeIn 0.6s ease;
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

.profile-avatar-large {
  width: 150px;
  height: 150px;
  margin: 0 auto 20px;
  border-radius: 50%;
  background: linear-gradient(135deg, #b22222, #ff4444);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 30px rgba(255, 68, 68, 0.4);
  animation: float 3s ease-in-out infinite;
}

.profile-avatar-large i {
  font-size: 100px;
  color: #fff;
}

.profile-header h2 {
  font-size: 32px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 10px;
  letter-spacing: 1px;
}

.profile-email {
  font-size: 16px;
  color: #bbb;
  margin: 0;
}

.profile-content {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.profile-section {
  background: rgba(25, 25, 25, 0.95);
  border: 2px solid rgba(255, 68, 68, 0.2);
  border-radius: 20px;
  padding: 30px;
  backdrop-filter: blur(15px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
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

.profile-section:nth-child(2) {
  animation-delay: 0.1s;
}

.profile-section h3 {
  font-size: 20px;
  font-weight: 600;
  color: #ff4444;
  margin-bottom: 25px;
  display: flex;
  align-items: center;
  gap: 10px;
  letter-spacing: 1px;
}

.profile-section h3 i {
  font-size: 22px;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.info-item {
  padding: 20px;
  background: rgba(40, 40, 40, 0.5);
  border-radius: 12px;
  border: 1px solid rgba(255, 68, 68, 0.2);
  transition: all 0.3s ease;
}

.info-item:hover {
  border-color: rgba(255, 68, 68, 0.4);
  background: rgba(40, 40, 40, 0.7);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 68, 68, 0.2);
}

.info-item label {
  display: block;
  font-size: 12px;
  color: #888;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 8px;
}

.info-item p {
  font-size: 16px;
  color: #fff;
  margin: 0;
  word-break: break-word;
}

.status-active {
  color: #51cf66 !important;
  display: flex;
  align-items: center;
  gap: 8px;
}

.status-active i {
  font-size: 18px;
}

.status-inactive {
  color: #ff6b6b !important;
  display: flex;
  align-items: center;
  gap: 8px;
}

.status-inactive i {
  font-size: 18px;
}

.profile-actions {
  display: flex;
  justify-content: space-between;
  gap: 15px;
  margin-top: 20px;
  padding-top: 30px;
  border-top: 2px solid rgba(255, 68, 68, 0.2);
}

.btn-back,
.btn-edit {
  flex: 1;
  padding: 14px 25px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 15px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  text-decoration: none;
  border: none;
}

.btn-back {
  background: rgba(255, 68, 68, 0.1);
  color: #ff4444;
  border: 2px solid rgba(255, 68, 68, 0.3);
}

.btn-back:hover {
  background: rgba(255, 68, 68, 0.2);
  border-color: rgba(255, 68, 68, 0.5);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 68, 68, 0.3);
}

.btn-edit {
  background: linear-gradient(135deg, #b22222, #ff4444);
  color: #fff;
  border: none;
}

.btn-edit:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(255, 68, 68, 0.4);
}

.btn-edit:active,
.btn-back:active {
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

  .user-menu {
    width: 100%;
    justify-content: flex-end;
  }

  .user-dropdown {
    right: 0;
    left: auto;
    min-width: 180px;
  }

  .container {
    padding: 30px 20px;
  }

  .profile-container {
    padding: 20px;
  }

  .profile-header {
    padding: 20px;
  }

  .profile-avatar-large {
    width: 120px;
    height: 120px;
  }

  .profile-avatar-large i {
    font-size: 80px;
  }

  .profile-header h2 {
    font-size: 24px;
  }

  .profile-section {
    padding: 20px;
  }

  .info-grid {
    grid-template-columns: 1fr;
    gap: 15px;
  }

  .profile-actions {
    flex-direction: column;
  }

  .btn-back,
  .btn-edit {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .logo h1 {
    font-size: 20px;
  }

  .user-info {
    padding: 6px 12px;
    font-size: 12px;
  }

  .user-info i {
    font-size: 20px;
  }

  .dropdown-arrow {
    font-size: 10px !important;
  }

  .user-dropdown {
    min-width: 160px;
  }

  .dropdown-item {
    padding: 10px 15px;
    font-size: 13px;
  }

  .profile-avatar-large {
    width: 100px;
    height: 100px;
  }
}

/* Modal de Edição */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(5px);
  z-index: 10000;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 40px 15px 15px 15px;
  overflow-y: auto;
}

.modal-content-profile {
  background: rgba(25, 25, 25, 0.98);
  border: 2px solid rgba(255, 68, 68, 0.3);
  border-radius: 15px;
  max-width: 480px;
  width: 100%;
  max-height: calc(100vh - 80px);
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8);
  position: relative;
}

.modal-header-profile {
  padding: 18px 20px;
  border-bottom: 2px solid rgba(255, 68, 68, 0.2);
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0;
}

.modal-header-profile h3 {
  color: #fff;
  font-size: 18px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  color: #888;
  font-size: 20px;
  cursor: pointer;
  padding: 4px;
  transition: all 0.3s ease;
  line-height: 1;
}

.modal-close:hover {
  color: #ff4444;
}

.modal-body-profile {
  padding: 20px;
  overflow-y: auto;
  flex: 1;
  min-height: 0;
}

.form-group-profile {
  margin-bottom: 16px;
}

.form-group-profile:last-child {
  margin-bottom: 0;
}

.form-group-profile label {
  display: block;
  color: #bbb;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 6px;
}

.form-group-profile input {
  width: 100%;
  padding: 10px 12px;
  background: rgba(40, 40, 40, 0.9);
  border: 2px solid rgba(255, 68, 68, 0.2);
  border-radius: 8px;
  color: #fff;
  font-size: 14px;
  transition: all 0.3s ease;
  box-sizing: border-box;
}

.form-group-profile input:focus {
  outline: none;
  border-color: #ff4444;
  background: rgba(50, 50, 50, 0.95);
}

.modal-footer-profile {
  padding: 15px 20px;
  border-top: 2px solid rgba(255, 68, 68, 0.2);
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  flex-shrink: 0;
}

.btn-cancel, .btn-save {
  padding: 10px 24px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
}

.btn-cancel {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  border: 2px solid rgba(255, 255, 255, 0.2);
}

.btn-cancel:hover {
  background: rgba(255, 255, 255, 0.2);
}

.btn-save {
  background: linear-gradient(135deg, #8B0000, #DC143C);
  color: #fff;
  border: 2px solid #ff4444;
}

.btn-save:hover {
  background: linear-gradient(135deg, #DC143C, #FF4444);
  transform: translateY(-2px);
}

/* Responsive para modal */
@media (max-width: 768px) {
  .modal-overlay {
    padding: 20px 10px 10px 10px;
    align-items: flex-start;
  }

  .modal-content-profile {
    max-width: 100%;
    max-height: calc(100vh - 40px);
    border-radius: 12px;
  }

  .modal-header-profile {
    padding: 15px;
  }

  .modal-header-profile h3 {
    font-size: 16px;
  }

  .modal-body-profile {
    padding: 15px;
  }

  .modal-footer-profile {
    padding: 12px 15px;
    flex-direction: column-reverse;
  }

  .btn-cancel, .btn-save {
    width: 100%;
    padding: 12px;
  }
}

/* Cursos e Planos */
.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #888;
}

.empty-state i {
  font-size: 48px;
  color: #ff4444;
  margin-bottom: 15px;
}

.empty-state p {
  margin-bottom: 20px;
  font-size: 16px;
}

.btn-primary {
  display: inline-block;
  padding: 12px 30px;
  background: linear-gradient(135deg, #8B0000, #DC143C);
  color: #fff;
  text-decoration: none;
  border-radius: 10px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #DC143C, #FF4444);
  transform: translateY(-2px);
}

.cursos-list, .planos-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.curso-card, .plano-card {
  background: rgba(40, 40, 40, 0.5);
  border: 2px solid rgba(255, 68, 68, 0.2);
  border-radius: 15px;
  padding: 20px;
  transition: all 0.3s ease;
}

.curso-card:hover, .plano-card:hover {
  border-color: rgba(255, 68, 68, 0.5);
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(255, 68, 68, 0.2);
}

.curso-header, .plano-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  padding-bottom: 15px;
  border-bottom: 1px solid rgba(255, 68, 68, 0.2);
}

.curso-header h4, .plano-header h4 {
  color: #fff;
  font-size: 18px;
  font-weight: 700;
  margin: 0;
}

.curso-status {
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.status-pendente {
  background: rgba(255, 193, 7, 0.2);
  color: #ffc107;
}

.status-confirmada {
  background: rgba(40, 167, 69, 0.2);
  color: #28a745;
}

.status-cancelada {
  background: rgba(220, 53, 69, 0.2);
  color: #dc3545;
}

.status-concluida {
  background: rgba(108, 117, 125, 0.2);
  color: #6c757d;
}

.curso-info p, .plano-descricao {
  color: #bbb;
  font-size: 14px;
  margin: 8px 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.curso-info i {
  color: #ff4444;
  width: 20px;
}

.plano-preco {
  color: #ff4444;
  font-size: 24px;
  font-weight: 700;
}

.plano-preco span {
  font-size: 14px;
  color: #888;
  font-weight: 400;
}

.plano-beneficios {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin: 15px 0;
}

.benefit-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 10px;
  background: rgba(255, 68, 68, 0.1);
  border: 1px solid rgba(255, 68, 68, 0.3);
  border-radius: 15px;
  font-size: 11px;
  color: #ff4444;
}

.benefit-badge i {
  font-size: 10px;
}

.curso-actions, .plano-card {
  margin-top: 15px;
}

.btn-view, .btn-plano {
  display: inline-block;
  padding: 8px 20px;
  background: rgba(255, 68, 68, 0.1);
  color: #ff4444;
  text-decoration: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 68, 68, 0.3);
}

.btn-view:hover, .btn-plano:hover {
  background: rgba(255, 68, 68, 0.2);
  border-color: rgba(255, 68, 68, 0.5);
}

/* Estilos para novas seções */
.frequencia-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: var(--spacing-md);
  margin-bottom: var(--spacing-lg);
}

.stat-card {
  background: rgba(255, 68, 68, 0.1);
  border: 2px solid var(--color-primary-alpha-2);
  border-radius: var(--radius-md);
  padding: var(--spacing-lg);
  text-align: center;
}

.stat-card h4 {
  font-size: 2rem;
  font-weight: var(--font-bold);
  color: var(--color-primary);
  margin: 0 0 var(--spacing-xs) 0;
}

.stat-card p {
  color: var(--color-text-muted);
  margin: 0;
  font-size: var(--font-size-sm);
}

.frequencia-list {
  margin-top: var(--spacing-lg);
}

.frequencia-list h4 {
  color: var(--color-text-white);
  margin-bottom: var(--spacing-md);
}

.frequencia-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--spacing-md);
  background: rgba(40, 40, 40, 0.5);
  border-radius: var(--radius-md);
  margin-bottom: var(--spacing-sm);
  border-left: 4px solid;
}

.frequencia-item.presente {
  border-left-color: #28a745;
}

.frequencia-item.ausente {
  border-left-color: #dc3545;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 15px;
  font-size: 11px;
  font-weight: var(--font-bold);
  text-transform: uppercase;
}

.status-badge.presente {
  background: rgba(40, 167, 69, 0.2);
  color: #28a745;
}

.status-badge.ausente {
  background: rgba(220, 53, 69, 0.2);
  color: #dc3545;
}

.avaliacoes-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: var(--spacing-lg);
}

.avaliacao-card {
  background: rgba(40, 40, 40, 0.5);
  border: 2px solid var(--color-primary-alpha-2);
  border-radius: var(--radius-lg);
  padding: var(--spacing-lg);
}

.avaliacao-header {
  border-bottom: 2px solid var(--color-primary-alpha-2);
  padding-bottom: var(--spacing-md);
  margin-bottom: var(--spacing-md);
}

.avaliacao-header h4 {
  color: var(--color-text-white);
  margin: 0 0 var(--spacing-xs) 0;
}

.avaliacao-body {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--spacing-sm);
}

.avaliacao-metric {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.metric-label {
  font-size: var(--font-size-xs);
  color: var(--color-text-muted);
}

.metric-value {
  font-size: var(--font-size-md);
  font-weight: var(--font-bold);
  color: var(--color-primary);
}

/* Footer específico para perfil */
.footer {
  background: rgba(25, 25, 25, 0.95);
  border-top: 2px solid rgba(255, 68, 68, 0.2);
  padding: 30px 0;
  margin-top: 80px;
  text-align: center;
  width: 100%;
  position: relative;
  clear: both;
}

.footer-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 30px;
}

.footer-text {
  color: #888;
  font-size: 14px;
  margin: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  flex-wrap: wrap;
}

.footer-text i {
  color: #ff4444;
  font-size: 16px;
}

.footer-text strong {
  color: #bbb;
  font-weight: 600;
}

@media (max-width: 768px) {
  .footer {
    padding: 20px 0;
    margin-top: 50px;
  }

  .footer-text {
    font-size: 12px;
    flex-direction: column;
    gap: 5px;
  }
}

  .profile-avatar-large i {
    font-size: 70px;
  }

  .profile-header h2 {
    font-size: 20px;
  }

  .profile-section h3 {
    font-size: 18px;
  }

  .info-item p {
    font-size: 14px;
  }
}
  </style>
  <link rel="stylesheet" href="assets/css/notifications.css">
</head>
<body>
  <div class="header">
    <div class="header-content">
      <div class="logo">
        <a href="paginaInicial.php" style="text-decoration: none; display: flex; align-items: center; gap: 15px; color: inherit;">
          <i class="fas fa-dumbbell"></i>
          <h1>TechFit</h1>
        </a>
      </div>
      <div style="display: flex; align-items: center; gap: var(--spacing-md);">
        <?php if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin'): ?>
        <a href="admin/index.php" class="admin-button">
          <i class="fas fa-cog"></i>
          <span>Painel Admin</span>
        </a>
        <?php endif; ?>
        <div class="user-menu">
          <div class="user-info" onclick="toggleUserMenu()">
            <i class="fas fa-user-circle"></i>
            <span id="userName"><?php echo htmlspecialchars($usuario['nome']); ?></span>
            <i class="fas fa-chevron-down dropdown-arrow"></i>
          </div>
          <div class="user-dropdown" id="userDropdown">
          <a href="perfil.php" class="dropdown-item active">
            <i class="fas fa-user"></i>
            <span>Meu Perfil</span>
          </a>
          <a href="suporte.php" class="dropdown-item">
            <i class="fas fa-headset"></i>
            <span>Suporte</span>
          </a>
          <?php if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin'): ?>
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

  <div class="container profile-container">
    <div class="profile-header">
      <div class="profile-avatar-large">
        <i class="fas fa-user-circle"></i>
      </div>
      <h2><?php echo htmlspecialchars($usuario['nome']); ?></h2>
      <p class="profile-email"><?php echo htmlspecialchars($usuario['email']); ?></p>
    </div>

    <div class="profile-content">
      <div class="profile-section">
        <h3><i class="fas fa-info-circle"></i> Informações Pessoais</h3>
        <div class="info-grid">
          <div class="info-item">
            <label>Nome Completo</label>
            <p><?php echo htmlspecialchars($usuario['nome']); ?></p>
          </div>
          <div class="info-item">
            <label>E-mail</label>
            <p><?php echo htmlspecialchars($usuario['email']); ?></p>
          </div>
          <div class="info-item">
            <label>ID do Usuário</label>
            <p>#<?php echo htmlspecialchars($usuario['id']); ?></p>
          </div>
        </div>
      </div>

      <div class="profile-section">
        <h3><i class="fas fa-calendar-alt"></i> Informações da Conta</h3>
        <div class="info-grid">
          <div class="info-item">
            <label>Data de Cadastro</label>
            <p><?php echo $data_cadastro_formatada; ?></p>
          </div>
          <div class="info-item">
            <label>Última Atualização</label>
            <p><?php echo $data_atualizacao_formatada; ?></p>
          </div>
          <div class="info-item">
            <label>Status da Conta</label>
            <p class="status-active"><i class="fas fa-check-circle"></i> Ativa</p>
          </div>
        </div>
      </div>

      <div class="profile-section">
        <h3><i class="fas fa-graduation-cap"></i> Cursos e Turmas Matriculadas</h3>
        <div id="cursosMatriculados">
          <?php if (empty($matriculas)): ?>
            <div class="empty-state">
              <i class="fas fa-book-open"></i>
              <p>Você ainda não está matriculado em nenhum curso.</p>
              <a href="turmas_disponiveis.php" class="btn-primary">Ver Turmas Disponíveis</a>
            </div>
          <?php else: ?>
            <div class="cursos-list">
              <?php foreach ($matriculas as $matricula): ?>
                <div class="curso-card">
                  <div class="curso-header">
                    <h4><?php echo htmlspecialchars($matricula['curso_nome']); ?></h4>
                    <span class="curso-status status-<?php echo $matricula['status']; ?>">
                      <?php 
                      $status_labels = [
                        'pendente' => 'Pendente',
                        'confirmada' => 'Confirmada',
                        'cancelada' => 'Cancelada',
                        'concluida' => 'Concluída'
                      ];
                      echo $status_labels[$matricula['status']] ?? $matricula['status'];
                      ?>
                    </span>
                  </div>
                  <div class="curso-info">
                    <p><i class="fas fa-chalkboard-teacher"></i> <strong>Turma:</strong> <?php echo htmlspecialchars($matricula['nome_turma']); ?></p>
                    <p><i class="fas fa-building"></i> <strong>Unidade:</strong> <?php echo htmlspecialchars($matricula['unidade_nome']); ?> - <?php echo htmlspecialchars($matricula['cidade']); ?></p>
                    <p><i class="fas fa-tag"></i> <strong>Categoria:</strong> <?php echo htmlspecialchars($matricula['categoria']); ?></p>
                    <p><i class="fas fa-clock"></i> <strong>Duração:</strong> <?php echo htmlspecialchars($matricula['duracao']); ?> horas</p>
                    <p><i class="fas fa-calendar"></i> <strong>Data de Matrícula:</strong> <?php echo date('d/m/Y H:i', strtotime($matricula['data_matricula'])); ?></p>
                  </div>
                  <div class="curso-actions">
                    <a href="minhas_matriculas.php" class="btn-view">Ver Detalhes</a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="profile-section">
        <h3><i class="fas fa-clock"></i> Lista de Espera</h3>
        <div id="listaEspera">
          <div class="text-center">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Carregando...</p>
          </div>
        </div>
      </div>

      <div class="profile-section">
        <h3><i class="fas fa-check-circle"></i> Minha Frequência</h3>
        <div id="minhaFrequencia">
          <div class="text-center">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Carregando...</p>
          </div>
        </div>
      </div>

      <div class="profile-section">
        <h3><i class="fas fa-heartbeat"></i> Avaliações Físicas</h3>
        <div id="avaliacoesFisicas">
          <div class="text-center">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Carregando...</p>
          </div>
        </div>
      </div>

      <div class="profile-section">
        <h3><i class="fas fa-tags"></i> Planos Disponíveis</h3>
        <div id="planosDisponiveis">
          <?php if (empty($planos)): ?>
            <div class="empty-state">
              <i class="fas fa-tags"></i>
              <p>Nenhum plano disponível no momento.</p>
            </div>
          <?php else: ?>
            <div class="planos-list">
              <?php foreach ($planos as $plano): ?>
                <div class="plano-card">
                  <div class="plano-header">
                    <h4><?php echo htmlspecialchars($plano['nome']); ?></h4>
                    <div class="plano-preco">R$ <?php echo number_format($plano['valor_mensal'], 2, ',', '.'); ?><span>/mês</span></div>
                  </div>
                  <p class="plano-descricao"><?php echo htmlspecialchars($plano['descricao']); ?></p>
                  <div class="plano-beneficios">
                    <?php if ($plano['acesso_academia']): ?><span class="benefit-badge"><i class="fas fa-check"></i> Academia</span><?php endif; ?>
                    <?php if ($plano['acesso_musculacao']): ?><span class="benefit-badge"><i class="fas fa-check"></i> Musculação</span><?php endif; ?>
                    <?php if ($plano['acesso_todas_unidades']): ?><span class="benefit-badge"><i class="fas fa-check"></i> Todas Unidades</span><?php endif; ?>
                    <?php if ($plano['acesso_todos_cursos']): ?><span class="benefit-badge"><i class="fas fa-check"></i> Todos Cursos</span><?php endif; ?>
                    <?php if ($plano['quantidade_cursos'] > 0): ?><span class="benefit-badge"><i class="fas fa-check"></i> <?php echo $plano['quantidade_cursos']; ?> Cursos</span><?php endif; ?>
                    <?php if ($plano['aulas_grupais_ilimitadas']): ?><span class="benefit-badge"><i class="fas fa-check"></i> Aulas Ilimitadas</span><?php endif; ?>
                    <?php if ($plano['personal_trainer']): ?><span class="benefit-badge"><i class="fas fa-check"></i> Personal Trainer</span><?php endif; ?>
                    <?php if ($plano['nutricionista']): ?><span class="benefit-badge"><i class="fas fa-check"></i> Nutricionista</span><?php endif; ?>
                    <?php if ($plano['avaliacao_fisica']): ?><span class="benefit-badge"><i class="fas fa-check"></i> Avaliação Física</span><?php endif; ?>
                    <?php if ($plano['app_exclusivo']): ?><span class="benefit-badge"><i class="fas fa-check"></i> App Exclusivo</span><?php endif; ?>
                    <?php if ($plano['desconto_loja']): ?><span class="benefit-badge"><i class="fas fa-check"></i> Desconto Loja</span><?php endif; ?>
                  </div>
                  <a href="planos.php" class="btn-plano">Ver Detalhes</a>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="profile-actions">
        <a href="paginaInicial.php" class="btn-back">
          <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <button class="btn-edit" onclick="abrirModalEditarPerfil()">
          <i class="fas fa-edit"></i> Editar Perfil
        </button>
      </div>
    </div>
  </div>

  <!-- Modal de Edição de Perfil -->
  <div id="modalEditarPerfil" class="modal-overlay" style="display: none;">
    <div class="modal-content-profile">
      <div class="modal-header-profile">
        <h3><i class="fas fa-user-edit"></i> Editar Perfil</h3>
        <button class="modal-close" onclick="fecharModalEditarPerfil()">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="modal-body-profile">
        <form id="formEditarPerfil">
          <div class="form-group-profile">
            <label for="editNome">Nome Completo</label>
            <input type="text" id="editNome" name="nome" required>
          </div>
          <div class="form-group-profile">
            <label for="editEmail">E-mail</label>
            <input type="email" id="editEmail" name="email" required>
          </div>
          <div class="form-group-profile">
            <label for="editSenhaAtual">Senha Atual (deixe em branco se não quiser alterar)</label>
            <input type="password" id="editSenhaAtual" name="senha_atual" placeholder="Digite sua senha atual">
          </div>
          <div class="form-group-profile">
            <label for="editSenhaNova">Nova Senha (deixe em branco se não quiser alterar)</label>
            <input type="password" id="editSenhaNova" name="senha_nova" placeholder="Digite a nova senha (mín. 8 caracteres)">
          </div>
        </form>
      </div>
      <div class="modal-footer-profile">
        <button class="btn-cancel" onclick="fecharModalEditarPerfil()">Cancelar</button>
        <button class="btn-save" onclick="salvarPerfil()">Salvar Alterações</button>
      </div>
    </div>
  </div>

  <script src="assets/js/notifications.js"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script>
    // Toggle do menu dropdown
    function toggleUserMenu() {
      const dropdown = document.getElementById('userDropdown');
      dropdown.classList.toggle('show');
    }

    // Fechar menu ao clicar fora
    window.addEventListener('click', function(event) {
      const userMenu = document.querySelector('.user-menu');
      const dropdown = document.getElementById('userDropdown');
      if (!userMenu.contains(event.target)) {
        dropdown.classList.remove('show');
      }
    });

    async function logout() {
      const confirmed = await showConfirm('Tem certeza que deseja sair?', 'Confirmar Saída', 'warning');
      if (confirmed) {
        window.location.href = 'logout.php';
      }
    }

    // Funções do modal de edição
    function abrirModalEditarPerfil() {
      // Carregar dados atuais
      $.ajax({
        url: 'perfil_api.php?action=get',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            $('#editNome').val(response.data.nome);
            $('#editEmail').val(response.data.email);
            $('#editSenhaAtual').val('');
            $('#editSenhaNova').val('');
            $('#modalEditarPerfil').css('display', 'flex').hide().fadeIn(300);
            // Scroll para o topo do modal
            $('html, body').animate({
              scrollTop: 0
            }, 300);
          } else {
            showNotification('Erro ao carregar dados do perfil', 'error');
          }
        },
        error: function() {
          showNotification('Erro ao carregar dados do perfil', 'error');
        }
      });
    }

    function fecharModalEditarPerfil() {
      $('#modalEditarPerfil').fadeOut(300);
    }

    function salvarPerfil() {
      const nome = $('#editNome').val().trim();
      const email = $('#editEmail').val().trim();
      const senhaAtual = $('#editSenhaAtual').val();
      const senhaNova = $('#editSenhaNova').val();

      if (!nome || !email) {
        showNotification('Nome e e-mail são obrigatórios', 'error');
        return;
      }

      if (senhaNova && senhaNova.length < 8) {
        showNotification('A nova senha deve ter pelo menos 8 caracteres', 'error');
        return;
      }

      if (senhaNova && !senhaAtual) {
        showNotification('Para alterar a senha, é necessário informar a senha atual', 'error');
        return;
      }

      const formData = {
        action: 'update',
        nome: nome,
        email: email,
        senha_atual: senhaAtual,
        senha_nova: senhaNova
      };

      $.ajax({
        url: 'perfil_api.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            showNotification(response.message, 'success');
            fecharModalEditarPerfil();
            setTimeout(() => {
              location.reload();
            }, 1500);
          } else {
            showNotification(response.message, 'error');
          }
        },
        error: function(xhr) {
          const response = xhr.responseJSON;
          showNotification(response ? response.message : 'Erro ao atualizar perfil', 'error');
        }
      });
    }

    // Fechar modal ao clicar fora
    $('#modalEditarPerfil').on('click', function(e) {
      if (e.target === this) {
        fecharModalEditarPerfil();
      }
    });

    // Carregar lista de espera
    function carregarListaEspera() {
      $.ajax({
        url: 'matriculas_api.php?action=lista_espera',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            if (response.data.length === 0) {
              $('#listaEspera').html(`
                <div class="empty-state">
                  <i class="fas fa-clock"></i>
                  <p>Você não está em nenhuma lista de espera.</p>
                </div>
              `);
            } else {
              let html = '<div class="cursos-list">';
              response.data.forEach(function(item) {
                html += `
                  <div class="curso-card">
                    <div class="curso-header">
                      <h4>${item.curso_nome}</h4>
                      <span class="curso-status status-pendente">Lista de Espera - Posição ${item.prioridade}</span>
                    </div>
                    <div class="curso-info">
                      <p><i class="fas fa-chalkboard-teacher"></i> ${item.nome_turma}</p>
                      <p><i class="fas fa-map-marker-alt"></i> ${item.unidade_nome}</p>
                      <p><i class="fas fa-calendar"></i> ${new Date(item.data_inscricao).toLocaleDateString('pt-BR')}</p>
                    </div>
                  </div>
                `;
              });
              html += '</div>';
              $('#listaEspera').html(html);
            }
          }
        },
        error: function() {
          $('#listaEspera').html('<div class="empty-state"><p>Erro ao carregar lista de espera</p></div>');
        }
      });
    }

    // Carregar frequência
    function carregarFrequencia() {
      $.ajax({
        url: 'frequencia_api.php?action=minha_frequencia',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            if (response.data.length === 0) {
              $('#minhaFrequencia').html(`
                <div class="empty-state">
                  <i class="fas fa-check-circle"></i>
                  <p>Nenhuma frequência registrada ainda.</p>
                </div>
              `);
            } else {
              let html = '<div class="frequencia-stats">';
              let presentes = response.data.filter(f => f.status === 'presente').length;
              let total = response.data.length;
              let percentual = total > 0 ? Math.round((presentes / total) * 100) : 0;
              
              html += `
                <div class="stat-card">
                  <h4>${percentual}%</h4>
                  <p>Taxa de Presença</p>
                </div>
                <div class="stat-card">
                  <h4>${presentes}</h4>
                  <p>Presenças</p>
                </div>
                <div class="stat-card">
                  <h4>${total - presentes}</h4>
                  <p>Ausências</p>
                </div>
              </div>
              <div class="frequencia-list">
                <h4>Últimas Aulas</h4>
              `;
              
              response.data.slice(0, 10).forEach(function(freq) {
                const statusClass = freq.status === 'presente' ? 'presente' : 'ausente';
                const statusLabel = freq.status === 'presente' ? 'Presente' : freq.status === 'ausente' ? 'Ausente' : 'Justificado';
                html += `
                  <div class="frequencia-item ${statusClass}">
                    <div>
                      <strong>${freq.curso_nome}</strong>
                      <p>${freq.nome_turma} - ${new Date(freq.data_aula).toLocaleDateString('pt-BR')}</p>
                    </div>
                    <span class="status-badge ${statusClass}">${statusLabel}</span>
                  </div>
                `;
              });
              html += '</div>';
              $('#minhaFrequencia').html(html);
            }
          }
        },
        error: function() {
          $('#minhaFrequencia').html('<div class="empty-state"><p>Erro ao carregar frequência</p></div>');
        }
      });
    }

    // Carregar avaliações físicas
    function carregarAvaliacoes() {
      $.ajax({
        url: 'avaliacoes_api.php?action=minhas_avaliacoes',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            if (response.data.length === 0) {
              $('#avaliacoesFisicas').html(`
                <div class="empty-state">
                  <i class="fas fa-heartbeat"></i>
                  <p>Nenhuma avaliação física registrada ainda.</p>
                </div>
              `);
            } else {
              // Ordenar avaliações por data
              const avaliacoesOrdenadas = response.data.sort((a, b) => 
                new Date(a.data_avaliacao) - new Date(b.data_avaliacao)
              );
              
              let html = '';
              
              // Adicionar gráfico de evolução se houver mais de uma avaliação
              if (avaliacoesOrdenadas.length > 1) {
                html += `
                  <div style="background: rgba(255,255,255,0.05); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                    <h4 style="margin-bottom: 15px;"><i class="fas fa-chart-line"></i> Evolução</h4>
                    <div style="position: relative; height: 300px;">
                      <canvas id="graficoEvolucao"></canvas>
                    </div>
                  </div>
                `;
              }
              
              html += '<div class="avaliacoes-list">';
              avaliacoesOrdenadas.forEach(function(av) {
                const imc = (av.imc && !isNaN(parseFloat(av.imc))) ? parseFloat(av.imc).toFixed(1) : 'N/A';
                html += `
                  <div class="avaliacao-card">
                    <div class="avaliacao-header">
                      <h4>Avaliação de ${new Date(av.data_avaliacao).toLocaleDateString('pt-BR')}</h4>
                      ${av.avaliador_nome ? `<p><i class="fas fa-user-md"></i> ${av.avaliador_nome}</p>` : ''}
                    </div>
                    <div class="avaliacao-body">
                      <div class="avaliacao-metric">
                        <span class="metric-label">IMC:</span>
                        <span class="metric-value">${imc}</span>
                      </div>
                      ${av.peso ? `<div class="avaliacao-metric"><span class="metric-label">Peso:</span><span class="metric-value">${av.peso} kg</span></div>` : ''}
                      ${av.altura ? `<div class="avaliacao-metric"><span class="metric-label">Altura:</span><span class="metric-value">${av.altura} m</span></div>` : ''}
                      ${av.percentual_gordura ? `<div class="avaliacao-metric"><span class="metric-label">% Gordura:</span><span class="metric-value">${av.percentual_gordura}%</span></div>` : ''}
                      ${av.proxima_avaliacao ? `<div class="avaliacao-metric"><span class="metric-label">Próxima Avaliação:</span><span class="metric-value">${new Date(av.proxima_avaliacao).toLocaleDateString('pt-BR')}</span></div>` : ''}
                    </div>
                  </div>
                `;
              });
              html += '</div>';
              $('#avaliacoesFisicas').html(html);
              
              // Renderizar gráfico se houver mais de uma avaliação
              if (avaliacoesOrdenadas.length > 1) {
                renderizarGraficoEvolucao(avaliacoesOrdenadas);
              }
            }
          }
        },
        error: function() {
          $('#avaliacoesFisicas').html('<div class="empty-state"><p>Erro ao carregar avaliações</p></div>');
        }
      });
    }

    // Renderizar gráfico de evolução
    function renderizarGraficoEvolucao(avaliacoes) {
      const ctx = document.getElementById('graficoEvolucao');
      if (!ctx) return;
      
      const labels = avaliacoes.map(av => 
        new Date(av.data_avaliacao).toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' })
      );
      
      const dadosPeso = avaliacoes.map(av => (av.peso && !isNaN(parseFloat(av.peso))) ? parseFloat(av.peso) : null);
      const dadosIMC = avaliacoes.map(av => (av.imc && !isNaN(parseFloat(av.imc))) ? parseFloat(av.imc) : null);
      const dadosGordura = avaliacoes.map(av => (av.percentual_gordura && !isNaN(parseFloat(av.percentual_gordura))) ? parseFloat(av.percentual_gordura) : null);
      
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [
            dadosPeso.some(p => p !== null) ? {
              label: 'Peso (kg)',
              data: dadosPeso,
              borderColor: 'rgba(54, 162, 235, 1)',
              backgroundColor: 'rgba(54, 162, 235, 0.1)',
              tension: 0.4,
              yAxisID: 'y'
            } : null,
            dadosIMC.some(i => i !== null) ? {
              label: 'IMC',
              data: dadosIMC,
              borderColor: 'rgba(255, 99, 132, 1)',
              backgroundColor: 'rgba(255, 99, 132, 0.1)',
              tension: 0.4,
              yAxisID: 'y1'
            } : null,
            dadosGordura.some(g => g !== null) ? {
              label: '% Gordura',
              data: dadosGordura,
              borderColor: 'rgba(255, 206, 86, 1)',
              backgroundColor: 'rgba(255, 206, 86, 0.1)',
              tension: 0.4,
              yAxisID: 'y2'
            } : null
          ].filter(d => d !== null)
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              position: 'top',
              labels: {
                color: '#fff'
              }
            }
          },
          scales: {
            y: {
              type: 'linear',
              display: true,
              position: 'left',
              ticks: {
                color: '#fff'
              },
              grid: {
                color: 'rgba(255, 255, 255, 0.1)'
              }
            },
            y1: {
              type: 'linear',
              display: false,
              position: 'right',
              grid: {
                drawOnChartArea: false
              }
            },
            y2: {
              type: 'linear',
              display: false,
              position: 'right',
              grid: {
                drawOnChartArea: false
              }
            },
            x: {
              ticks: {
                color: '#fff'
              },
              grid: {
                color: 'rgba(255, 255, 255, 0.1)'
              }
            }
          }
        }
      });
    }

    // Carregar tudo ao iniciar
    $(document).ready(function() {
      carregarListaEspera();
      carregarFrequencia();
      carregarAvaliacoes();
    });
  </script>

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

