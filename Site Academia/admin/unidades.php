<?php
require_once 'check_admin.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Unidades - TechFit</title>
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
                <a class="nav-link active" href="unidades.php">
                    <i class="fas fa-building"></i> Gerenciar Unidades
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="cursos.php">
                    <i class="fas fa-graduation-cap"></i> Gerenciar Cursos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="planos.php">
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
                    <h1 class="h2 mb-0">Gerenciar Unidades</h1>
                    <p class="text-muted mb-0">Adicione, edite e gerencie as unidades da academia.</p>
                    <button class="btn btn-primary mt-2" data-toggle="modal" data-target="#modalNovaUnidade">
                        <i class="fas fa-plus"></i> Nova Unidade
                    </button>
                </div>

                <!-- Mensagens de alerta -->
                <div id="alertContainer"></div>

                <!-- Tabela de unidades -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Cidade</th>
                                <th>Endereço</th>
                                <th>Telefone</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="unidadesTable">
                            <tr>
                                <td colspan="7" class="text-center">Carregando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </main>

    <!-- Modal para nova unidade -->
    <div class="modal fade" id="modalNovaUnidade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white">Nova Unidade</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formNovaUnidade">
                        <div class="form-group">
                            <label for="nome">Nome da Unidade:</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="cidade">Cidade:</label>
                                <input type="text" class="form-control" id="cidade" name="cidade" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="telefone">Telefone:</label>
                                <input type="tel" class="form-control" id="telefone" name="telefone">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="endereco">Endereço:</label>
                            <input type="text" class="form-control" id="endereco" name="endereco" required>
                        </div>
                        <div class="form-group">
                            <label for="horario_funcionamento">Horário de Funcionamento:</label>
                            <textarea class="form-control" id="horario_funcionamento" name="horario_funcionamento" rows="2" placeholder="Ex: Segunda a Sexta: 6h às 23h | Sábado: 7h às 20h"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarNovaUnidade">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar unidade -->
    <div class="modal fade" id="modalEditarUnidade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white">Editar Unidade</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formEditarUnidade">
                        <input type="hidden" id="editarUnidadeId" name="id">
                        <div class="form-group">
                            <label for="editarNome">Nome da Unidade:</label>
                            <input type="text" class="form-control" id="editarNome" name="nome" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="editarCidade">Cidade:</label>
                                <input type="text" class="form-control" id="editarCidade" name="cidade" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="editarTelefone">Telefone:</label>
                                <input type="tel" class="form-control" id="editarTelefone" name="telefone">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="editarEndereco">Endereço:</label>
                            <input type="text" class="form-control" id="editarEndereco" name="endereco" required>
                        </div>
                        <div class="form-group">
                            <label for="editarHorario">Horário de Funcionamento:</label>
                            <textarea class="form-control" id="editarHorario" name="horario_funcionamento" rows="2"></textarea>
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
                    <button type="button" class="btn btn-primary" id="btnSalvarEdicaoUnidade">Salvar Alterações</button>
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
        // Função para carregar unidades
        function carregarUnidades() {
            $.ajax({
                url: 'unidades_api.php?action=list',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let html = '';
                        response.data.forEach(function(unidade) {
                            const status = unidade.ativo ? '<span class="badge badge-success">Ativo</span>' : '<span class="badge badge-danger">Inativo</span>';
                            
                            html += `<tr>
                                <td>${unidade.id}</td>
                                <td>${unidade.nome}</td>
                                <td>${unidade.cidade}</td>
                                <td>${unidade.endereco}</td>
                                <td>${unidade.telefone || '-'}</td>
                                <td>${status}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="editarUnidade(${unidade.id})">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deletarUnidade(${unidade.id})">
                                        <i class="fas fa-trash"></i> Deletar
                                    </button>
                                </td>
                            </tr>`;
                        });
                        $('#unidadesTable').html(html);
                    } else {
                        $('#unidadesTable').html('<tr><td colspan="7" class="text-center text-danger">Erro ao carregar unidades</td></tr>');
                    }
                },
                error: function() {
                    $('#unidadesTable').html('<tr><td colspan="7" class="text-center text-danger">Erro ao conectar ao servidor</td></tr>');
                }
            });
        }

        // Função para editar unidade
        function editarUnidade(id) {
            $.ajax({
                url: 'unidades_api.php?action=get&id=' + id,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const unidade = response.data;
                        $('#editarUnidadeId').val(unidade.id);
                        $('#editarNome').val(unidade.nome);
                        $('#editarCidade').val(unidade.cidade);
                        $('#editarTelefone').val(unidade.telefone);
                        $('#editarEndereco').val(unidade.endereco);
                        $('#editarHorario').val(unidade.horario_funcionamento);
                        $('#editarAtivo').val(unidade.ativo);
                        $('#modalEditarUnidade').modal('show');
                    } else {
                        showNotification('Erro ao carregar unidade', 'error');
                    }
                }
            });
        }

        // Função para deletar unidade
        async function deletarUnidade(id) {
            const confirmed = await showConfirm('Tem certeza que deseja deletar esta unidade?', 'Confirmar Exclusão', 'danger');
            if (confirmed) {
                $.ajax({
                    url: 'unidades_api.php?action=delete',
                    method: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            mostrarAlerta('Unidade deletada com sucesso', 'success');
                            carregarUnidades();
                        } else {
                            mostrarAlerta(response.message || 'Erro ao deletar unidade', 'danger');
                        }
                    }
                });
            }
        }

        // Função para mostrar alertas
        function mostrarAlerta(mensagem, tipo) {
            const alert = `<div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
                ${mensagem}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>`;
            $('#alertContainer').html(alert);
        }

        // Salvar nova unidade
        $('#btnSalvarNovaUnidade').click(function() {
            const dados = $('#formNovaUnidade').serialize() + '&action=create';
            $.ajax({
                url: 'unidades_api.php',
                method: 'POST',
                data: dados,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarAlerta('Unidade criada com sucesso', 'success');
                        $('#modalNovaUnidade').modal('hide');
                        $('#formNovaUnidade')[0].reset();
                        carregarUnidades();
                    } else {
                        mostrarAlerta(response.message || 'Erro ao criar unidade', 'danger');
                    }
                }
            });
        });

        // Salvar edição de unidade
        $('#btnSalvarEdicaoUnidade').click(function() {
            const dados = $('#formEditarUnidade').serialize() + '&action=update';
            $.ajax({
                url: 'unidades_api.php',
                method: 'POST',
                data: dados,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarAlerta('Unidade atualizada com sucesso', 'success');
                        $('#modalEditarUnidade').modal('hide');
                        carregarUnidades();
                    } else {
                        mostrarAlerta(response.message || 'Erro ao atualizar unidade', 'danger');
                    }
                }
            });
        });

        // Carregar unidades ao abrir a página
        $(document).ready(function() {
            carregarUnidades();
        });
    </script>
</body>
</html>
