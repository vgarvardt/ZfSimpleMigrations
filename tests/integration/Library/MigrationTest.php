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
use ZfSimpleMigrations\Model\MigrationVersion;
use ZfSimpleMigrations\Model\MigrationVersionTable;

class MigrationTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Adapter */
    private $adapter;

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

       $this->adapter = $adapter = new Adapter($driverConfig);

        $metadata = new Metadata($adapter);
        $tableNames = $metadata->getTableNames();

        if(in_array('test',$tableNames)){
            // ensure db is in expected state
            $drop_test = new DropTable('test');
            $adapter->query($drop_test->getSqlString($adapter->getPlatform()));
        }
    }

    public function test_apply_migration() {
        $config = [
            'dir' => __DIR__ . '/../data/ApplyMigration',
            'namespace' => 'ApplyMigration'
        ];
        $adapter = $this->adapter;

        /** @var ArrayObject $version */
        $version = new MigrationVersion();
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype($version);

        $gateway = new TableGateway(MigrationVersion::TABLE_NAME, $adapter, null, $resultSetPrototype);
        $table = new MigrationVersionTable($gateway);

        $migration = new Migration($adapter, $config, $table);
        $migration->migrate(null);

        $metadata = new Metadata($adapter);
        $this->assertContains('test', $metadata->getTableNames(), 'up should create table');

        $migration->migrate('01', true, true);

        $metadata = new Metadata($adapter);
        $this->assertNotContains('test', $metadata->getTableNames(), 'down should drop table');
    }
}
