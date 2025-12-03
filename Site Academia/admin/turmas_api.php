<?php

// IMPORTANTE: session_start() deve ser chamado ANTES de qualquer output
// Iniciar sessão apenas se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log para debug - verificar se a API está sendo chamada
error_log("=== TURMAS_API.PHP CHAMADO ===");
error_log("Método: " . $_SERVER['REQUEST_METHOD']);
error_log("Timestamp: " . date('Y-m-d H:i:s'));

// Usar caminho absoluto baseado no diretório atual
$conexao_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'conexao.php';
$check_admin_path = __DIR__ . DIRECTORY_SEPARATOR . 'check_admin.php';

// Normalizar caminhos para Windows
$conexao_path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $conexao_path);
$check_admin_path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $check_admin_path);

error_log("Caminho conexao: " . $conexao_path);
error_log("Caminho check_admin: " . $check_admin_path);
error_log("Conexao existe: " . (file_exists($conexao_path) ? 'SIM' : 'NÃO'));
error_log("Check_admin existe: " . (file_exists($check_admin_path) ? 'SIM' : 'NÃO'));

if (!file_exists($conexao_path)) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Erro: Arquivo de conexão não encontrado em ' . $conexao_path,
        'caminho_tentado' => $conexao_path,
        'diretorio_atual' => __DIR__,
        'diretorio_pai' => dirname(__DIR__)
    ]);
    exit;
}

if (!file_exists($check_admin_path)) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Erro: Arquivo check_admin.php não encontrado em ' . $check_admin_path
    ]);
    exit;
}

require_once $conexao_path;

