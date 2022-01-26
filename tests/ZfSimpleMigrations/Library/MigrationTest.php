<?php

/**
 * User: Jeremy
 * Date: 8/8/2015
 * Time: 10:06 AM
 */

namespace ZfSimpleMigrations\Library;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Metadata\Metadata;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Ddl\DropTable;
use Zend\Db\TableGateway\TableGateway;
use ZfSimpleMigrations\Model\MigrationVersion;
use ZfSimpleMigrations\Model\MigrationVersionTable;

/**
 * @group integration
 */
class MigrationTest extends TestCase
{
    /** @var Adapter */
    private $adapter;
    /** @var Migration */
    private $migration;

    protected function setUp()
    {
        parent::setUp();
        $driverConfig = [
            'driver' => getenv('DB_TYPE'),
            'database' => getenv('DB_NAME'),
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'hostname' => getenv('DB_HOST'),
            'port' => getenv('DB_PORT'),
            'options' => [
                'buffer_results' => true,
            ],
        ];
        $config = [
            'dir' => __DIR__ . '/ApplyMigration',
            'namespace' => 'ZfSimpleMigrations\\Library\\ApplyMigration'
        ];

        $this->adapter = $adapter = new Adapter($driverConfig);

        $metadata = new Metadata($adapter);
        $tableNames = $metadata->getTableNames();

        $drop_if_exists = [
            'test',
            MigrationVersion::TABLE_NAME
        ];
        foreach ($drop_if_exists as $table) {
            if (in_array($table, $tableNames)) {
                // ensure db is in expected state
                $drop = new DropTable($table);
                $adapter->query($drop->getSqlString($adapter->getPlatform()));
            }
        }


        /** @var ArrayObject $version */
        $version = new MigrationVersion();
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype($version);

        $gateway = new TableGateway(MigrationVersion::TABLE_NAME, $adapter, null, $resultSetPrototype);
        $table = new MigrationVersionTable($gateway);

        $this->migration = new Migration($adapter, $config, $table);
    }

    public function testApplyMigration()
    {
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
    public function testMultiStatementErrorDetection()
    {
        $this->markTestSkipped(
            'need to implement driver specific features & test if this driver supports multi-row functionality'
        );

        try {
            $this->migration->migrate('02');
        } catch (\Exception $e) {
            $this->migration->migrate('02', true, true);
            $this->assertEquals('ZfSimpleMigrations\Library\MigrationException', get_class($e));
            return;
        }

        $this->fail(sprintf('expected exception %s', '\ZfSimpleMigrations\Library\MigrationException'));
    }

    public function testMigrationInitializesMigrationTable()
    {
        // because Migration was instantiated in setup, the version table should exist
        $metadata = new Metadata($this->adapter);
        $tableNames = $metadata->getTableNames();
        $this->assertContains(MigrationVersion::TABLE_NAME, $tableNames);
    }
}
