<?php
/**
 * User: Jeremy
 * Date: 8/8/2015
 * Time: 10:06 AM
 */

namespace ZfSimpleMigrations\IntegrationTest\Library;


use ArrayObject;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Metadata\Metadata;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Ddl\DropTable;
use Zend\Db\TableGateway\TableGateway;
use ZfSimpleMigrations\Library\Migration;
use ZfSimpleMigrations\Library\MigrationException;
use ZfSimpleMigrations\Model\MigrationVersion;
use ZfSimpleMigrations\Model\MigrationVersionTable;

class MigrationTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Adapter */
    private $adapter;
    /** @var  Migration */
    private $migration;

    protected function setUp()
    {
        parent::setUp();
        $driverConfig = [
            'driver' => getenv('db_type'),
            // sqlite handling (if necessary)
            'database' => str_replace('%BASE_DIR%', __DIR__ . '/../../../', getenv('db_name')),
            'username' => getenv('db_user'),
            'password' => getenv('db_password'),
            'hostname' => getenv('db_host'),
            'port' => getenv('db_port')
        ];
        $config = [
            'dir' => __DIR__ . '/../data/ApplyMigration',
            'namespace' => 'ApplyMigration'
        ];

       $this->adapter = $adapter = new Adapter($driverConfig);

        $metadata = new Metadata($adapter);
        $tableNames = $metadata->getTableNames();

        if(in_array('test',$tableNames)){
            // ensure db is in expected state
            $drop_test = new DropTable('test');
            $adapter->query($drop_test->getSqlString($adapter->getPlatform()));
        }

        /** @var ArrayObject $version */
        $version = new MigrationVersion();
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype($version);

        $gateway = new TableGateway(MigrationVersion::TABLE_NAME, $adapter, null, $resultSetPrototype);
        $table = new MigrationVersionTable($gateway);

        $this->migration = new Migration($adapter, $config, $table);
    }

    public function test_apply_migration() {
        $this->migration->migrate('01');

        $metadata = new Metadata($this->adapter);
        $this->assertContains('test', $metadata->getTableNames(), 'up should create table');

        $this->migration->migrate('01', true, true);

        $metadata = new Metadata($this->adapter);
        $this->assertNotContains('test', $metadata->getTableNames(), 'down should drop table');
    }

    /**
     * @expectedException \ZfSimpleMigrations\Library\MigrationException
     */
    public function test_multi_statement_error_detection()
    {
        if(strtolower(getenv('db_type')) == 'pdo_sqlite'){
            echo "blah";
            $this->markTestSkipped('sqlite driver does not support multi row sets [how we test for errors w/ multi statements]');
        }

        try {
            $this->migration->migrate('02');
        } catch (\Exception $e) {
            $this->migration->migrate('02', true, true);
            $this->assertEquals('ZfSimpleMigrations\Library\MigrationException', get_class($e));
            return;
        }

        $this->fail(sprintf('expected exception %s', '\ZfSimpleMigrations\Library\MigrationException'));
    }
}
