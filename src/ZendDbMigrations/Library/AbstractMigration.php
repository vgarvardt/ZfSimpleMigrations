<?php

namespace ZendDbMigrations\Library;

use Zend\Db\Metadata\MetadataInterface;
use ZendDbMigrations\Library\MigrationInterface;
/**
 * Абстрактный класс для миграций
 */
abstract class AbstractMigration implements MigrationInterface {
    
    private $sql = array();
    private $metadata;

    public function __construct(MetadataInterface $metadata) {
        $this->metadata = $metadata;
    }

    /**
     * Метод для добавления SQL кода для миграции
     * @param string $sql
     */
    protected function addSql($sql){
        $this->sql[] = $sql;
    }
    
    /**
     * Вернуть sql код выполнения миграции
     * @return array
     */
    public function getUpSql(){
        $this->sql = array();
        $this->up($this->metadata);
        
        return $this->sql;
    }

    /**
     * Вернуть sql код отката миграции
     * @return array
     */
    public function getDownSql(){
        $this->sql = array();
        $this->down($this->metadata);
        
        return $this->sql;
    }
}

?>
