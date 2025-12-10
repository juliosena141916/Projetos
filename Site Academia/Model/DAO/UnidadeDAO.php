<?php
namespace Model\DAO;

/**
 * DAO para Unidade
 */
class UnidadeDAO extends \Model\DAO\DAO {
    
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM unidades WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findAll($ativo = null) {
        if ($ativo !== null) {
            $stmt = $this->pdo->prepare("SELECT * FROM unidades WHERE ativo = ? ORDER BY nome");
            $stmt->execute([$ativo]);
        } else {
            $stmt = $this->pdo->query("SELECT * FROM unidades ORDER BY nome");
        }
        return $stmt->fetchAll();
    }
    
    public function findByCidade($cidade) {
        $stmt = $this->pdo->prepare("SELECT * FROM unidades WHERE cidade = ? AND ativo = 1 ORDER BY nome");
        $stmt->execute([$cidade]);
        return $stmt->fetchAll();
    }
    
    public function insert($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO unidades (nome, cidade, endereco, telefone, horario_funcionamento, ativo) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['nome'],
            $data['cidade'],
            $data['endereco'],
            $data['telefone'] ?? null,
            $data['horario_funcionamento'] ?? null,
            $data['ativo'] ?? 1
        ]);
        return $this->pdo->lastInsertId();
    }
    
    public function update($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE unidades 
            SET nome = ?, cidade = ?, endereco = ?, telefone = ?, horario_funcionamento = ?, ativo = ? 
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['nome'],
            $data['cidade'],
            $data['endereco'],
            $data['telefone'] ?? null,
            $data['horario_funcionamento'] ?? null,
            $data['ativo'] ?? 1,
            $id
        ]);
    }
    
    public function delete($id) {
        // Verificar se há turmas usando esta unidade
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM turmas_cursos WHERE unidade_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            throw new \Exception('Não é possível deletar: existem turmas usando esta unidade');
        }
        
        $stmt = $this->pdo->prepare("DELETE FROM unidades WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

