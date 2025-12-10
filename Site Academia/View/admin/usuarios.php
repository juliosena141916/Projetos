<?php
require_once 'check_admin.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - TechFit</title>
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

        .mb-3 {
            width: 25%;
        }
    </style>
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
                <a class="nav-link active" href="usuarios.php">
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
                    <h1 class="h2 mb-0">Gerenciar Usuários</h1>
                    <p class="text-muted mb-0">Adicione, edite e gerencie os usuários do sistema.</p>
                    <button class="btn btn-primary mt-2" data-toggle="modal" data-target="#modalNovoUsuario">
                        <i class="fas fa-user-plus"></i> Novo Usuário
                    </button>
                </div>

                <!-- Barra de pesquisa -->
                <div class="mb-3">
                    <input type="text" id="searchInput" class="form-control" placeholder="Pesquisar por nome...">
                </div>

                <!-- Mensagens de alerta -->
                <div id="alertContainer"></div>

                <!-- Tabela de usuários -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Tipo</th>
                                <th>Status</th>
                                <th>Data de Cadastro</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="usuariosTable">
                            <tr>
                                <td colspan="7" class="text-center">Carregando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </main>

    <!-- Modal para novo usuário -->
    <div class="modal fade" id="modalNovoUsuario" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white">Novo Usuário</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formNovoUsuario">
                        <div class="form-group">
                            <label for="nome">Nome:</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="senha">Senha:</label>
                            <input type="password" class="form-control" id="senha" name="senha" required>
                        </div>
                        <div class="form-group">
                            <label for="tipo_usuario">Tipo:</label>
                            <select class="form-control" id="tipo_usuario" name="tipo_usuario">
                                <option value="usuario">Usuário</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarNovoUsuario">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar usuário -->
    <div class="modal fade" id="modalEditarUsuario" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white">Editar Usuário</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formEditarUsuario">
                        <input type="hidden" id="editarUsuarioId" name="id">
                        <div class="form-group">
                            <label for="editarNome">Nome:</label>
                            <input type="text" class="form-control" id="editarNome" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="editarEmail">Email:</label>
                            <input type="email" class="form-control" id="editarEmail" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="editarTipo">Tipo:</label>
                            <select class="form-control" id="editarTipo" name="tipo_usuario">
                                <option value="usuario">Usuário</option>
                                <option value="admin">Administrador</option>
                            </select>
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
                    <button type="button" class="btn btn-primary" id="btnSalvarEdicao">Salvar Alterações</button>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="../assets/css/notifications.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../assets/js/notifications.js"></script>
    <script>
        let usuariosData = []; // Armazenar dados dos usuários

        // Função para carregar usuários
        function carregarUsuarios() {
            $.ajax({
                url: 'usuarios_api.php?action=list',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        usuariosData = response.data; // Armazenar dados
                        renderizarUsuarios(usuariosData);
                    } else {
                        $('#usuariosTable').html('<tr><td colspan="7" class="text-center text-danger">Erro ao carregar usuários</td></tr>');
                    }
                },
                error: function() {
                    $('#usuariosTable').html('<tr><td colspan="7" class="text-center text-danger">Erro ao conectar ao servidor</td></tr>');
                }
            });
        }

        // Função para renderizar usuários na tabela
        function renderizarUsuarios(usuarios) {
            let html = '';
            usuarios.forEach(function(usuario) {
                const status = usuario.ativo ? '<span class="badge badge-success">Ativo</span>' : '<span class="badge badge-danger">Inativo</span>';
                const tipo = usuario.tipo_usuario === 'admin' ? '<span class="badge badge-warning">Admin</span>' : '<span class="badge badge-info">Usuário</span>';
                const data = new Date(usuario.data_cadastro).toLocaleDateString('pt-BR');

                html += `<tr>
                    <td>${usuario.id}</td>
                    <td>${usuario.nome}</td>
                    <td>${usuario.email}</td>
                    <td>${tipo}</td>
                    <td>${status}</td>
                    <td>${data}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editarUsuario(${usuario.id})">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deletarUsuario(${usuario.id})">
                            <i class="fas fa-trash"></i> Deletar
                        </button>
                    </td>
                </tr>`;
            });
            $('#usuariosTable').html(html);
        }

        // Função para filtrar usuários
        function filtrarUsuarios() {
            const searchTerm = $('#searchInput').val().toLowerCase();
            const filteredUsuarios = usuariosData.filter(function(usuario) {
                return usuario.nome.toLowerCase().includes(searchTerm);
            });
            renderizarUsuarios(filteredUsuarios);
        }

        // Event listener para o campo de pesquisa
        $('#searchInput').on('input', function() {
            filtrarUsuarios();
        });

        // Função para editar usuário
        function editarUsuario(id) {
            $.ajax({
                url: 'usuarios_api.php?action=get&id=' + id,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const usuario = response.data;
                        $('#editarUsuarioId').val(usuario.id);
                        $('#editarNome').val(usuario.nome);
                        $('#editarEmail').val(usuario.email);
                        $('#editarTipo').val(usuario.tipo_usuario);
                        $('#editarAtivo').val(usuario.ativo);
                        $('#modalEditarUsuario').modal('show');
                    } else {
                        showNotification('Erro ao carregar usuário', 'error');
                    }
                }
            });
        }

        // Função para deletar usuário
        async function deletarUsuario(id) {
            const confirmed = await showConfirm('Tem certeza que deseja deletar este usuário?', 'Confirmar Exclusão', 'danger');
            if (confirmed) {
                $.ajax({
                    url: 'usuarios_api.php?action=delete',
                    method: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            mostrarAlerta('Usuário deletado com sucesso', 'success');
                            carregarUsuarios();
                        } else {
                            mostrarAlerta(response.message || 'Erro ao deletar usuário', 'danger');
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

        // Salvar novo usuário
        $('#btnSalvarNovoUsuario').click(function() {
            const dados = $('#formNovoUsuario').serialize() + '&action=create';
            $.ajax({
                url: 'usuarios_api.php',
                method: 'POST',
                data: dados,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarAlerta('Usuário criado com sucesso', 'success');
                        $('#modalNovoUsuario').modal('hide');
                        $('#formNovoUsuario')[0].reset();
                        carregarUsuarios();
                    } else {
                        mostrarAlerta(response.message || 'Erro ao criar usuário', 'danger');
                    }
                }
            });
        });

        // Salvar edição de usuário
        $('#btnSalvarEdicao').click(function() {
            const dados = $('#formEditarUsuario').serialize() + '&action=update';
            $.ajax({
                url: 'usuarios_api.php',
                method: 'POST',
                data: dados,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarAlerta('Usuário atualizado com sucesso', 'success');
                        $('#modalEditarUsuario').modal('hide');
                        carregarUsuarios();
                    } else {
                        mostrarAlerta(response.message || 'Erro ao atualizar usuário', 'danger');
                    }
                }
            });
        });

        // Carregar usuários ao abrir a página
        $(document).ready(function() {
            carregarUsuarios();
        });
    </script>
</body>
</html>
