<?php

/**
 * User: Jeremy
 * Date: 8/8/2015
 * Time: 11:53 AM
 */

namespace ZfSimpleMigrations\Library\ApplyMigration;

use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Db\Metadata\MetadataInterface;
use Zend\Db\Sql\Ddl\Column\Integer;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Ddl\DropTable;
use ZfSimpleMigrations\Library\AbstractMigration;

class Version02 extends AbstractMigration implements AdapterAwareInterface
{
    use AdapterAwareTrait;

    public static $description = "Migration to test error in multi statement sql";

    public function up(MetadataInterface $schema)
    {
        $createTable = new CreateTable('test');
        $createTable->addColumn(new Integer('a'));
        $sql = $createTable->getSqlString($this->adapter->getPlatform());

        // attempt to drop a non-existing table on second statement
        $dropTable = new DropTable('fake');
        $sql .= '; ' . $dropTable->getSqlString($this->adapter->getPlatform());

        // execute multi-statement sql
        $this->addSql($sql);
    }

    public function down(MetadataInterface $schema)
    {
        // clean up result of first statement
        $dropTable = new DropTable('test');
        $this->addSql($dropTable->getSqlString($this->adapter->getPlatform()));
    }
}
