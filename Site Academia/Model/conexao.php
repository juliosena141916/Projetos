<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'senaisp');
define('DB_NAME', 'techfit');
define('DB_CHARSET', 'utf8mb4');

/**
 * Função para obter conexão PDO
 * @return PDO Retorna uma instância de PDO
 */
function getConexao() {

    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        
        return $pdo;
        
    } catch (PDOException $e) {
        error_log("Erro de conexão com banco de dados: " . $e->getMessage());
        error_log("DSN: " . $dsn);
        error_log("User: " . DB_USER);
        throw new Exception("Erro ao conectar com o banco de dados: " . $e->getMessage());
    }
}


function verificarBancoDados() {
    try {
        // Conectar sem especificar o banco
        $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // Criar banco se não existir
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Conectar ao banco criado
        $pdo = getConexao();
        
        // Criar tabelas se não existirem
        criarTabelas($pdo);
        
        return true;
    } catch (PDOException $e) {
        error_log("Erro ao verificar/criar banco de dados: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        // Lançar exceção para ser tratada pelo código que chama
        throw new Exception("Erro ao verificar/criar banco de dados: " . $e->getMessage());
    } catch (Exception $e) {
        error_log("Erro geral ao verificar banco de dados: " . $e->getMessage());
        throw $e;
    }
}


function criarTabelas($pdo) {
    try {
        // Tabela de usuários
        $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            senha VARCHAR(255) NOT NULL,
            tipo_usuario ENUM('usuario', 'admin') NOT NULL DEFAULT 'usuario',
            data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            ativo TINYINT(1) DEFAULT 1,
            INDEX idx_email (email),
            INDEX idx_nome (nome)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Inserir usuário administrador padrão se não existir
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM usuarios WHERE tipo_usuario = 'admin'");
        $result = $stmt->fetch();
        if ($result['count'] == 0) {
            $senha_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $pdo->exec("INSERT INTO usuarios (nome, email, senha, tipo_usuario) 
                        VALUES ('Administrador', 'admin@techfit.com', '$senha_hash', 'admin')");
        }
        
        // Tabela de logs de auditoria (opcional - sem foreign key para evitar problemas)
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS logs_auditoria (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT,
                acao VARCHAR(50) NOT NULL,
                ip VARCHAR(45),
                data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_usuario (usuario_id),
                INDEX idx_data (data_hora)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela logs_auditoria: " . $e->getMessage());
            // Continuar mesmo se esta tabela falhar
        }
        
        // Tabela de tokens de autenticação (opcional - sem foreign key para evitar problemas)
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS tokens_autenticacao (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                token VARCHAR(64) NOT NULL UNIQUE,
                expira_em DATETIME NOT NULL,
                data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_token (token),
                INDEX idx_usuario (usuario_id),
                INDEX idx_expiracao (expira_em)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela tokens_autenticacao: " . $e->getMessage());
            // Continuar mesmo se esta tabela falhar
        }
        
        // Tabela de unidades
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS unidades (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                cidade VARCHAR(100) NOT NULL,
                endereco VARCHAR(255) NOT NULL,
                telefone VARCHAR(20),
                horario_funcionamento VARCHAR(100),
                ativo TINYINT(1) DEFAULT 1,
                data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_cidade (cidade),
                INDEX idx_ativo (ativo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            
            // Verificar se já existem unidades, se não, inserir
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM unidades");
            $result = $stmt->fetch();
            if ($result['count'] == 0) {
                $pdo->exec("INSERT INTO unidades (nome, cidade, endereco, telefone, horario_funcionamento) VALUES
                    ('TechFit Limeira Centro', 'Limeira', 'Av. Campinas, 1234 - Centro, Limeira - SP', '(19) 3451-1234', 'Segunda a Sexta: 6h às 23h | Sábado: 7h às 20h | Domingo: 8h às 18h'),
                    ('TechFit Limeira Norte', 'Limeira', 'Rua Dr. Trajano de Barros Camargo, 567 - Jardim Nova Limeira, Limeira - SP', '(19) 3451-5678', 'Segunda a Sexta: 6h às 23h | Sábado: 7h às 20h | Domingo: 8h às 18h'),
                    ('TechFit Campinas Centro', 'Campinas', 'Av. Francisco Glicério, 890 - Centro, Campinas - SP', '(19) 3234-1111', 'Segunda a Sexta: 5h30 às 23h | Sábado: 6h às 21h | Domingo: 7h às 19h'),
                    ('TechFit Campinas Cambuí', 'Campinas', 'Rua Barão de Jaguara, 2345 - Cambuí, Campinas - SP', '(19) 3234-2222', 'Segunda a Sexta: 5h30 às 23h | Sábado: 6h às 21h | Domingo: 7h às 19h'),
                    ('TechFit Campinas Taquaral', 'Campinas', 'Av. Nossa Sra. de Fátima, 678 - Jardim Nossa Sra. Auxiliadora, Campinas - SP', '(19) 3234-3333', 'Segunda a Sexta: 5h30 às 23h | Sábado: 6h às 21h | Domingo: 7h às 19h'),
                    ('TechFit Cordeirópolis', 'Cordeirópolis', 'Rua XV de Novembro, 456 - Centro, Cordeirópolis - SP', '(19) 3546-7890', 'Segunda a Sexta: 6h às 22h | Sábado: 7h às 19h | Domingo: 8h às 17h'),
                    ('TechFit Paulínia Centro', 'Paulínia', 'Av. José Paulino, 123 - Centro, Paulínia - SP', '(19) 3874-1234', 'Segunda a Sexta: 6h às 23h | Sábado: 7h às 20h | Domingo: 8h às 18h'),
                    ('TechFit Paulínia Betel', 'Paulínia', 'Rua dos Estudantes, 789 - Betel, Paulínia - SP', '(19) 3874-5678', 'Segunda a Sexta: 6h às 23h | Sábado: 7h às 20h | Domingo: 8h às 18h'),
                    ('TechFit Iracemápolis', 'Iracemápolis', 'Av. da República, 321 - Centro, Iracemápolis - SP', '(19) 3456-9012', 'Segunda a Sexta: 6h às 22h | Sábado: 7h às 19h | Domingo: 8h às 17h')");
            }
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela unidades: " . $e->getMessage());
            // Continuar mesmo se esta tabela falhar
        }
        
        // Tabela de cursos
        try {
            // Verificar se a tabela existe
            $stmt = $pdo->query("SHOW TABLES LIKE 'cursos'");
            $tableExists = $stmt->rowCount() > 0;
            
            if ($tableExists) {
                // Verificar se tem estrutura antiga (duracao_min/duracao_max)
                $stmt = $pdo->query("SHOW COLUMNS FROM cursos LIKE 'duracao_min'");
                if ($stmt->rowCount() > 0) {
                    // Atualizar estrutura antiga
                    try {
                        $pdo->exec("ALTER TABLE cursos DROP COLUMN duracao_min");
                    } catch (PDOException $e) {}
                    try {
                        $pdo->exec("ALTER TABLE cursos DROP COLUMN duracao_max");
                    } catch (PDOException $e) {}
                    try {
                        $pdo->exec("ALTER TABLE cursos ADD COLUMN duracao INT NOT NULL DEFAULT 8 AFTER descricao");
                    } catch (PDOException $e) {}
                }
                // Verificar se tem valor_mensal
                $stmt = $pdo->query("SHOW COLUMNS FROM cursos LIKE 'valor_mensal'");
                if ($stmt->rowCount() > 0) {
                    try {
                        $pdo->exec("ALTER TABLE cursos DROP COLUMN valor_mensal");
                    } catch (PDOException $e) {}
                    try {
                        $pdo->exec("ALTER TABLE cursos ADD COLUMN valor_total DECIMAL(10, 2) NOT NULL AFTER duracao");
                    } catch (PDOException $e) {}
                }
            } else {
                // Criar tabela com estrutura nova
                $pdo->exec("CREATE TABLE IF NOT EXISTS cursos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nome VARCHAR(255) NOT NULL,
                    categoria VARCHAR(100) NOT NULL,
                    descricao TEXT NOT NULL,
                    duracao INT NOT NULL,
                    valor_total DECIMAL(10, 2) NOT NULL,
                    ativo TINYINT(1) DEFAULT 1,
                    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_categoria (categoria),
                    INDEX idx_ativo (ativo)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            }
            
            // Verificar se já existem cursos, se não, inserir
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM cursos");
            $result = $stmt->fetch();
            if ($result['count'] == 0) {
                $pdo->exec("INSERT INTO cursos (nome, categoria, descricao, duracao, valor_total) VALUES
                    ('Treinamento Funcional Intensivo', 'Condicionamento Físico', 'Curso ensinando movimentos funcionais, mobilidade e força, com progressão semanal.', 12, 315.50),
                    ('Curso de Musculação para Iniciantes', 'Condicionamento Físico', 'Ensina postura correta, técnicas de execução, como montar treino e evitar lesões. Muito procurado por iniciantes.', 10, 249.90),
                    ('Curso de HIIT e Emagrecimento Acelerado', 'Condicionamento Físico', 'Focado em treinos de alta intensidade, queima calórica e estratégias de perda de gordura.', 8, 340.00),
                    ('Yoga Terapêutica', 'Saúde e Bem-estar', 'Foco em postura, alongamento, respiração, relaxamento e redução de estresse. Do básico ao avançado.', 14, 210.50),
                    ('Pilates Solo para Correção Postural', 'Saúde e Bem-estar', 'Reforço de core, estabilidade, consciência corporal; ideal para público com dores nas costas.', 11, 295.00),
                    ('Meditação e Mindfulness', 'Saúde e Bem-estar', 'Curso semanal focado em controle de ansiedade, foco e equilíbrio emocional.', 9, 205.90),
                    ('Curso de Defesa Pessoal / Krav Maga', 'Especializado', 'Atrai público jovem e adultos que buscam segurança e preparo físico.', 16, 330.00),
                    ('Curso de Mobilidade Articular e Flexibilidade', 'Especializado', 'Atende desde idosos até atletas, com aulas focadas em amplitude de movimento e prevenção de lesões.', 13, 275.50),
                    ('Danças Fitness (Zumba, FitDance)', 'Especializado', 'Turmas fechadas com acompanhamento especializado.', 10, 220.00)");
            }
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela cursos: " . $e->getMessage());
            // Continuar mesmo se esta tabela falhar
        }
        
        // Tabela de planos
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS planos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                descricao TEXT,
                valor_mensal DECIMAL(10, 2) NOT NULL,
                acesso_academia TINYINT(1) DEFAULT 1,
                acesso_musculacao TINYINT(1) DEFAULT 1,
                acesso_todas_unidades TINYINT(1) DEFAULT 0,
                acesso_todos_cursos TINYINT(1) DEFAULT 0,
                quantidade_cursos INT DEFAULT 0,
                aulas_grupais_ilimitadas TINYINT(1) DEFAULT 0,
                personal_trainer TINYINT(1) DEFAULT 0,
                nutricionista TINYINT(1) DEFAULT 0,
                avaliacao_fisica TINYINT(1) DEFAULT 0,
                app_exclusivo TINYINT(1) DEFAULT 0,
                desconto_loja TINYINT(1) DEFAULT 0,
                ativo TINYINT(1) DEFAULT 1,
                data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_ativo (ativo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            
            // Adicionar novos campos se não existirem
            try {
                $stmt = $pdo->query("SHOW COLUMNS FROM planos LIKE 'acesso_academia'");
                if ($stmt->rowCount() == 0) {
                    $pdo->exec("ALTER TABLE planos ADD COLUMN acesso_academia TINYINT(1) DEFAULT 1 AFTER valor_mensal");
                    $pdo->exec("ALTER TABLE planos ADD COLUMN acesso_musculacao TINYINT(1) DEFAULT 1 AFTER acesso_academia");
                    $pdo->exec("ALTER TABLE planos ADD COLUMN quantidade_cursos INT DEFAULT 0 AFTER acesso_todos_cursos");
                }
            } catch (PDOException $e) {}
            
            // Verificar se já existem planos, se não, inserir
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM planos");
            $result = $stmt->fetch();
            if ($result['count'] == 0) {
                $pdo->exec("INSERT INTO planos (nome, descricao, valor_mensal, acesso_academia, acesso_musculacao, acesso_todas_unidades, acesso_todos_cursos, quantidade_cursos, aulas_grupais_ilimitadas, personal_trainer, nutricionista, avaliacao_fisica, app_exclusivo, desconto_loja) VALUES
                    ('Plano Básico', 'Ideal para quem está começando na academia', 89.90, 1, 1, 0, 0, 1, 1, 0, 0, 1, 0, 0),
                    ('Plano Premium', 'Para quem quer mais benefícios e flexibilidade', 149.90, 1, 1, 1, 0, 4, 1, 0, 0, 1, 1, 0),
                    ('Plano VIP', 'Acesso completo a todos os recursos e benefícios', 249.90, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1)");
            }
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela planos: " . $e->getMessage());
            // Continuar mesmo se esta tabela falhar
        }
        
        // Tabela de turmas_cursos
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS turmas_cursos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                curso_id INT NOT NULL,
                unidade_id INT NOT NULL,
                nome_turma VARCHAR(100) NOT NULL,
                instrutor VARCHAR(100),
                vagas_totais INT NOT NULL DEFAULT 20,
                vagas_disponiveis INT NOT NULL DEFAULT 20,
                data_inicio DATE NOT NULL,
                data_fim DATE NOT NULL,
                dias_semana VARCHAR(20) COMMENT 'Dias da semana separados por vírgula (0=Dom, 1=Seg, 2=Ter, 3=Qua, 4=Qui, 5=Sex, 6=Sab)',
                hora_inicio TIME COMMENT 'Horário padrão de início das aulas',
                hora_fim TIME COMMENT 'Horário padrão de fim das aulas',
                sala_padrao VARCHAR(50) COMMENT 'Sala padrão para as aulas da turma',
                status ENUM('planejada', 'em_andamento', 'concluida', 'cancelada') DEFAULT 'planejada',
                ativo TINYINT(1) DEFAULT 1,
                data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
                FOREIGN KEY (unidade_id) REFERENCES unidades(id) ON DELETE CASCADE,
                INDEX idx_curso (curso_id),
                INDEX idx_unidade (unidade_id),
                INDEX idx_status (status),
                INDEX idx_data_inicio (data_inicio)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela turmas_cursos: " . $e->getMessage());
            // Continuar mesmo se esta tabela falhar
        }
        
        // Tabela de aulas_agendadas
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS aulas_agendadas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                turma_id INT NOT NULL,
                data_aula DATE NOT NULL,
                hora_inicio TIME NOT NULL,
                hora_fim TIME NOT NULL,
                sala VARCHAR(50),
                observacoes TEXT,
                status ENUM('agendada', 'realizada', 'cancelada', 'remarcada') DEFAULT 'agendada',
                ativo TINYINT(1) DEFAULT 1,
                data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (turma_id) REFERENCES turmas_cursos(id) ON DELETE CASCADE,
                INDEX idx_turma (turma_id),
                INDEX idx_data (data_aula),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela aulas_agendadas: " . $e->getMessage());
            // Continuar mesmo se esta tabela falhar
        }
        
        // Tabela de matrículas
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS matriculas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                turma_id INT NOT NULL,
                data_matricula DATETIME DEFAULT CURRENT_TIMESTAMP,
                status ENUM('pendente', 'confirmada', 'cancelada', 'concluida') DEFAULT 'pendente',
                valor_pago DECIMAL(10, 2),
                forma_pagamento VARCHAR(50),
                observacoes TEXT,
                ativo TINYINT(1) DEFAULT 1,
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
                FOREIGN KEY (turma_id) REFERENCES turmas_cursos(id) ON DELETE CASCADE,
                INDEX idx_usuario (usuario_id),
                INDEX idx_turma (turma_id),
                INDEX idx_status (status),
                UNIQUE KEY unique_matricula (usuario_id, turma_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela matriculas: " . $e->getMessage());
            // Continuar mesmo se esta tabela falhar
        }
        
        // Tabela de frequência em aulas
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS frequencia_aulas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                aula_id INT NOT NULL,
                data_presenca DATETIME DEFAULT CURRENT_TIMESTAMP,
                status ENUM('presente', 'ausente', 'justificado') DEFAULT 'presente',
                observacoes TEXT,
                registrado_por INT,
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
                FOREIGN KEY (aula_id) REFERENCES aulas_agendadas(id) ON DELETE CASCADE,
                FOREIGN KEY (registrado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
                INDEX idx_usuario (usuario_id),
                INDEX idx_aula (aula_id),
                INDEX idx_data (data_presenca),
                UNIQUE KEY unique_frequencia (usuario_id, aula_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela frequencia_aulas: " . $e->getMessage());
        }
        
        // Tabela de lista de espera
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS lista_espera (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                turma_id INT NOT NULL,
                data_inscricao DATETIME DEFAULT CURRENT_TIMESTAMP,
                prioridade INT DEFAULT 0,
                status ENUM('ativa', 'atendida', 'cancelada') DEFAULT 'ativa',
                notificado TINYINT(1) DEFAULT 0,
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
                FOREIGN KEY (turma_id) REFERENCES turmas_cursos(id) ON DELETE CASCADE,
                INDEX idx_usuario (usuario_id),
                INDEX idx_turma (turma_id),
                INDEX idx_status (status),
                INDEX idx_prioridade (prioridade),
                UNIQUE KEY unique_lista_espera (usuario_id, turma_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela lista_espera: " . $e->getMessage());
        }
        
        // Tabela de avaliações físicas
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS avaliacoes_fisicas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                avaliador_id INT,
                data_avaliacao DATE NOT NULL,
                peso DECIMAL(5, 2),
                altura DECIMAL(3, 2),
                imc DECIMAL(4, 2),
                percentual_gordura DECIMAL(4, 2),
                percentual_massa_magra DECIMAL(4, 2),
                circunferencia_peito DECIMAL(5, 2),
                circunferencia_cintura DECIMAL(5, 2),
                circunferencia_quadril DECIMAL(5, 2),
                circunferencia_braco DECIMAL(4, 2),
                circunferencia_coxa DECIMAL(4, 2),
                pressao_arterial_sistolica INT,
                pressao_arterial_diastolica INT,
                frequencia_cardiaca_repouso INT,
                flexibilidade_cm DECIMAL(5, 2),
                forca_abdominal INT,
                resistencia_cardiovascular VARCHAR(50),
                observacoes TEXT,
                proxima_avaliacao DATE,
                ativo TINYINT(1) DEFAULT 1,
                data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
                FOREIGN KEY (avaliador_id) REFERENCES usuarios(id) ON DELETE SET NULL,
                INDEX idx_usuario (usuario_id),
                INDEX idx_data (data_avaliacao),
                INDEX idx_proxima (proxima_avaliacao)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela avaliacoes_fisicas: " . $e->getMessage());
        }
        
        // Tabela de notificações
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS notificacoes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT,
                turma_id INT,
                tipo ENUM('alteracao_horario', 'vaga_disponivel', 'lembrete_aula', 'avaliacao_pendente', 'outro') NOT NULL,
                titulo VARCHAR(255) NOT NULL,
                mensagem TEXT NOT NULL,
                lida TINYINT(1) DEFAULT 0,
                data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
                data_leitura DATETIME,
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
                FOREIGN KEY (turma_id) REFERENCES turmas_cursos(id) ON DELETE CASCADE,
                INDEX idx_usuario (usuario_id),
                INDEX idx_lida (lida),
                INDEX idx_data (data_criacao)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela notificacoes: " . $e->getMessage());
        }
        
        // Tabela de mensagens de suporte
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS mensagens_suporte (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                assunto VARCHAR(200) NOT NULL,
                mensagem TEXT NOT NULL,
                tipo ENUM('duvida', 'problema', 'sugestao', 'outro') DEFAULT 'outro',
                status ENUM('aberta', 'em_atendimento', 'resolvida', 'fechada') DEFAULT 'aberta',
                resposta TEXT,
                respondido_por INT,
                data_resposta DATETIME,
                data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
                data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
                FOREIGN KEY (respondido_por) REFERENCES usuarios(id) ON DELETE SET NULL,
                INDEX idx_usuario (usuario_id),
                INDEX idx_status (status),
                INDEX idx_data_criacao (data_criacao)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela mensagens_suporte: " . $e->getMessage());
        }
        
        // Inserir turmas de teste (se não existirem)
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM turmas_cursos");
        $result = $stmt->fetch();
        if ($result['count'] == 0) {
            // Assumindo que o curso_id 1 e unidade_id 1 existem
            $pdo->exec("INSERT INTO turmas_cursos (curso_id, unidade_id, nome_turma, instrutor, vagas_totais, vagas_disponiveis, data_inicio, data_fim, dias_semana, hora_inicio, hora_fim, sala_padrao, status) VALUES (1, 1, 'Turma Noturna - Janeiro 2025', 'Prof. Carlos Silva', 20, 20, '2025-01-13', '2025-04-07', '1,3,5', '19:00:00', '20:30:00', 'Sala 1', 'planejada')");
            
            // Turma de Teste Adicionada
            $pdo->exec("INSERT INTO turmas_cursos (curso_id, unidade_id, nome_turma, instrutor, vagas_totais, vagas_disponiveis, data_inicio, data_fim, dias_semana, hora_inicio, hora_fim, sala_padrao, status) VALUES (2, 2, 'Turma Teste - Pilates Manhã', 'Prof. Ana Paula', 15, 15, '2025-12-01', '2026-03-30', '2,4', '09:00:00', '10:00:00', 'Sala Pilates', 'em_andamento')");
        }

        return true;
    } catch (PDOException $e) {
        error_log("Erro ao criar tabelas: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        // Lançar exceção para ser tratada pelo código que chama
        throw new Exception("Erro ao criar tabelas: " . $e->getMessage());
    }
}

?>

