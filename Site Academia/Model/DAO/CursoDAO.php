<?php
namespace Model\DAO;

/**
 * DAO para Curso
 */
class CursoDAO extends \Model\DAO\DAO {
    
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM cursos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findAll($ativo = null) {
        if ($ativo !== null) {
            $stmt = $this->pdo->prepare("SELECT * FROM cursos WHERE ativo = ? ORDER BY nome");
            $stmt->execute([$ativo]);
        } else {
            $stmt = $this->pdo->query("SELECT * FROM cursos ORDER BY nome");
        }
        return $stmt->fetchAll();
    }
    
    public function getCategorias() {
        $stmt = $this->pdo->query("SELECT DISTINCT categoria FROM cursos ORDER BY categoria");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
    
    public function insert($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO cursos (nome, categoria, descricao, duracao, valor_total, ativo) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['nome'],
            $data['categoria'],
            $data['descricao'],
            $data['duracao'],
            $data['valor_total'],
            $data['ativo'] ?? 1
        ]);
        return $this->pdo->lastInsertId();
    }
    
    public function update($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE cursos 
            SET nome = ?, categoria = ?, descricao = ?, duracao = ?, valor_total = ?, ativo = ? 
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['nome'],
            $data['categoria'],
            $data['descricao'],
            $data['duracao'],
            $data['valor_total'],
            $data['ativo'] ?? 1,
            $id
        ]);
    }
    
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM cursos WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

