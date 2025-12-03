?php
require_once 'check_admin.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Cursos - TechFit</title>
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
	                <a class="nav-link" href="unidades.php">
	                    <i class="fas fa-building"></i> Gerenciar Unidades
	                </a>
	            </li>
	            <li class="nav-item">
	                <a class="nav-link active" href="cursos.php">
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
	                    <h1 class="h2 mb-0">Gerenciar Cursos</h1>
	                    <p class="text-muted mb-0">Adicione, edite e gerencie os cursos oferecidos.</p>
	                    <button class="btn btn-primary mt-2" data-toggle="modal" data-target="#modalNovoCurso">
	                        <i class="fas fa-plus"></i> Novo Curso
	                    </button>
	                </div>

                <!-- Mensagens de alerta -->
                <div id="alertContainer"></div>

                <!-- Tabela de cursos -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Categoria</th>
                                <th>Duração (semanas)</th>
                                <th>Valor (R$)</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="cursosTable">
                            <tr>
                                <td colspan="7" class="text-center">Carregando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
	            </main>

    <!-- Modal para novo curso -->
    <div class="modal fade" id="modalNovoCurso" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
	                <div class="modal-header">
	                    <h5 class="modal-title text-white">Novo Curso</h5>
	                    <button type="button" class="close text-white" data-dismiss="modal">
	                        <span>&times;</span>
	                    </button>
	                </div>
                <div class="modal-body">
                    <form id="formNovoCurso">
                        <div class="form-group">
                            <label for="nome">Nome do Curso:</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="categoria">Categoria:</label>
                                <input type="text" class="form-control" id="categoria" name="categoria" placeholder="Ex: Condicionamento Físico" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="duracao">Duração (semanas):</label>
                                <input type="number" class="form-control" id="duracao" name="duracao" min="1" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="valor_total">Valor Total (R$):</label>
                                <input type="number" class="form-control" id="valor_total" name="valor_total" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="descricao">Descrição:</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-info" id="btnSalvarNovoCurso">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar curso -->
    <div class="modal fade" id="modalEditarCurso" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
	                <div class="modal-header">
	                    <h5 class="modal-title text-white">Editar Curso</h5>
	                    <button type="button" class="close text-white" data-dismiss="modal">
	                        <span>&times;</span>
	                    </button>
	                </div>
                <div class="modal-body">
                    <form id="formEditarCurso">
                        <input type="hidden" id="editarCursoId" name="id">
                        <div class="form-group">
                            <label for="editarNome">Nome do Curso:</label>
                            <input type="text" class="form-control" id="editarNome" name="nome" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="editarCategoria">Categoria:</label>
                                <input type="text" class="form-control" id="editarCategoria" name="categoria" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="editarDuracao">Duração (semanas):</label>
                                <input type="number" class="form-control" id="editarDuracao" name="duracao" min="1" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="editarValor">Valor Total (R$):</label>
                                <input type="number" class="form-control" id="editarValor" name="valor_total" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="editarDescricao">Descrição:</label>
                            <textarea class="form-control" id="editarDescricao" name="descricao" rows="3" required></textarea>
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
                    <button type="button" class="btn btn-primary" id="btnSalvarEdicaoCurso">Salvar Alterações</button>
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
        // Função para carregar cursos
        function carregarCursos() {
            $.ajax({
                url: 'cursos_api.php?action=list',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let html = '';
                        response.data.forEach(function(curso) {
                            const status = curso.ativo ? '<span class="badge badge-success">Ativo</span>' : '<span class="badge badge-danger">Inativo</span>';
                            const valor = parseFloat(curso.valor_total).toFixed(2).replace('.', ',');
                            
                            html += `<tr>
                                <td>${curso.id}</td>
                                <td>${curso.nome}</td>
                                <td>${curso.categoria}</td>
                                <td>${curso.duracao} semanas</td>
                                <td>R$ ${valor}</td>
                                <td>${status}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="editarCurso(${curso.id})">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deletarCurso(${curso.id})">
                                        <i class="fas fa-trash"></i> Deletar
                                    </button>
                                </td>
                            </tr>`;
                        });
                        $('#cursosTable').html(html);
                    } else {
                        $('#cursosTable').html('<tr><td colspan="7" class="text-center text-danger">Erro ao carregar cursos</td></tr>');
                    }
                },
                error: function() {
                    $('#cursosTable').html('<tr><td colspan="7" class="text-center text-danger">Erro ao conectar ao servidor</td></tr>');
                }
            });
        }

        // Função para editar curso
        function editarCurso(id) {
            $.ajax({
                url: 'cursos_api.php?action=get&id=' + id,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const curso = response.data;
                        $('#editarCursoId').val(curso.id);
                        $('#editarNome').val(curso.nome);
                        $('#editarCategoria').val(curso.categoria);
                        $('#editarDuracao').val(curso.duracao);
                        $('#editarValor').val(curso.valor_total);
                        $('#editarDescricao').val(curso.descricao);
                        $('#editarAtivo').val(curso.ativo);
                        $('#modalEditarCurso').modal('show');
                    } else {
                        showNotification('Erro ao carregar curso', 'error');
                    }
                }
            });
        }

        // Função para deletar curso
        async function deletarCurso(id) {
            const confirmed = await showConfirm('Tem certeza que deseja deletar este curso?', 'Confirmar Exclusão', 'danger');
            if (confirmed) {
                $.ajax({
                    url: 'cursos_api.php?action=delete',
                    method: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            mostrarAlerta('Curso deletado com sucesso', 'success');
                            carregarCursos();
                        } else {
                            mostrarAlerta(response.message || 'Erro ao deletar curso', 'danger');
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

        // Salvar novo curso
        $('#btnSalvarNovoCurso').click(function() {
            const dados = $('#formNovoCurso').serialize() + '&action=create';
            $.ajax({
                url: 'cursos_api.php',
                method: 'POST',
                data: dados,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarAlerta('Curso criado com sucesso', 'success');
                        $('#modalNovoCurso').modal('hide');
                        $('#formNovoCurso')[0].reset();
                        carregarCursos();
                    } else {
                        mostrarAlerta(response.message || 'Erro ao criar curso', 'danger');
                    }
                }
            });
        });

        // Salvar edição de curso
        $('#btnSalvarEdicaoCurso').click(function() {
            const dados = $('#formEditarCurso').serialize() + '&action=update';
            console.log('Enviando dados:', dados);
            
            // Desabilitar botão durante o envio
            const btn = $(this);
            btn.prop('disabled', true).text('Salvando...');
            
            $.ajax({
                url: 'cursos_api.php',
                method: 'POST',
                data: dados,
                dataType: 'text', // Receber como texto primeiro
                success: function(responseText) {
                    console.log('Resposta bruta:', responseText);
                    
                    try {
                        const response = JSON.parse(responseText);
                        console.log('Resposta JSON:', response);
                        
                        if (response.success) {
                            mostrarAlerta('Curso atualizado com sucesso!', 'success');
                            $('#modalEditarCurso').modal('hide');
                            carregarCursos();
                        } else {
                            let msg = response.message || 'Erro ao atualizar curso';
                            if (response.dados_recebidos) {
                                console.log('Dados recebidos pelo servidor:', response.dados_recebidos);
                            }
                            mostrarAlerta(msg, 'danger');
                        }
                    } catch (e) {
                        console.error('Erro ao parsear JSON:', e);
                        console.error('Resposta recebida:', responseText);
                        mostrarAlerta('Erro: resposta inválida do servidor. Veja o console (F12).', 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro AJAX:', status, error);
                    console.error('Resposta:', xhr.responseText);
                    
                    let msg = 'Erro ao conectar ao servidor';
                    try {
                        const resp = JSON.parse(xhr.responseText);
                        msg = resp.message || msg;
                    } catch(e) {}
                    
                    mostrarAlerta(msg, 'danger');
                },
                complete: function() {
                    btn.prop('disabled', false).text('Salvar Alterações');
                }
            });
        });

        // Carregar cursos ao abrir a página
        $(document).ready(function() {
            carregarCursos();
        });
    </script>
</body>
</html>
