<?php
namespace Model\DAO;

/**
 * DAO para Usuario
 */
class UsuarioDAO extends DAO {
    
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function findByNome($nome) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE nome = ?");
        $stmt->execute([$nome]);
        return $stmt->fetch();
    }
    
    public function findByEmailOuNome($login) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = ? OR nome = ?");
        $stmt->execute([$login, $login]);
        return $stmt->fetch();
    }
    
    public function findAll($ativo = null) {
        if ($ativo !== null) {
            $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE ativo = ? ORDER BY data_cadastro DESC");
            $stmt->execute([$ativo]);
        } else {
            $stmt = $this->pdo->query("SELECT * FROM usuarios ORDER BY data_cadastro DESC");
        }
        return $stmt->fetchAll();
    }
    
    public function insert($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO usuarios (nome, email, senha, tipo_usuario, data_cadastro) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['nome'],
            $data['email'],
            $data['senha'],
            $data['tipo_usuario'] ?? 'usuario'
        ]);
        return $this->pdo->lastInsertId();
    }
    
    public function update($id, $data) {
        if (isset($data['senha']) && !empty($data['senha'])) {
            $stmt = $this->pdo->prepare("
                UPDATE usuarios 
                SET nome = ?, email = ?, senha = ?, tipo_usuario = ?, ativo = ? 
                WHERE id = ?
            ");
            $stmt->execute([
                $data['nome'],
                $data['email'],
                $data['senha'],
                $data['tipo_usuario'],
                $data['ativo'] ?? 1,
                $id
            ]);
        } else {
            $stmt = $this->pdo->prepare("
                UPDATE usuarios 
                SET nome = ?, email = ?, tipo_usuario = ?, ativo = ? 
                WHERE id = ?
            ");
            $stmt->execute([
                $data['nome'],
                $data['email'],
                $data['tipo_usuario'],
                $data['ativo'] ?? 1,
                $id
            ]);
        }
        return true;
    }
    
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function emailExists($email, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$email, $excludeId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
        }
        return $stmt->fetch() !== false;
    }
}

