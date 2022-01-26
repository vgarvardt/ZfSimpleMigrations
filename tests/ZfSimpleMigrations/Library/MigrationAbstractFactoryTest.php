<?php

namespace ZfSimpleMigrations\Library;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use RuntimeException;
use Zend\Console\Console;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\Pdo\Connection;
use Zend\Db\Adapter\Driver\Pdo\Pdo;
use Zend\Db\Adapter\Platform\Sqlite;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use ZfSimpleMigrations\Model\MigrationVersionTable;

/**
 * @group unit
 */
class MigrationAbstractFactoryTest extends TestCase
{
    public function testItIndicatesWhatServicesItCreates()
    {
        $serviceManager = $this->buildServiceManager(false);

        $factory = new MigrationAbstractFactory();
        $this->assertTrue(
            $factory->canCreateServiceWithName(
                $serviceManager,
                'migrations.migration.foo',
                'asdf'
            ),
            "should indicate it provides service for \$name"
        );

        $this->assertTrue(
            $factory->canCreateServiceWithName(
                $serviceManager,
                'asdf',
                'migrations.migration.foo'
            ),
            "should indicate it provides service for requestedName"
        );

        $this->assertFalse(
            $factory->canCreateServiceWithName(
                $serviceManager,
                'asdf',
                'asdf'
            ),
            "should indicate it does not provide service for name or requestedName"
        );
    }

    public function testItReturnsAMigration()
    {
        $serviceManager = $this->buildServiceManager(true);

        $controllerManager = new ControllerManager();
        $controllerManager->setServiceLocator($serviceManager);

        $factory = new MigrationAbstractFactory();
        $instance = $factory->createServiceWithName(
            $controllerManager,
            'migrations.migration.foo',
            'asdf'
        );
        $this->assertInstanceOf(
            Migration::class,
            $instance,
            "factory should return an instance of " . Migration::class . " when asked by name"
        );

        $instance2 = $factory->createServiceWithName(
            $serviceManager,
            'asdf',
            'migrations.migration.foo'
        );
        $this->assertInstanceOf(
            Migration::class,
            $instance2,
            "factory should return an instance of " . Migration::class . " when asked by requestedName"
        );
    }

    public function testItInjectsAnOutputWriter()
    {
        $serviceManager = $this->buildServiceManager(true);

        $serviceManager->setService('Config', [
            'migrations' => [
                'foo' => [
                    'dir' => __DIR__,
                    'namespace' => 'Foo',
                    'adapter' => 'fooDb',
                    'show_log' => true
                ]
            ]
        ]);
        $factory = new MigrationAbstractFactory();
        $instance = $factory->createServiceWithName(
            $serviceManager,
            'migrations.migration.foo',
            'asdf'
        );

        $this->assertInstanceOf(
            OutputWriter::class,
            $instance->getOutputWriter(),
            "factory should inject a " . OutputWriter::class
        );
    }

    public function testItComplainsIfNamedMigrationNotConfigured()
    {
        $serviceManager = $this->buildServiceManager(false);
        $this->expectException(RuntimeException::class);

        $factory = new MigrationAbstractFactory();
        $factory->createServiceWithName(
            $serviceManager,
            'migrations.migration.bar',
            'asdf'
        );
    }

    private function buildServiceManager(bool $expectVersionTableQuery): ServiceManager
    {
        $migrationVersionTable = $this->prophesize(MigrationVersionTable::class);
        $console = $this->prophesize(Console::class);
        $adapter = $this->prophesize(Adapter::class);
        $driver = $this->prophesize(Pdo::class);
        $connection = $this->prophesize(Connection::class);

        if ($expectVersionTableQuery) {
            $sqlite = new Sqlite();

            $driver->getConnection()
                ->shouldBeCalled()
                ->willReturn($connection->reveal());

            $adapter->getCurrentSchema()
                ->shouldBeCalled()
                ->willReturn('fooDb');
            $adapter->getPlatform()
                ->shouldBeCalled()
                ->willReturn($sqlite);
            $adapter->getDriver()
                ->shouldBeCalled()
                ->willReturn($driver->reveal());
            $adapter->query(Argument::containingString('CREATE TABLE "migration_version"'), Adapter::QUERY_MODE_EXECUTE)
                ->shouldBeCalled();
        }

        $serviceManager = new ServiceManager(new Config(['allow_override' => true]));
        $serviceManager->setService('Config', [
            'migrations' => [
                'foo' => [
                    'dir' => __DIR__,
                    'namespace' => 'Foo',
                    'adapter' => 'fooDb'
                ]
            ]
        ]);
        $serviceManager->setService(
            'migrations.versiontable.fooDb',
            $migrationVersionTable->reveal()
        );
        $serviceManager->setService(
            'console',
            $console->reveal()
        );
        $serviceManager->setService(
            'fooDb',
            $adapter->reveal()
        );

        return $serviceManager;
    }
}
