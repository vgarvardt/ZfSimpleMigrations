<?php

namespace ZendDbMigrations\Library;

use Zend\Db\Metadata\MetadataInterface;

/**
 * Интерфейс который должны реализовывать классы миграций
 */
interface MigrationInterface {

    /**
     * Вернуть sql код выполнения миграции
     * @return array
     */
    public function getUpSql();

    /**
     * Вернуть sql код отката миграции
     * @return array
     */
    public function getDownSql();

    /**
     * Выполнить миграцию
     * @param MetadataInterface $schema
     */
    public function up(MetadataInterface $schema);
    
    /**
     * Откатить миграцию
     * @param MetadataInterface $schema
     */
    public function down(MetadataInterface $schema);
}

?>
