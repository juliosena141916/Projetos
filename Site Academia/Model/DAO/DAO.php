<?php
namespace Model\DAO;

use Model\Database;

/**
 * Classe base para todos os DAOs
 */
abstract class DAO {
    protected $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    protected function getTableName() {
        // Por padrão, usa o nome da classe em minúsculo + 's'
        $class = get_class($this);
        $class = substr($class, strrpos($class, '\\') + 1);
        return strtolower(str_replace('DAO', '', $class)) . 's';
    }
}

