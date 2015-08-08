<?php
/**
 * User: Jeremy
 * Date: 8/8/2015
 * Time: 10:16 AM
 */

namespace ApplyMigration;


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

    public static $description = "Migration description";

    public function up(MetadataInterface $schema)
    {
        $create_test = new CreateTable('test');
        $create_test->addColumn(new Integer('a'));
        $this->addSql($create_test->getSqlString($this->adapter->getPlatform()));

    }

    public function down(MetadataInterface $schema)
    {
        $drop_test = new DropTable('test');
        $this->addSql($drop_test->getSqlString($this->adapter->getPlatform()));
    }
}