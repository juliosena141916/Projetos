<?php
namespace Controller;

use Model\Usuario;
use Model\Matricula;
use Model\Database;

/**
 * Controller para gerenciamento de usuários
 */
class UsuarioController {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    public function listar() {
        $usuario = new Usuario();
        return $usuario->findAll();
    }
    
    public function buscar($id) {
        $usuario = new Usuario();
        return $usuario->findById($id);
    }
    
    public function criar($dados) {
        $usuario = new Usuario();
        $usuario->setNome($dados['nome'])
                ->setEmail($dados['email'])
                ->setSenha($dados['senha'])
                ->setTipoUsuario($dados['tipo_usuario'] ?? 'usuario')
                ->setAtivo($dados['ativo'] ?? 1);
        
        // Verificar se email já existe
        if ($usuario->findByEmail($dados['email'])) {
            throw new \Exception('Email já cadastrado');
        }
        
        return $usuario->save();
    }
    
    public function atualizar($id, $dados) {
        $usuario = new Usuario();
        $usuario->findById($id);
        
        if (!$usuario->getId()) {
            throw new \Exception('Usuário não encontrado');
        }
        
        // Verificar se email já existe (exceto para o usuário atual)
        if (isset($dados['email'])) {
            $usuarioExistente = $usuario->findByEmail($dados['email']);
            if ($usuarioExistente && $usuarioExistente->getId() != $id) {
                throw new \Exception('Email já cadastrado');
            }
        }
        
        if (isset($dados['nome'])) $usuario->setNome($dados['nome']);
        if (isset($dados['email'])) $usuario->setEmail($dados['email']);
        if (isset($dados['senha']) && !empty($dados['senha'])) {
            $usuario->setSenha($dados['senha']);
        }
        if (isset($dados['tipo_usuario'])) $usuario->setTipoUsuario($dados['tipo_usuario']);
        if (isset($dados['ativo'])) $usuario->setAtivo($dados['ativo']);
        
        return $usuario->save();
    }
    
    public function deletar($id) {
        $usuario = new Usuario();
        $usuario->findById($id);
        
        if (!$usuario->getId()) {
            throw new \Exception('Usuário não encontrado');
        }
        
        return $usuario->delete();
    }
    
    public function getTurmas($usuarioId) {
        $matricula = new Matricula();
        return $matricula->findByUsuario($usuarioId);
    }
    
    public function removerTurma($matriculaId) {
        $matricula = new Matricula();
        $matricula->findById($matriculaId);
        
        if (!$matricula->getId()) {
            throw new \Exception('Matrícula não encontrada');
        }
        
        // Atualizar vagas disponíveis da turma
        $stmt = $this->pdo->prepare("UPDATE turmas_cursos SET vagas_disponiveis = vagas_disponiveis + 1 WHERE id = ?");
        $stmt->execute([$matricula->getTurmaId()]);
        
        return $matricula->delete();
    }
}