// Verificar se usuário está logado e é admin (sem redirecionar, apenas retornar erro JSON)
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['tipo_usuario'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

if ($_SESSION['tipo_usuario'] !== 'admin') {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Acesso negado. Apenas administradores podem acessar.']);
    exit;
}

header('Content-Type: application/json');

// A verificação de admin é feita pelo check_admin.php
// Se chegou aqui, o usuário é admin e a sessão está ativa.

$method = $_SERVER['REQUEST_METHOD'];
error_log("Método processado: " . $method);

try {
    $pdo = getConexao();
    error_log("Conexão com banco estabelecida");
} catch (Exception $e) {
    error_log("ERRO na conexão: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com banco de dados']);
    exit;
}

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Buscar turma específica com detalhes
                $stmt = $pdo->prepare("
                    SELECT t.*, c.nome as curso_nome, c.duracao, c.valor_total,
                           u.nome as unidade_nome, u.cidade,
                           (SELECT COUNT(*) FROM matriculas WHERE turma_id = t.id AND status != 'cancelada') as total_matriculas
                    FROM turmas_cursos t
                    JOIN cursos c ON t.curso_id = c.id
                    JOIN unidades u ON t.unidade_id = u.id
                    WHERE t.id = ?
                ");
                $stmt->execute([$_GET['id']]);
                $turma = $stmt->fetch();
                
                if ($turma) {
                    // Buscar aulas da turma
                    $stmt = $pdo->prepare("
                        SELECT * FROM aulas_agendadas 
                        WHERE turma_id = ? 
                        ORDER BY data_aula, hora_inicio
                    ");
                    $stmt->execute([$_GET['id']]);
                    $turma['aulas'] = $stmt->fetchAll();
                    
                    echo json_encode(['success' => true, 'turma' => $turma]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Turma não encontrada']);
                }
            } else {
                // Listar todas as turmas ativas
                $stmt = $pdo->query("
                    SELECT t.*, c.nome as curso_nome, c.categoria, c.duracao,
                           u.nome as unidade_nome, u.cidade,
                           (SELECT COUNT(*) FROM matriculas WHERE turma_id = t.id AND status != 'cancelada') as total_matriculas,
                           (SELECT COUNT(*) FROM aulas_agendadas WHERE turma_id = t.id) as total_aulas
                    FROM turmas_cursos t
                    JOIN cursos c ON t.curso_id = c.id
                    JOIN unidades u ON t.unidade_id = u.id
                    WHERE t.ativo = 1
                    ORDER BY t.data_inicio DESC, t.nome_turma ASC
                ");
                $turmas = $stmt->fetchAll();
                echo json_encode(['success' => true, 'turmas' => $turmas]);
            }
            break;

        case 'POST':
            // Criar nova turma
            error_log("=== PROCESSANDO POST ===");
            $input = file_get_contents('php://input');
            error_log("Input bruto recebido: " . substr($input, 0, 500));
            
            $data = json_decode($input, true);
            
            // Log para debug
            error_log("POST turmas_api.php - Input recebido: " . $input);
            error_log("Dados decodificados: " . print_r($data, true));
            
            // Verificar se conseguiu decodificar o JSON
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode([
                    'success' => false, 
                    'message' => 'Erro ao processar dados JSON: ' . json_last_error_msg(),
                    'input_received' => substr($input, 0, 200)
                ]);
                break;
            }
            
            // Verificar se $data é um array
            if (!is_array($data)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false, 
                    'message' => 'Dados inválidos. Esperado um objeto JSON.',
                    'data_type' => gettype($data)
                ]);
                break;
            }
            
            // Validar dados obrigatórios
            $campos_obrigatorios = ['curso_id', 'unidade_id', 'nome_turma', 'data_inicio', 'data_fim'];
            $campos_faltando = [];
            
            foreach ($campos_obrigatorios as $campo) {
                if (!isset($data[$campo]) || (is_string($data[$campo]) && trim($data[$campo]) === '')) {
                    $campos_faltando[] = $campo;
                }
            }
            
            if (!empty($campos_faltando)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false, 
                    'message' => 'Dados incompletos. Campos obrigatórios faltando: ' . implode(', ', $campos_faltando),
                    'campos_recebidos' => array_keys($data)
                ]);
                break;
            }
            
            // Validar se curso existe e está ativo
            $stmt = $pdo->prepare("SELECT id FROM cursos WHERE id = ? AND ativo = 1");
            $stmt->execute([$data['curso_id']]);
            if (!$stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Curso não encontrado ou inativo']);
                break;
            }
            
            // Validar se unidade existe e está ativa
            $stmt = $pdo->prepare("SELECT id FROM unidades WHERE id = ? AND ativo = 1");
            $stmt->execute([$data['unidade_id']]);
            if (!$stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Unidade não encontrada ou inativa']);
                break;
            }
	            
	            // Validar datas (data_fim > data_inicio)
	            if ($data['data_fim'] <= $data['data_inicio']) {
	                http_response_code(400);
	                echo json_encode([
	                    'success' => false, 
	                    'message' => 'Data de fim deve ser posterior à data de início'
	                ]);
	                break;
	            }
	            
            // Validar horários (hora_fim > hora_inicio) se AMBOS fornecidos
            // Tornar horários opcionais - não bloquear criação se não preenchidos
            if (!empty($data['hora_inicio']) && !empty($data['hora_fim']) && $data['hora_fim'] <= $data['hora_inicio']) {
                http_response_code(400);
                echo json_encode([
                    'success' => false, 
                    'message' => 'Horário de fim deve ser posterior ao horário de início'
                ]);
                break;
            }
	            
	            // Validar vagas_totais (> 0)
	            if (isset($data['vagas_totais']) && $data['vagas_totais'] <= 0) {
	                http_response_code(400);
	                echo json_encode([
	                    'success' => false, 
	                    'message' => 'Vagas totais deve ser maior que zero'
	                ]);
	                break;
	            }
            
            $stmt = $pdo->prepare("
                    INSERT INTO turmas_cursos 
                (curso_id, unidade_id, nome_turma, instrutor, vagas_totais, vagas_disponiveis, 
                 data_inicio, data_fim, dias_semana, hora_inicio, hora_fim, sala_padrao, status, ativo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            
            // Preparar valores
            $vagas_totais = isset($data['vagas_totais']) ? intval($data['vagas_totais']) : 20;
            $vagas_disponiveis = $vagas_totais;
            $status = isset($data['status']) && !empty($data['status']) ? $data['status'] : 'planejada';
            
            // Tratar campos opcionais (converter strings vazias para null)
            $instrutor = isset($data['instrutor']) && trim($data['instrutor']) !== '' ? trim($data['instrutor']) : null;
            $dias_semana = isset($data['dias_semana']) && trim($data['dias_semana']) !== '' ? trim($data['dias_semana']) : null;
            $hora_inicio = isset($data['hora_inicio']) && trim($data['hora_inicio']) !== '' ? trim($data['hora_inicio']) : null;
            $hora_fim = isset($data['hora_fim']) && trim($data['hora_fim']) !== '' ? trim($data['hora_fim']) : null;
            $sala_padrao = isset($data['sala_padrao']) && trim($data['sala_padrao']) !== '' ? trim($data['sala_padrao']) : null;
            
            // Log para debug
            error_log("Tentando inserir turma: " . json_encode([
                'curso_id' => $data['curso_id'],
                'unidade_id' => $data['unidade_id'],
                'nome_turma' => $data['nome_turma'],
                'vagas_totais' => $vagas_totais,
                'status' => $status
            ]));
            
            try {
                $valores = [
                    intval($data['curso_id']),
                    intval($data['unidade_id']),
                    trim($data['nome_turma']),
                    $instrutor,
                    $vagas_totais,
                    $vagas_disponiveis,
                    $data['data_inicio'],
                    $data['data_fim'],
                    $dias_semana,
                    $hora_inicio,
                    $hora_fim,
                    $sala_padrao,
                    $status
                ];
                
                // Log dos valores que serão inseridos
                error_log("Valores a serem inseridos: " . json_encode($valores));
                
                $stmt->execute($valores);
            } catch (PDOException $e) {
                error_log("Erro ao executar INSERT: " . $e->getMessage());
                error_log("SQL: " . $stmt->queryString);
                error_log("Valores: " . json_encode($valores ?? []));
                error_log("SQL State: " . $e->errorInfo[0]);
                error_log("Error Code: " . $e->errorInfo[1]);
                error_log("Error Message: " . $e->errorInfo[2]);
                
                // Mensagem mais específica baseada no erro
                $error_message = 'Erro ao salvar turma no banco de dados.';
                
                if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                    if (strpos($e->getMessage(), 'curso_id') !== false) {
                        $error_message = 'Erro: O curso selecionado não existe ou foi removido. Por favor, selecione outro curso.';
                    } elseif (strpos($e->getMessage(), 'unidade_id') !== false) {
                        $error_message = 'Erro: A unidade selecionada não existe ou foi removida. Por favor, selecione outra unidade.';
                    } else {
                        $error_message = 'Erro: Curso ou Unidade inválidos. Verifique se o curso e unidade selecionados existem.';
                    }
                } elseif (strpos($e->getMessage(), 'Data truncated') !== false || strpos($e->getMessage(), 'Incorrect date value') !== false) {
                    $error_message = 'Erro: Formato de data inválido. Use o formato YYYY-MM-DD (ex: 2025-01-15).';
                } elseif (strpos($e->getMessage(), 'Incorrect time value') !== false) {
                    $error_message = 'Erro: Formato de horário inválido. Use o formato HH:MM (ex: 19:00).';
                } elseif (strpos($e->getMessage(), 'Out of range value') !== false) {
                    $error_message = 'Erro: Algum valor está fora do intervalo permitido. Verifique os campos numéricos.';
                }
                
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => $error_message,
                    'error_details' => $e->getMessage(),
                    'sql_state' => $e->errorInfo[0] ?? null
                ]);
                break;
            }
            
            $turma_id = $pdo->lastInsertId();
            error_log("Turma criada com sucesso! ID: " . $turma_id);
            
            // GERAR AULAS AUTOMATICAMENTE se tiver dias da semana e horários configurados
            $aulas_geradas = 0;
            $mensagem_aulas = '';
            
            if (!empty($dias_semana) && !empty($hora_inicio) && !empty($hora_fim)) {
                error_log("Iniciando geração automática de aulas para turma ID: " . $turma_id);
                
                try {
                    // Converter dias da semana de string para array de inteiros
                    $dias_semana_array = array_map('intval', explode(',', $dias_semana));
                    
                    // Gerar aulas entre data_inicio e data_fim
                    $data_inicio_obj = new DateTime($data['data_inicio']);
                    $data_fim_obj = new DateTime($data['data_fim']);
                    
                    $stmt_insert_aula = $pdo->prepare("
                        INSERT INTO aulas_agendadas 
                        (turma_id, data_aula, hora_inicio, hora_fim, sala, status)
                        VALUES (?, ?, ?, ?, ?, 'agendada')
                    ");
                    
                    $pdo->beginTransaction();
                    $count = 0;
                    
                    // Iterar por cada dia entre data_inicio e data_fim
                    $current_date = clone $data_inicio_obj;
                    while ($current_date <= $data_fim_obj) {
                        // Verificar se o dia da semana atual está na lista
                        // format('w') retorna 0=domingo, 1=segunda, etc (igual ao banco)
                        $dia_semana_atual = (int)$current_date->format('w');
                        
                        if (in_array($dia_semana_atual, $dias_semana_array, true)) {
                            // Verificar se já existe aula nesta data (não deveria, mas por segurança)
                            $stmt_check = $pdo->prepare("
                                SELECT COUNT(*) as count FROM aulas_agendadas 
                                WHERE turma_id = ? AND data_aula = ?
                            ");
                            $stmt_check->execute([$turma_id, $current_date->format('Y-m-d')]);
                            $exists = $stmt_check->fetch(PDO::FETCH_ASSOC);
                            
                            if ($exists['count'] == 0) {
                                $stmt_insert_aula->execute([
                                    $turma_id,
                                    $current_date->format('Y-m-d'),
                                    $hora_inicio,
                                    $hora_fim,
                                    $sala_padrao
                                ]);
                                $count++;
                            }
                        }
                        
                        $current_date->modify('+1 day');
                    }
                    
                    $pdo->commit();
                    $aulas_geradas = $count;
                    error_log("Aulas geradas automaticamente: " . $count);
                    
                    if ($count > 0) {
                        $mensagem_aulas = " e {$count} aulas foram geradas automaticamente no calendário!";
                    }
                } catch (Exception $e) {
                    $pdo->rollBack();
                    error_log("Erro ao gerar aulas automaticamente: " . $e->getMessage());
                    // Não falhar a criação da turma se a geração de aulas falhar
                    $mensagem_aulas = " (Aviso: Não foi possível gerar aulas automaticamente: " . $e->getMessage() . ")";
                }
            } else {
                error_log("Turma criada sem geração automática de aulas (faltam dias_semana, hora_inicio ou hora_fim)");
            }
            
            // Buscar a turma criada com todos os detalhes para retornar
            $stmt = $pdo->prepare("
                SELECT t.*, c.nome as curso_nome, c.categoria, c.duracao,
                       u.nome as unidade_nome, u.cidade,
                       (SELECT COUNT(*) FROM matriculas WHERE turma_id = t.id AND status != 'cancelada') as total_matriculas,
                       (SELECT COUNT(*) FROM aulas_agendadas WHERE turma_id = t.id) as total_aulas
                FROM turmas_cursos t
                JOIN cursos c ON t.curso_id = c.id
                JOIN unidades u ON t.unidade_id = u.id
                WHERE t.id = ?
            ");
            $stmt->execute([$turma_id]);
            $turma_criada = $stmt->fetch();
            
            $mensagem = 'Turma criada com sucesso!';
            if ($aulas_geradas > 0) {
                $mensagem = "Turma criada com sucesso! {$aulas_geradas} aulas foram geradas automaticamente no calendário.";
            } else {
                $mensagem = 'Turma criada com sucesso! Configure os dias da semana e horários para gerar aulas automaticamente.';
            }
            
            $response = [
                'success' => true, 
                'message' => $mensagem,
                'turma_id' => $turma_id,
                'turma' => $turma_criada,
                'aulas_geradas' => $aulas_geradas
            ];
            
            error_log("Resposta a ser enviada: " . json_encode($response));
            echo json_encode($response);
            error_log("Resposta enviada com sucesso");
            break;

        case 'PUT':
            // Atualizar turma
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
                break;
            }
            
            $turma_id = intval($data['id']);
            
            // Buscar dados atuais da turma para comparar
            $stmt = $pdo->prepare("SELECT * FROM turmas_cursos WHERE id = ?");
            $stmt->execute([$turma_id]);
            $turma_atual = $stmt->fetch();
            
            if (!$turma_atual) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Turma não encontrada']);
                break;
            }
            
            $updates = [];
            $params = [];
            
            $allowed_fields = ['nome_turma', 'instrutor', 'vagas_totais', 'vagas_disponiveis', 'data_inicio', 
                              'data_fim', 'dias_semana', 'hora_inicio', 'hora_fim', 'sala_padrao', 'status', 'ativo'];
            
            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = ?";
                    // Tratar strings vazias como null para campos opcionais
                    if (in_array($field, ['instrutor', 'dias_semana', 'hora_inicio', 'hora_fim', 'sala_padrao']) && 
                        is_string($data[$field]) && trim($data[$field]) === '') {
                        $params[] = null;
                    } else {
                        $params[] = $data[$field];
                    }
                }
            }
            
            if (empty($updates)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Nenhum campo para atualizar']);
                break;
            }
            
            $params[] = $turma_id;
            $sql = "UPDATE turmas_cursos SET " . implode(', ', $updates) . " WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            // Verificar se precisa gerar/atualizar aulas automaticamente
            $dias_semana = isset($data['dias_semana']) && !empty($data['dias_semana']) ? trim($data['dias_semana']) : $turma_atual['dias_semana'];
            $hora_inicio = isset($data['hora_inicio']) && !empty($data['hora_inicio']) ? trim($data['hora_inicio']) : $turma_atual['hora_inicio'];
            $hora_fim = isset($data['hora_fim']) && !empty($data['hora_fim']) ? trim($data['hora_fim']) : $turma_atual['hora_fim'];
            $data_inicio = isset($data['data_inicio']) ? $data['data_inicio'] : $turma_atual['data_inicio'];
            $data_fim = isset($data['data_fim']) ? $data['data_fim'] : $turma_atual['data_fim'];
            $sala_padrao = isset($data['sala_padrao']) && !empty($data['sala_padrao']) ? trim($data['sala_padrao']) : $turma_atual['sala_padrao'];
            
            // Verificar se campos relevantes para geração de aulas foram alterados
            $campos_relevantes_alterados = 
                (isset($data['dias_semana']) && $data['dias_semana'] !== $turma_atual['dias_semana']) ||
                (isset($data['hora_inicio']) && $data['hora_inicio'] !== $turma_atual['hora_inicio']) ||
                (isset($data['hora_fim']) && $data['hora_fim'] !== $turma_atual['hora_fim']) ||
                (isset($data['data_inicio']) && $data['data_inicio'] !== $turma_atual['data_inicio']) ||
                (isset($data['data_fim']) && $data['data_fim'] !== $turma_atual['data_fim']);
            
            $aulas_geradas = 0;
            $mensagem_aulas = '';
            
            // Gerar aulas automaticamente se tiver todos os dados necessários
            if (!empty($dias_semana) && !empty($hora_inicio) && !empty($hora_fim)) {
                // Verificar se já existem aulas agendadas
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM aulas_agendadas WHERE turma_id = ?");
                $stmt->execute([$turma_id]);
                $existem_aulas = $stmt->fetch()['count'] > 0;
                
                // Se campos relevantes foram alterados ou não existem aulas, gerar/atualizar
                if ($campos_relevantes_alterados || !$existem_aulas) {
                    try {
                        // Se existem aulas e os campos foram alterados, remover aulas antigas
                        if ($existem_aulas && $campos_relevantes_alterados) {
                            $stmt = $pdo->prepare("DELETE FROM aulas_agendadas WHERE turma_id = ?");
                            $stmt->execute([$turma_id]);
                            error_log("Aulas antigas removidas para turma ID: " . $turma_id);
                        }
                        
                        // Converter dias da semana de string para array de inteiros
                        $dias_semana_array = array_map('intval', explode(',', $dias_semana));
                        
                        // Gerar aulas entre data_inicio e data_fim
                        $data_inicio_obj = new DateTime($data_inicio);
                        $data_fim_obj = new DateTime($data_fim);
                        
                        $stmt_insert_aula = $pdo->prepare("
                            INSERT INTO aulas_agendadas 
                            (turma_id, data_aula, hora_inicio, hora_fim, sala, status)
                            VALUES (?, ?, ?, ?, ?, 'agendada')
                        ");
                        
                        $pdo->beginTransaction();
                        $count = 0;
                        
                        // Iterar por cada dia entre data_inicio e data_fim
                        $current_date = clone $data_inicio_obj;
                        while ($current_date <= $data_fim_obj) {
                            // Verificar se o dia da semana atual está na lista
                            $dia_semana_atual = (int)$current_date->format('w');
                            
                            if (in_array($dia_semana_atual, $dias_semana_array, true)) {
                                // Verificar se já existe aula nesta data (não deveria, mas por segurança)
                                $stmt_check = $pdo->prepare("
                                    SELECT COUNT(*) as count FROM aulas_agendadas 
                                    WHERE turma_id = ? AND data_aula = ?
                                ");
                                $stmt_check->execute([$turma_id, $current_date->format('Y-m-d')]);
                                $exists = $stmt_check->fetch(PDO::FETCH_ASSOC);
                                
                                if ($exists['count'] == 0) {
                                    $stmt_insert_aula->execute([
                                        $turma_id,
                                        $current_date->format('Y-m-d'),
                                        $hora_inicio,
                                        $hora_fim,
                                        $sala_padrao
                                    ]);
                                    $count++;
                                }
                            }
                            
                            $current_date->modify('+1 day');
                        }
                        
                        $pdo->commit();
                        $aulas_geradas = $count;
                        error_log("Aulas geradas/atualizadas automaticamente para turma ID: {$turma_id}. Total: {$count}");
                        
                        if ($count > 0) {
                            $mensagem_aulas = " e {$count} aulas foram geradas/atualizadas automaticamente no calendário!";
                        }
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        error_log("Erro ao gerar aulas automaticamente na edição: " . $e->getMessage());
                        // Não falhar a atualização da turma se a geração de aulas falhar
                        $mensagem_aulas = " (Aviso: Não foi possível gerar aulas automaticamente: " . $e->getMessage() . ")";
                    }
                }
            }
            
            $mensagem = 'Turma atualizada com sucesso';
            if ($aulas_geradas > 0) {
                $mensagem = "Turma atualizada com sucesso{$mensagem_aulas}";
            } elseif ($campos_relevantes_alterados && (empty($dias_semana) || empty($hora_inicio) || empty($hora_fim))) {
                $mensagem = 'Turma atualizada com sucesso! Configure os dias da semana e horários para gerar aulas automaticamente.';
            }
            
            echo json_encode([
                'success' => true, 
                'message' => $mensagem,
                'aulas_geradas' => $aulas_geradas
            ]);
            break;

        case 'DELETE':
            // Deletar turma
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
                break;
            }
            
            // Verificar se há matrículas
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count FROM matriculas 
                WHERE turma_id = ? AND status != 'cancelada'
            ");
            $stmt->execute([$data['id']]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                // Apenas desativar se houver matrículas
                $stmt = $pdo->prepare("UPDATE turmas_cursos SET ativo = 0 WHERE id = ?");
                $stmt->execute([$data['id']]);
                echo json_encode(['success' => true, 'message' => 'Turma desativada (há matrículas ativas)']);
            } else {
                // Deletar se não houver matrículas
                $stmt = $pdo->prepare("DELETE FROM turmas_cursos WHERE id = ?");
                $stmt->execute([$data['id']]);
                echo json_encode(['success' => true, 'message' => 'Turma excluída com sucesso']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Erro PDO na API de turmas: " . $e->getMessage());
    error_log("SQL State: " . $e->getCode());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    $message = 'Erro ao processar requisição no banco de dados.';
    
    // Mensagens mais específicas para erros comuns
    if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
        $message = 'Erro: Curso ou Unidade não encontrado. Verifique se o curso e unidade selecionados existem.';
    } elseif (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        $message = 'Erro: Já existe uma turma com estes dados.';
    } elseif (strpos($e->getMessage(), 'Data truncated') !== false) {
        $message = 'Erro: Algum dado está fora do formato esperado. Verifique datas e horários.';
    }
    
    echo json_encode([
        'success' => false, 
        'message' => $message,
        'error_details' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    error_log("Erro geral na API de turmas: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false, 
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
