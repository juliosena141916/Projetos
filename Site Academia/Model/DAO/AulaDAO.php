<?php
namespace Model\DAO;

/**
 * DAO para Aula
 */
class AulaDAO extends \Model\DAO\DAO {
    
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM aulas_agendadas WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findByTurma($turmaId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM aulas_agendadas 
            WHERE turma_id = ? AND ativo = 1
            ORDER BY data_aula, hora_inicio
        ");
        $stmt->execute([$turmaId]);
        return $stmt->fetchAll();
    }
    
    public function findAll($ativo = null) {
        if ($ativo !== null) {
            $stmt = $this->pdo->prepare("
                SELECT * FROM aulas_agendadas 
                WHERE ativo = ? 
                ORDER BY data_aula DESC, hora_inicio DESC
            ");
            $stmt->execute([$ativo]);
        } else {
            $stmt = $this->pdo->query("
                SELECT * FROM aulas_agendadas 
                ORDER BY data_aula DESC, hora_inicio DESC
            ");
        }
        return $stmt->fetchAll();
    }
    
    public function insert($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO aulas_agendadas (turma_id, data_aula, hora_inicio, hora_fim, sala, observacoes, status, ativo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['turma_id'],
            $data['data_aula'],
            $data['hora_inicio'],
            $data['hora_fim'],
            $data['sala'] ?? null,
            $data['observacoes'] ?? null,
            $data['status'] ?? 'agendada',
            $data['ativo'] ?? 1
        ]);
        return $this->pdo->lastInsertId();
    }
    
    public function update($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE aulas_agendadas 
            SET turma_id = ?, data_aula = ?, hora_inicio = ?, hora_fim = ?, sala = ?, 
                observacoes = ?, status = ?, ativo = ? 
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['turma_id'],
            $data['data_aula'],
            $data['hora_inicio'],
            $data['hora_fim'],
            $data['sala'] ?? null,
            $data['observacoes'] ?? null,
            $data['status'] ?? 'agendada',
            $data['ativo'] ?? 1,
            $id
        ]);
    }
    
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM aulas_agendadas WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function deleteByTurma($turmaId) {
        $stmt = $this->pdo->prepare("DELETE FROM aulas_agendadas WHERE turma_id = ?");
        return $stmt->execute([$turmaId]);
    }
}

