<?php

/**
 * User: Jeremy
 * Date: 8/8/2015
 * Time: 10:16 AM
 */

namespace ZfSimpleMigrations\Library\ApplyMigration;

use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Db\Sql\Ddl\Column\Integer;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Ddl\DropTable;
use ZfSimpleMigrations\Library\AbstractMigration;
use Zend\Db\Metadata\MetadataInterface;

class Version01 extends AbstractMigration implements AdapterAwareInterface
{
    use AdapterAwareTrait;

    public static $description = "Migration to test up/down";

    public function up(MetadataInterface $schema)
    {
        $createTable = new CreateTable('test');
        $createTable->addColumn(new Integer('a'));
        $this->addSql($createTable->getSqlString($this->adapter->getPlatform()));
    }

    public function down(MetadataInterface $schema)
    {
        $dropTable = new DropTable('test');
        $this->addSql($dropTable->getSqlString($this->adapter->getPlatform()));
    }
}
