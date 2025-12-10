<?php
namespace Controller;

use Model\Aula;
use Model\Turma;
use Exception;

class AulaController {
    public static function jsonResponse($success, $message, $data = null, $code = 200) {
        http_response_code($code);
        echo json_encode([
            "success" => $success,
            "message" => $message,
            "data" => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private static function checkAdmin() {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
            self::jsonResponse(false, 'Acesso negado. Faça login como administrador.', null, 403);
        }
    }

    public static function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $aulaModel = new Aula();
        $turmaModel = new Turma();

        try {
            switch ($method) {
                case 'GET':
                    // GET é público (não requer admin)
                    if (isset($_GET['turma_id'])) {
                        $turma_id = intval($_GET['turma_id']);
                        $aulas = $aulaModel->findByTurma($turma_id);
                        self::jsonResponse(true, 'Aulas listadas com sucesso', ['aulas' => $aulas]);
                    } elseif (isset($_GET['id'])) {
                        $aula = $aulaModel->findById($_GET['id']);
                        if ($aula) {
                            self::jsonResponse(true, 'Aula encontrada', ['aula' => $aula->toArray()]);
                        } else {
                            self::jsonResponse(false, 'Aula não encontrada', null, 404);
                        }
                    } else {
                        // Listar todas requer autenticação
                        if (!isset($_SESSION['usuario_id'])) {
                            self::jsonResponse(false, 'Usuário não autenticado', null, 401);
                        }
                        $aulas = $aulaModel->findAll(1);
                        self::jsonResponse(true, 'Aulas listadas com sucesso', ['aulas' => $aulas]);
                    }
                    break;

                case 'POST':
                    self::checkAdmin(); // POST requer admin
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        self::jsonResponse(false, 'JSON inválido: ' . json_last_error_msg(), null, 400);
                    }

                    // Verificar se é criação em lote
                    if (isset($data['aulas']) && is_array($data['aulas'])) {
                        $count = 0;
                        foreach ($data['aulas'] as $aulaData) {
                            if (isset($aulaData['turma_id'], $aulaData['data_aula'], $aulaData['hora_inicio'], $aulaData['hora_fim'])) {
                                $aula = new Aula();
                                $aula->create($aulaData);
                                $count++;
                            }
                        }
                        self::jsonResponse(true, "$count aulas criadas com sucesso", ['count' => $count]);
                    } else {
                        // Criar aula individual
                        if (!isset($data['turma_id'], $data['data_aula'], $data['hora_inicio'], $data['hora_fim'])) {
                            self::jsonResponse(false, 'Dados incompletos', null, 400);
                        }

                        $aula = new Aula();
                        $aulaId = $aula->create($data);
                        self::jsonResponse(true, 'Aula criada com sucesso', ['aula_id' => $aulaId]);
                    }
                    break;

                case 'PUT':
                    self::checkAdmin(); // PUT requer admin
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        self::jsonResponse(false, 'JSON inválido: ' . json_last_error_msg(), null, 400);
                    }

                    if (!isset($data['id'])) {
                        self::jsonResponse(false, 'ID não fornecido', null, 400);
                    }

                    $aula = $aulaModel->findById($data['id']);
                    if (!$aula) {
                        self::jsonResponse(false, 'Aula não encontrada', null, 404);
                    }

                    $updateData = [];
                    $allowedFields = ['data_aula', 'hora_inicio', 'hora_fim', 'sala', 'observacoes', 'status', 'ativo'];
                    foreach ($allowedFields as $field) {
                        if (isset($data[$field])) {
                            $updateData[$field] = $data[$field];
                        }
                    }

                    if (empty($updateData)) {
                        self::jsonResponse(false, 'Nenhum campo para atualizar', null, 400);
                    }

                    $result = $aulaModel->update($data['id'], $updateData);
                    if ($result) {
                        self::jsonResponse(true, 'Aula atualizada com sucesso');
                    } else {
                        self::jsonResponse(false, 'Erro ao atualizar aula', null, 500);
                    }
                    break;

                case 'DELETE':
                    self::checkAdmin(); // DELETE requer admin
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        self::jsonResponse(false, 'JSON inválido: ' . json_last_error_msg(), null, 400);
                    }

                    if (!isset($data['id'])) {
                        self::jsonResponse(false, 'ID não fornecido', null, 400);
                    }

                    $result = $aulaModel->delete($data['id']);
                    if ($result) {
                        self::jsonResponse(true, 'Aula deletada com sucesso');
                    } else {
                        self::jsonResponse(false, 'Erro ao deletar aula', null, 500);
                    }
                    break;

                default:
                    self::jsonResponse(false, 'Método não permitido', null, 405);
            }
        } catch (Exception $e) {
            error_log("Erro no AulaController: " . $e->getMessage());
            self::jsonResponse(false, 'Erro interno do servidor: ' . $e->getMessage(), null, 500);
        }
    }
}

