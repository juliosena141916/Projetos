<?php
session_start();

// Prevenir cache da página
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once 'includes/conexao.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit;
}

try {
    $pdo = getConexao();
    
    // Buscar cursos para filtro
    $stmt = $pdo->query("SELECT id, nome FROM cursos WHERE ativo = 1 ORDER BY nome");
    $cursos = $stmt->fetchAll();
    
    // Buscar categorias para filtro
    $stmt = $pdo->query("SELECT DISTINCT categoria FROM cursos WHERE ativo = 1 ORDER BY categoria");
    $categorias = $stmt->fetchAll();
    
    // Buscar unidades para filtro
    $stmt = $pdo->query("SELECT DISTINCT cidade FROM unidades WHERE ativo = 1 ORDER BY cidade");
    $cidades = $stmt->fetchAll();
    
    // Verificar se o usuário tem matrículas ativas
    $tem_matriculas = false;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM matriculas 
        WHERE usuario_id = ? AND status != 'cancelada'
    ");
    $stmt->execute([$_SESSION['usuario_id']]);
    $result = $stmt->fetch();
    $tem_matriculas = ($result && $result['total'] > 0);
    
} catch (Exception $e) {
    error_log("Erro ao buscar dados: " . $e->getMessage());
    $tem_matriculas = false;
    $cursos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Turmas Disponíveis - TechFit</title>
    <link rel="icon" href="data:,">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/pages/turmas_disponiveis.css">
    <link rel="stylesheet" href="assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <a href="paginaInicial.php" class="logo">
                <i class="fas fa-dumbbell"></i>
                <h1>TechFit</h1>
            </a>
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
                    <span id="userName"><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário'); ?></span>
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
    </div>

    <!-- Main Content -->
    <div class="container container-main" style="max-width: 1400px; margin: 0 auto;">
        <div class="page-header">
            <h1><i class="fas fa-calendar-alt"></i> Turmas Disponíveis</h1>
            <p class="text-muted mb-0">Encontre a turma perfeita para você e inscreva-se!</p>
        </div>

        <!-- Filtros -->
        <div class="filters-card">
            <h5 class="mb-3"><i class="fas fa-filter"></i> Filtrar Turmas</h5>
            <div class="row">
                <div class="col-md-3">
                    <label>Curso</label>
                    <select class="form-control" id="filtroCurso">
                        <option value="">Todos os cursos</option>
                        <?php foreach ($cursos as $curso): ?>
                            <option value="<?= $curso['id'] ?>">
                                <?= htmlspecialchars($curso['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Categoria</label>
                    <select class="form-control" id="filtroCategoria">
                        <option value="">Todas</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['categoria']) ?>">
                                <?= htmlspecialchars($cat['categoria']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Cidade</label>
                    <select class="form-control" id="filtroCidade">
                        <option value="">Todas</option>
                        <?php foreach ($cidades as $cidade): ?>
                            <option value="<?= htmlspecialchars($cidade['cidade']) ?>">
                                <?= htmlspecialchars($cidade['cidade']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Buscar</label>
                    <input type="text" class="form-control" id="filtroBusca" placeholder="Nome da turma...">
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <button class="btn btn-primary btn-block" onclick="carregarTurmas()">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </div>
        </div>

        <!-- Lista de Turmas -->
        <div id="turmasList">
            <div class="text-center py-5">
                <div class="spinner-border text-light" role="status">
                    <span class="sr-only">Carregando...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalhes da Turma -->
    <div class="modal fade" id="modalDetalhes" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalhes da Turma</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalDetalhesContent">
                    <!-- Conteúdo carregado dinamicamente -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="assets/js/notifications.js"></script>
    <script src="assets/js/utils.js"></script>
    <script>
        let turmas = [];

        $(document).ready(function() {
            // Verificar se há curso_id na URL para auto-selecionar
            const urlParams = new URLSearchParams(window.location.search);
            const cursoIdParam = urlParams.get('curso_id');
            
            if (cursoIdParam) {
                $('#filtroCurso').val(cursoIdParam);
            }
            
            carregarTurmas();
        });

        function carregarTurmas() {
            const params = new URLSearchParams();
            
            const cursoId = $('#filtroCurso').val();
            const categoria = $('#filtroCategoria').val();
            const cidade = $('#filtroCidade').val();
            
            if (cursoId) params.append('curso_id', cursoId);
            if (categoria) params.append('categoria', categoria);
            if (cidade) params.append('cidade', cidade);

            // Adicionar timestamp para evitar cache
            params.append('_t', Date.now());
            
            $.get('turmas_disponiveis_api.php?' + params.toString(), function(response) {
                if (response.success) {
                    turmas = response.turmas;
                    renderizarTurmas();
                } else {
                    toastr.error(response.message);
                }
            }).fail(function() {
                toastr.error('Erro ao carregar turmas');
            });
        }

        function renderizarTurmas() {
            const busca = $('#filtroBusca').val().toLowerCase();
            
            let turmasFiltradas = turmas.filter(turma => {
                return !busca || 
                       turma.curso_nome.toLowerCase().includes(busca) ||
                       turma.nome_turma.toLowerCase().includes(busca);
            });

            if (turmasFiltradas.length === 0) {
                $('#turmasList').html(`
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h4>Nenhuma turma encontrada</h4>
                        <p class="text-muted">Tente ajustar os filtros ou volte mais tarde.</p>
                    </div>
                `);
                return;
            }

            let html = '';
            turmasFiltradas.forEach(turma => {
                const diasSemana = turma.dias_semana ? formatarDiasSemana(turma.dias_semana) : 'Não definido';
                const horario = turma.hora_inicio && turma.hora_fim ? 
                    `${turma.hora_inicio.substring(0,5)} - ${turma.hora_fim.substring(0,5)}` : 
                    'Não definido';
                
                const vagasClass = turma.vagas_disponiveis < 5 ? 'badge-danger' : 'badge-success';
                const jaMatriculado = turma.ja_matriculado;

                html += `
                    <div class="turma-card">
                        <div class="turma-header text-center">
                            <h3 class="turma-title">${turma.nome_turma}</h3>
                            <p class="turma-curso">${turma.curso_nome} - ${turma.categoria}</p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="turma-details-grid">
                                <div class="info-item">
                                    <i class="fas fa-building"></i>
                                        <div class="info-content">
                                            <span class="info-label">Unidade</span>
                                            <span class="info-value">${turma.unidade_nome} - ${turma.cidade}</span>
                                        </div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-user"></i>
                                        <div class="info-content">
                                            <span class="info-label">Instrutor</span>
                                            <span class="info-value">${turma.instrutor || 'A definir'}</span>
                                        </div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-calendar-week"></i>
                                        <div class="info-content">
                                            <span class="info-label">Dias da Semana</span>
                                            <span class="info-value">${diasSemana}</span>
                                        </div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-clock"></i>
                                        <div class="info-content">
                                            <span class="info-label">Horário</span>
                                            <span class="info-value">${horario}</span>
                                        </div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-calendar"></i>
                                        <div class="info-content">
                                            <span class="info-label">Período</span>
                                            <span class="info-value">${formatarData(turma.data_inicio)} a ${formatarData(turma.data_fim)}</span>
                                        </div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                        <div class="info-content">
                                            <span class="info-label">Total de Aulas</span>
                                            <span class="info-value">${turma.total_aulas || 0} aulas</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="turma-actions">
                                    <div class="mb-3 text-center">
                                    <span class="badge badge-custom ${vagasClass}">
                                        ${turma.vagas_disponiveis} vagas disponíveis
                                    </span>
                                </div>
                                    <div class="mb-4 text-center">
                                        <div class="price-label">Valor Total</div>
                                        <h4 class="text-primary price-value">R$ ${parseFloat(turma.valor_total).toFixed(2)}</h4>
                                </div>
                                ${jaMatriculado ? 
                                    `<div class="inscrito-badge">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Inscrito</span>
                                    </div>
                                    <div class="inscrito-info">
                                        <p>Você já está inscrito nesta turma!</p>
                                        <a href="minhas_matriculas.php"><i class="fas fa-arrow-right"></i> Ver Minhas Turmas</a>
                                    </div>` :
                                    `<button class="btn btn-inscrever btn-block" onclick="inscreverTurma(${turma.id})"><i class="fas fa-user-plus"></i> Inscrever-se</button>`
                                }
                                <button class="btn btn-outline-info btn-block mt-2" onclick="verDetalhes(${turma.id})">
                                    <i class="fas fa-info-circle"></i> Ver Detalhes
                                </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            $('#turmasList').html(html);
        }

        function formatarDiasSemana(diasStr) {
            if (!diasStr) return 'Não definido';
            const dias = diasStr.split(',');
            const nomes = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
            return dias.map(d => nomes[parseInt(d)]).join(', ');
        }

        function formatarData(data) {
            if (!data) return '';
            const d = new Date(data + 'T00:00:00');
            return d.toLocaleDateString('pt-BR');
        }

        async function inscreverTurma(turmaId) {
            const confirmed = await showConfirm('Deseja realmente se inscrever nesta turma?', 'Confirmar Inscrição', 'info');
            if (!confirmed) {
                return;
            }

            $.ajax({
                url: 'matriculas_api.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    turma_id: turmaId,
                    forma_pagamento: 'a_definir'
                }),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(() => {
                            window.location.href = 'minhas_matriculas.php';
                        }, 1500);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    toastr.error(response ? response.message : 'Erro ao realizar inscrição');
                }
            });
        }

        function verDetalhes(turmaId) {
            $.get('aulas_turma_api.php?turma_id=' + turmaId, function(response) {
                if (response.success) {
                    const turma = response.turma;
                    const aulas = response.aulas;
                    
                    let html = `
                        <div class="modal-details-container">
                            <div class="modal-header-section text-center">
                                <h4 class="modal-turma-title">${turma.nome_turma}</h4>
                                <p class="modal-turma-curso">${turma.curso_nome}</p>
                            </div>
                            
                            <div class="modal-info-section">
                                <div class="modal-info-item">
                                    <i class="fas fa-info-circle"></i>
                                    <div class="modal-info-content">
                                        <span class="modal-info-label">Descrição do Curso</span>
                                        <span class="modal-info-value">${turma.curso_descricao || 'Sem descrição disponível'}</span>
                                    </div>
                                </div>
                                
                                <div class="modal-info-item">
                                    <i class="fas fa-building"></i>
                                    <div class="modal-info-content">
                                        <span class="modal-info-label">Unidade</span>
                                        <span class="modal-info-value">${turma.unidade_nome} - ${turma.cidade}</span>
                                    </div>
                                </div>
                                
                                <div class="modal-info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <div class="modal-info-content">
                                        <span class="modal-info-label">Endereço</span>
                                        <span class="modal-info-value">${turma.endereco || 'Não informado'}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="modal-aulas-section">
                                <h5 class="modal-section-title text-center">
                                    <i class="fas fa-calendar-alt"></i> Calendário de Aulas
                                    <span class="badge badge-primary ml-2">${aulas.length} aula${aulas.length !== 1 ? 's' : ''}</span>
                                </h5>
                        <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                                <th class="text-center">Data</th>
                                                <th class="text-center">Horário</th>
                                                <th class="text-center">Sala</th>
                                                <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    aulas.forEach(aula => {
                        const statusBadge = {
                            'agendada': '<span class="badge badge-success">Agendada</span>',
                            'realizada': '<span class="badge badge-secondary">Realizada</span>',
                            'cancelada': '<span class="badge badge-danger">Cancelada</span>',
                            'remarcada': '<span class="badge badge-warning">Remarcada</span>'
                        }[aula.status] || aula.status;

                        const horaInicio = aula.hora_inicio ? aula.hora_inicio.substring(0,5) : '-';
                        const horaFim = aula.hora_fim ? aula.hora_fim.substring(0,5) : '-';

                        html += `
                            <tr>
                                <td class="text-center">${formatarData(aula.data_aula)}</td>
                                <td class="text-center">${horaInicio} - ${horaFim}</td>
                                <td class="text-center">${aula.sala || '-'}</td>
                                <td class="text-center">${statusBadge}</td>
                            </tr>
                        `;
                    });
                    
                    html += `
                                </tbody>
                            </table>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    $('#modalDetalhesContent').html(html);
                    $('#modalDetalhes').modal('show');
                }
            });
        }

        // Filtro em tempo real
        $('#filtroBusca').on('keyup', function() {
            renderizarTurmas();
        });

        // Configurar toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };
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
