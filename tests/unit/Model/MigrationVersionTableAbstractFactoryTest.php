<?php
/**
 * @category WebPT
 * @copyright Copyright (c) 2015 WebPT, INC
 * @author jgiberson
 * 6/4/15 2:54 PM
 */

namespace ZfSimpleMigrations\UnitTest\Model;

use PHPUnit\Framework\TestCase;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceManager;
use ZfSimpleMigrations\Model\MigrationVersionTable;
use ZfSimpleMigrations\Model\MigrationVersionTableAbstractFactory;

class MigrationVersionTableAbstractFactoryTest extends TestCase
{
    public function test_it_indicates_what_services_it_creates()
    {
        $serviceManager = $this->buildServiceManager();

        $factory = new MigrationVersionTableAbstractFactory();
        $this->assertTrue(
            $factory->canCreateServiceWithName(
                $serviceManager,
                'migrations.versiontable.foo',
                'asdf'
            ),
            "should indicate it provides service for \$name"
        );

        $this->assertTrue(
            $factory->canCreateServiceWithName(
                $serviceManager,
                'asdf',
                'migrations.versiontable.foo'
            ),
            "should indicate it provides service for \$requestedName"
        );

        $this->assertFalse(
            $factory->canCreateServiceWithName(
                $serviceManager,
                'asdf',
                'asdf'
            ),
            "should indicate it does not provide service for \$name or \$requestedName"
        );
    }

    public function test_it_returns_a_migration_version_table()
    {
        $serviceManager = $this->buildServiceManager();

        $factory = new MigrationVersionTableAbstractFactory();
        $instance = $factory->createServiceWithName($serviceManager, 'migrations.versiontable.foo', 'asdf');
        $this->assertInstanceOf(
            MigrationVersionTable::class,
            $instance,
            "factory should return an instance of " . MigrationVersionTable::class . " when asked by \$name"
        );

        $instance2 = $factory->createServiceWithName($serviceManager, 'asdf', 'migrations.versiontable.foo');
        $this->assertInstanceOf(
            MigrationVersionTable::class,
            $instance2,
            "factory should return an instance of " . MigrationVersionTable::class . " when asked by \$requestedName"
        );
    }

    private function buildServiceManager(): ServiceManager
    {
        $tableGateway = $this->prophesize(TableGateway::class);

        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'migrations.versiontablegateway.foo',
            $tableGateway->reveal()
        );

        return $serviceManager;
    }
}
