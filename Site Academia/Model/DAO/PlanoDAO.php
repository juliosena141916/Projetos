<?php
namespace Model\DAO;

/**
 * DAO para Plano
 */
class PlanoDAO extends \Model\DAO\DAO {
    
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM planos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findAll($ativo = null) {
        if ($ativo !== null) {
            $stmt = $this->pdo->prepare("SELECT * FROM planos WHERE ativo = ? ORDER BY valor_mensal ASC");
            $stmt->execute([$ativo]);
        } else {
            $stmt = $this->pdo->query("SELECT * FROM planos ORDER BY valor_mensal ASC");
        }
        return $stmt->fetchAll();
    }
    
    public function insert($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO planos (nome, descricao, valor_mensal, acesso_academia, acesso_musculacao, 
                acesso_todas_unidades, acesso_todos_cursos, quantidade_cursos, aulas_grupais_ilimitadas, 
                personal_trainer, nutricionista, avaliacao_fisica, app_exclusivo, desconto_loja, ativo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['nome'],
            $data['descricao'] ?? null,
            $data['valor_mensal'],
            $data['acesso_academia'] ?? 1,
            $data['acesso_musculacao'] ?? 1,
            $data['acesso_todas_unidades'] ?? 0,
            $data['acesso_todos_cursos'] ?? 0,
            $data['quantidade_cursos'] ?? 0,
            $data['aulas_grupais_ilimitadas'] ?? 0,
            $data['personal_trainer'] ?? 0,
            $data['nutricionista'] ?? 0,
            $data['avaliacao_fisica'] ?? 0,
            $data['app_exclusivo'] ?? 0,
            $data['desconto_loja'] ?? 0,
            $data['ativo'] ?? 1
        ]);
        return $this->pdo->lastInsertId();
    }
    
    public function update($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE planos 
            SET nome = ?, descricao = ?, valor_mensal = ?, acesso_academia = ?, acesso_musculacao = ?, 
                acesso_todas_unidades = ?, acesso_todos_cursos = ?, quantidade_cursos = ?, 
                aulas_grupais_ilimitadas = ?, personal_trainer = ?, nutricionista = ?, 
                avaliacao_fisica = ?, app_exclusivo = ?, desconto_loja = ?, ativo = ? 
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['nome'],
            $data['descricao'] ?? null,
            $data['valor_mensal'],
            $data['acesso_academia'] ?? 1,
            $data['acesso_musculacao'] ?? 1,
            $data['acesso_todas_unidades'] ?? 0,
            $data['acesso_todos_cursos'] ?? 0,
            $data['quantidade_cursos'] ?? 0,
            $data['aulas_grupais_ilimitadas'] ?? 0,
            $data['personal_trainer'] ?? 0,
            $data['nutricionista'] ?? 0,
            $data['avaliacao_fisica'] ?? 0,
            $data['app_exclusivo'] ?? 0,
            $data['desconto_loja'] ?? 0,
            $data['ativo'] ?? 1,
            $id
        ]);
    }
    
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM planos WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

