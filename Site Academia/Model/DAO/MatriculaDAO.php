<?php
namespace Model\DAO;

/**
 * DAO para Matricula
 */
class MatriculaDAO extends \Model\DAO\DAO {
    
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM matriculas WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findByUsuario($usuarioId) {
        $stmt = $this->pdo->prepare("
            SELECT m.*, t.nome_turma, t.data_inicio, t.data_fim,
                   c.nome as curso_nome, u.nome as unidade_nome
            FROM matriculas m
            JOIN turmas_cursos t ON m.turma_id = t.id
            JOIN cursos c ON t.curso_id = c.id
            JOIN unidades u ON t.unidade_id = u.id
            WHERE m.usuario_id = ? AND m.ativo = 1
            ORDER BY m.data_matricula DESC
        ");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll();
    }
    
    public function findByTurma($turmaId) {
        $stmt = $this->pdo->prepare("
            SELECT m.*, u.nome as usuario_nome, u.email
            FROM matriculas m
            JOIN usuarios u ON m.usuario_id = u.id
            WHERE m.turma_id = ? AND m.ativo = 1
        ");
        $stmt->execute([$turmaId]);
        return $stmt->fetchAll();
    }
    
    public function findByUsuarioETurma($usuarioId, $turmaId) {
        $stmt = $this->pdo->prepare("SELECT * FROM matriculas WHERE usuario_id = ? AND turma_id = ? AND ativo = 1");
        $stmt->execute([$usuarioId, $turmaId]);
        return $stmt->fetch();
    }
    
    public function findAll() {
        $stmt = $this->pdo->query("
            SELECT m.*, u.nome as usuario_nome, u.email,
                   t.nome_turma, c.nome as curso_nome,
                   un.nome as unidade_nome
            FROM matriculas m
            JOIN usuarios u ON m.usuario_id = u.id
            JOIN turmas_cursos t ON m.turma_id = t.id
            JOIN cursos c ON t.curso_id = c.id
            JOIN unidades un ON t.unidade_id = un.id
            ORDER BY m.data_matricula DESC
        ");
        return $stmt->fetchAll();
    }
    
    public function insert($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO matriculas (usuario_id, turma_id, data_matricula, status, valor_pago, 
                forma_pagamento, observacoes, ativo) 
            VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['usuario_id'],
            $data['turma_id'],
            $data['status'] ?? 'pendente',
            $data['valor_pago'] ?? null,
            $data['forma_pagamento'] ?? null,
            $data['observacoes'] ?? null,
            $data['ativo'] ?? 1
        ]);
        return $this->pdo->lastInsertId();
    }
    
    public function update($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE matriculas 
            SET status = ?, valor_pago = ?, forma_pagamento = ?, observacoes = ?, ativo = ? 
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['status'] ?? 'pendente',
            $data['valor_pago'] ?? null,
            $data['forma_pagamento'] ?? null,
            $data['observacoes'] ?? null,
            $data['ativo'] ?? 1,
            $id
        ]);
    }
    
    public function delete($id) {
        $stmt = $this->pdo->prepare("UPDATE matriculas SET ativo = 0, status = 'cancelada' WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

