<?php
namespace Model\DAO;

/**
 * DAO para Turma
 */
class TurmaDAO extends \Model\DAO\DAO {
    
    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT t.*, c.nome as curso_nome, c.categoria, c.duracao,
                   u.nome as unidade_nome, u.cidade
            FROM turmas_cursos t
            JOIN cursos c ON t.curso_id = c.id
            JOIN unidades u ON t.unidade_id = u.id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findAll($ativo = null) {
        if ($ativo !== null) {
            $stmt = $this->pdo->prepare("
                SELECT t.*, c.nome as curso_nome, c.categoria,
                       u.nome as unidade_nome, u.cidade
                FROM turmas_cursos t
                JOIN cursos c ON t.curso_id = c.id
                JOIN unidades u ON t.unidade_id = u.id
                WHERE t.ativo = ?
                ORDER BY t.data_inicio DESC, t.nome_turma ASC
            ");
            $stmt->execute([$ativo]);
        } else {
            $stmt = $this->pdo->query("
                SELECT t.*, c.nome as curso_nome, c.categoria,
                       u.nome as unidade_nome, u.cidade
                FROM turmas_cursos t
                JOIN cursos c ON t.curso_id = c.id
                JOIN unidades u ON t.unidade_id = u.id
                ORDER BY t.data_inicio DESC, t.nome_turma ASC
            ");
        }
        return $stmt->fetchAll();
    }
    
    public function insert($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO turmas_cursos (curso_id, unidade_id, nome_turma, instrutor, vagas_totais, 
                vagas_disponiveis, data_inicio, data_fim, dias_semana, hora_inicio, hora_fim, 
                sala_padrao, status, ativo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['curso_id'],
            $data['unidade_id'],
            $data['nome_turma'],
            $data['instrutor'] ?? null,
            $data['vagas_totais'],
            $data['vagas_disponiveis'] ?? $data['vagas_totais'],
            $data['data_inicio'],
            $data['data_fim'],
            $data['dias_semana'] ?? null,
            $data['hora_inicio'] ?? null,
            $data['hora_fim'] ?? null,
            $data['sala_padrao'] ?? null,
            $data['status'] ?? 'planejada',
            $data['ativo'] ?? 1
        ]);
        return $this->pdo->lastInsertId();
    }
    
    public function update($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE turmas_cursos 
            SET curso_id = ?, unidade_id = ?, nome_turma = ?, instrutor = ?, vagas_totais = ?, 
                vagas_disponiveis = ?, data_inicio = ?, data_fim = ?, dias_semana = ?, 
                hora_inicio = ?, hora_fim = ?, sala_padrao = ?, status = ?, ativo = ? 
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['curso_id'],
            $data['unidade_id'],
            $data['nome_turma'],
            $data['instrutor'] ?? null,
            $data['vagas_totais'],
            $data['vagas_disponiveis'],
            $data['data_inicio'],
            $data['data_fim'],
            $data['dias_semana'] ?? null,
            $data['hora_inicio'] ?? null,
            $data['hora_fim'] ?? null,
            $data['sala_padrao'] ?? null,
            $data['status'] ?? 'planejada',
            $data['ativo'] ?? 1,
            $id
        ]);
    }
    
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM turmas_cursos WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function atualizarVagas($id, $incremento) {
        $stmt = $this->pdo->prepare("UPDATE turmas_cursos SET vagas_disponiveis = vagas_disponiveis + ? WHERE id = ?");
        return $stmt->execute([$incremento, $id]);
    }
}

