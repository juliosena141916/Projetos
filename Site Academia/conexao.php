<?php
// Configurações do banco de dados
$host = "localhost";
$usuario = "root";
$senha = "senaisp";
$banco = "ACADEMIA";

// Configurações de charset
$charset = "utf8mb4";

try {
    // Criar conexão usando mysqli
    $conn = new mysqli($host, $usuario, $senha, $banco);
    
    // Verificar conexão
    if ($conn->connect_error) {
        throw new Exception("Falha na conexão: " . $conn->connect_error);
    }
    
    // Definir charset
    if (!$conn->set_charset($charset)) {
        throw new Exception("Erro ao definir charset: " . $conn->error);
    }
    
    // Configurar timezone
    $conn->query("SET time_zone = '-03:00'");
    
} catch (Exception $e) {
    // Log do erro (em produção, usar um sistema de log adequado)
    error_log("Erro de conexão com banco de dados: " . $e->getMessage());
    
    // Em desenvolvimento, mostrar erro
    if (ini_get('display_errors')) {
        die("Erro de conexão: " . $e->getMessage());
    } else {
        die("Erro interno do servidor. Tente novamente mais tarde.");
    }
}

// Função para fechar conexão
function fecharConexao($conn) {
    if ($conn) {
        $conn->close();
    }
}

// Função para executar query com tratamento de erro
function executarQuery($conn, $sql, $params = null) {
    try {
        if ($params) {
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro na preparação da query: " . $conn->error);
            }
            
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro na execução da query: " . $stmt->error);
            }
            
            return $stmt;
        } else {
            $result = $conn->query($sql);
            if (!$result) {
                throw new Exception("Erro na query: " . $conn->error);
            }
            return $result;
        }
    } catch (Exception $e) {
        error_log("Erro na query: " . $e->getMessage());
        throw $e;
    }
}
?>