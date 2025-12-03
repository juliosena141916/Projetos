<?php
require_once 'check_admin.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Planos - TechFit</title>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            padding-top: 20px;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            overflow-y: auto;
        }
        .sidebar h4 {
            color: #fff;
            text-align: center;
            margin-bottom: 30px;
        }
        .sidebar a {
            color: #adb5bd;
            padding: 10px 15px;
            text-decoration: none;
            display: block;
            transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #495057;
            color: #fff;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        .page-header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #007bff;
            color: white;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .btn-sm {
            margin: 2px;
        }
        .benefit-badge {
            display: inline-block;
            padding: 2px 8px;
            margin: 2px;
            background-color: #28a745;
            color: white;
            border-radius: 3px;
            font-size: 11px;
        }
        .benefit-badge.no {
            background-color: #6c757d;
        }
    </style>
    <link rel="stylesheet" href="../assets/css/notifications.css">
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <h4>Admin TechFit</h4>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="usuarios.php">
                    <i class="fas fa-users"></i> Gerenciar Usuários
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="unidades.php">
                    <i class="fas fa-building"></i> Gerenciar Unidades
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="cursos.php">
                    <i class="fas fa-graduation-cap"></i> Gerenciar Cursos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="planos.php">
                    <i class="fas fa-tags"></i> Gerenciar Planos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="turmas.php">
                    <i class="fas fa-calendar-alt"></i> Gerenciar Turmas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="agendamento_aulas.php">
                    <i class="fas fa-calendar-day"></i> Agendar Aulas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="frequencia.php">
                    <i class="fas fa-check-circle"></i> Gerenciar Frequência
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="avaliacoes.php">
                    <i class="fas fa-heartbeat"></i> Avaliações Físicas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="suporte.php">
                    <i class="fas fa-headset"></i> Mensagens de Suporte
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../paginaInicial.php">
                    <i class="fas fa-globe"></i> Ver Site
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="content">
        <div class="page-header">
            <h1 class="h2 mb-0">Gerenciar Planos</h1>
            <p class="text-muted mb-0">Adicione, edite e gerencie os planos de assinatura oferecidos.</p>
            <button class="btn btn-primary mt-2" data-toggle="modal" data-target="#modalNovoPlano">
                <i class="fas fa-plus"></i> Novo Plano
            </button>
        </div>

        <!-- Mensagens de alerta -->
        <div id="alertContainer"></div>

        <!-- Tabela de planos -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Valor Mensal (R$)</th>
                        <th>Benefícios</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="planosTable">
                    <tr>
                        <td colspan="6" class="text-center">Carregando...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal para novo plano -->
    <div class="modal fade" id="modalNovoPlano" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Novo Plano</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formNovoPlano">
                        <div class="form-group">
                            <label for="nome">Nome do Plano:</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="descricao">Descrição:</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="valor_mensal">Valor Mensal (R$):</label>
                            <input type="number" class="form-control" id="valor_mensal" name="valor_mensal" step="0.01" min="0" required>
                        </div>
                        
                        <hr>
                        <h6>Benefícios e Acessos</h6>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="acesso_academia" name="acesso_academia" checked>
                                    <label class="form-check-label" for="acesso_academia">
                                        Acesso à Academia
                                    </label>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="acesso_musculacao" name="acesso_musculacao" checked>
                                    <label class="form-check-label" for="acesso_musculacao">
                                        Acesso à Musculação
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="acesso_todas_unidades" name="acesso_todas_unidades">
                                    <label class="form-check-label" for="acesso_todas_unidades">
                                        Acesso a Todas as Unidades
                                    </label>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="acesso_todos_cursos" name="acesso_todos_cursos">
                                    <label class="form-check-label" for="acesso_todos_cursos">
                                        Acesso a Todos os Cursos
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="quantidade_cursos">Quantidade de Cursos (0 = ilimitado se "Acesso a Todos" estiver marcado):</label>
                            <input type="number" class="form-control" id="quantidade_cursos" name="quantidade_cursos" min="0" value="0">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="aulas_grupais_ilimitadas" name="aulas_grupais_ilimitadas">
                                    <label class="form-check-label" for="aulas_grupais_ilimitadas">
                                        Aulas Grupais Ilimitadas
                                    </label>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="personal_trainer" name="personal_trainer">
                                    <label class="form-check-label" for="personal_trainer">
                                        Personal Trainer
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="nutricionista" name="nutricionista">
                                    <label class="form-check-label" for="nutricionista">
                                        Nutricionista
                                    </label>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="avaliacao_fisica" name="avaliacao_fisica">
                                    <label class="form-check-label" for="avaliacao_fisica">
                                        Avaliação Física
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="app_exclusivo" name="app_exclusivo">
                                    <label class="form-check-label" for="app_exclusivo">
                                        App Exclusivo
                                    </label>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="desconto_loja" name="desconto_loja">
                                    <label class="form-check-label" for="desconto_loja">
                                        Desconto na Loja
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarNovoPlano">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar plano -->
    <div class="modal fade" id="modalEditarPlano" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Editar Plano</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formEditarPlano">
                        <input type="hidden" id="editarPlanoId" name="id">
                        <div class="form-group">
                            <label for="editarNome">Nome do Plano:</label>
                            <input type="text" class="form-control" id="editarNome" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="editarDescricao">Descrição:</label>
                            <textarea class="form-control" id="editarDescricao" name="descricao" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="editarValorMensal">Valor Mensal (R$):</label>
                            <input type="number" class="form-control" id="editarValorMensal" name="valor_mensal" step="0.01" min="0" required>
                        </div>
                        
                        <hr>
                        <h6>Benefícios e Acessos</h6>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="editarAcessoAcademia" name="acesso_academia">
                                    <label class="form-check-label" for="editarAcessoAcademia">
                                        Acesso à Academia
                                    </label>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="editarAcessoMusculacao" name="acesso_musculacao">
                                    <label class="form-check-label" for="editarAcessoMusculacao">
                                        Acesso à Musculação
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="editarAcessoTodasUnidades" name="acesso_todas_unidades">
                                    <label class="form-check-label" for="editarAcessoTodasUnidades">
                                        Acesso a Todas as Unidades
                                    </label>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="editarAcessoTodosCursos" name="acesso_todos_cursos">
                                    <label class="form-check-label" for="editarAcessoTodosCursos">
                                        Acesso a Todos os Cursos
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="editarQuantidadeCursos">Quantidade de Cursos (0 = ilimitado se "Acesso a Todos" estiver marcado):</label>
                            <input type="number" class="form-control" id="editarQuantidadeCursos" name="quantidade_cursos" min="0" value="0">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="editarAulasGrupaisIlimitadas" name="aulas_grupais_ilimitadas">
                                    <label class="form-check-label" for="editarAulasGrupaisIlimitadas">
                                        Aulas Grupais Ilimitadas
                                    </label>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="editarPersonalTrainer" name="personal_trainer">
                                    <label class="form-check-label" for="editarPersonalTrainer">
                                        Personal Trainer
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="editarNutricionista" name="nutricionista">
                                    <label class="form-check-label" for="editarNutricionista">
                                        Nutricionista
                                    </label>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="editarAvaliacaoFisica" name="avaliacao_fisica">
                                    <label class="form-check-label" for="editarAvaliacaoFisica">
                                        Avaliação Física
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="editarAppExclusivo" name="app_exclusivo">
                                    <label class="form-check-label" for="editarAppExclusivo">
                                        App Exclusivo
                                    </label>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="editarDescontoLoja" name="desconto_loja">
                                    <label class="form-check-label" for="editarDescontoLoja">
                                        Desconto na Loja
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="editarAtivo">Status:</label>
                            <select class="form-control" id="editarAtivo" name="ativo">
                                <option value="1">Ativo</option>
                                <option value="0">Inativo</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarEdicaoPlano">Salvar Alterações</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../assets/js/notifications.js"></script>
    <script>
        // Função para carregar planos
        function carregarPlanos() {
            $.ajax({
                url: 'planos_api.php?action=list',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        renderizarPlanos(response.data);
                    } else {
                        $('#planosTable').html('<tr><td colspan="6" class="text-center text-danger">Erro ao carregar planos</td></tr>');
                    }
                },
                error: function() {
                    $('#planosTable').html('<tr><td colspan="6" class="text-center text-danger">Erro ao carregar planos</td></tr>');
                }
            });
        }

        // Função para renderizar planos na tabela
        function renderizarPlanos(planos) {
            if (planos.length === 0) {
                $('#planosTable').html('<tr><td colspan="6" class="text-center">Nenhum plano cadastrado</td></tr>');
                return;
            }

            let html = '';
            planos.forEach(function(plano) {
                const beneficios = [];
                if (plano.acesso_academia == 1) beneficios.push('Academia');
                if (plano.acesso_musculacao == 1) beneficios.push('Musculação');
                if (plano.acesso_todas_unidades == 1) beneficios.push('Todas Unidades');
                if (plano.acesso_todos_cursos == 1) beneficios.push('Todos Cursos');
                else if (plano.quantidade_cursos > 0) beneficios.push(plano.quantidade_cursos + ' Cursos');
                if (plano.aulas_grupais_ilimitadas == 1) beneficios.push('Aulas Ilimitadas');
                if (plano.personal_trainer == 1) beneficios.push('Personal');
                if (plano.nutricionista == 1) beneficios.push('Nutricionista');
                if (plano.avaliacao_fisica == 1) beneficios.push('Avaliação');
                if (plano.app_exclusivo == 1) beneficios.push('App');
                if (plano.desconto_loja == 1) beneficios.push('Desconto');

                const statusBadge = plano.ativo == 1 
                    ? '<span class="badge badge-success">Ativo</span>' 
                    : '<span class="badge badge-secondary">Inativo</span>';

                html += `
                    <tr>
                        <td>${plano.id}</td>
                        <td><strong>${plano.nome}</strong></td>
                        <td>R$ ${parseFloat(plano.valor_mensal).toFixed(2).replace('.', ',')}</td>
                        <td>
                            ${beneficios.length > 0 ? beneficios.map(b => `<span class="benefit-badge">${b}</span>`).join('') : '<span class="text-muted">Nenhum</span>'}
                        </td>
                        <td>${statusBadge}</td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="editarPlano(${plano.id})">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deletarPlano(${plano.id})">
                                <i class="fas fa-trash"></i> Deletar
                            </button>
                        </td>
                    </tr>
                `;
            });
            $('#planosTable').html(html);
        }

        // Função para mostrar alerta
        function mostrarAlerta(mensagem, tipo) {
            const alert = `<div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
                ${mensagem}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>`;
            $('#alertContainer').html(alert);
            setTimeout(() => {
                $('.alert').fadeOut();
            }, 5000);
        }

        // Salvar novo plano
        $('#btnSalvarNovoPlano').on('click', function() {
            const formData = {
                action: 'create',
                nome: $('#nome').val(),
                descricao: $('#descricao').val(),
                valor_mensal: $('#valor_mensal').val(),
                acesso_academia: $('#acesso_academia').is(':checked') ? 1 : 0,
                acesso_musculacao: $('#acesso_musculacao').is(':checked') ? 1 : 0,
                acesso_todas_unidades: $('#acesso_todas_unidades').is(':checked') ? 1 : 0,
                acesso_todos_cursos: $('#acesso_todos_cursos').is(':checked') ? 1 : 0,
                quantidade_cursos: $('#quantidade_cursos').val() || 0,
                aulas_grupais_ilimitadas: $('#aulas_grupais_ilimitadas').is(':checked') ? 1 : 0,
                personal_trainer: $('#personal_trainer').is(':checked') ? 1 : 0,
                nutricionista: $('#nutricionista').is(':checked') ? 1 : 0,
                avaliacao_fisica: $('#avaliacao_fisica').is(':checked') ? 1 : 0,
                app_exclusivo: $('#app_exclusivo').is(':checked') ? 1 : 0,
                desconto_loja: $('#desconto_loja').is(':checked') ? 1 : 0
            };

            $.ajax({
                url: 'planos_api.php',
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification(response.message, 'success');
                        $('#modalNovoPlano').modal('hide');
                        $('#formNovoPlano')[0].reset();
                        carregarPlanos();
                    } else {
                        showNotification(response.message, 'error');
                    }
                },
                error: function() {
                    showNotification('Erro ao criar plano', 'error');
                }
            });
        });

        // Função para editar plano
        function editarPlano(id) {
            $.ajax({
                url: 'planos_api.php?action=get&id=' + id,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const plano = response.data;
                        $('#editarPlanoId').val(plano.id);
                        $('#editarNome').val(plano.nome);
                        $('#editarDescricao').val(plano.descricao || '');
                        $('#editarValorMensal').val(plano.valor_mensal);
                        $('#editarAcessoAcademia').prop('checked', plano.acesso_academia == 1);
                        $('#editarAcessoMusculacao').prop('checked', plano.acesso_musculacao == 1);
                        $('#editarAcessoTodasUnidades').prop('checked', plano.acesso_todas_unidades == 1);
                        $('#editarAcessoTodosCursos').prop('checked', plano.acesso_todos_cursos == 1);
                        $('#editarQuantidadeCursos').val(plano.quantidade_cursos || 0);
                        $('#editarAulasGrupaisIlimitadas').prop('checked', plano.aulas_grupais_ilimitadas == 1);
                        $('#editarPersonalTrainer').prop('checked', plano.personal_trainer == 1);
                        $('#editarNutricionista').prop('checked', plano.nutricionista == 1);
                        $('#editarAvaliacaoFisica').prop('checked', plano.avaliacao_fisica == 1);
                        $('#editarAppExclusivo').prop('checked', plano.app_exclusivo == 1);
                        $('#editarDescontoLoja').prop('checked', plano.desconto_loja == 1);
                        $('#editarAtivo').val(plano.ativo);
                        $('#modalEditarPlano').modal('show');
                    } else {
                        showNotification('Erro ao carregar plano', 'error');
                    }
                }
            });
        }

        // Salvar edição
        $('#btnSalvarEdicaoPlano').on('click', function() {
            const formData = {
                action: 'update',
                id: $('#editarPlanoId').val(),
                nome: $('#editarNome').val(),
                descricao: $('#editarDescricao').val(),
                valor_mensal: $('#editarValorMensal').val(),
                acesso_academia: $('#editarAcessoAcademia').is(':checked') ? 1 : 0,
                acesso_musculacao: $('#editarAcessoMusculacao').is(':checked') ? 1 : 0,
                acesso_todas_unidades: $('#editarAcessoTodasUnidades').is(':checked') ? 1 : 0,
                acesso_todos_cursos: $('#editarAcessoTodosCursos').is(':checked') ? 1 : 0,
                quantidade_cursos: $('#editarQuantidadeCursos').val() || 0,
                aulas_grupais_ilimitadas: $('#editarAulasGrupaisIlimitadas').is(':checked') ? 1 : 0,
                personal_trainer: $('#editarPersonalTrainer').is(':checked') ? 1 : 0,
                nutricionista: $('#editarNutricionista').is(':checked') ? 1 : 0,
                avaliacao_fisica: $('#editarAvaliacaoFisica').is(':checked') ? 1 : 0,
                app_exclusivo: $('#editarAppExclusivo').is(':checked') ? 1 : 0,
                desconto_loja: $('#editarDescontoLoja').is(':checked') ? 1 : 0,
                ativo: $('#editarAtivo').val()
            };

            $.ajax({
                url: 'planos_api.php',
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification(response.message, 'success');
                        $('#modalEditarPlano').modal('hide');
                        carregarPlanos();
                    } else {
                        showNotification(response.message, 'error');
                    }
                },
                error: function() {
                    showNotification('Erro ao atualizar plano', 'error');
                }
            });
        });

        // Função para deletar plano
        async function deletarPlano(id) {
            const confirmed = await showConfirm('Tem certeza que deseja deletar este plano?', 'Confirmar Exclusão', 'danger');
            if (confirmed) {
                $.ajax({
                    url: 'planos_api.php?action=delete',
                    method: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showNotification(response.message, 'success');
                            carregarPlanos();
                        } else {
                            showNotification(response.message, 'error');
                        }
                    }
                });
            }
        }

        // Carregar planos ao iniciar
        $(document).ready(function() {
            carregarPlanos();
        });
    </script>
</body>
</html>

