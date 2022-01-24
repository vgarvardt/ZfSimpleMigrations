<?php

namespace ZfSimpleMigrations\UnitTest\Library;

use Zend\Console\Console;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\Pdo\Connection;
use Zend\Db\Adapter\Driver\Pdo\Pdo;
use Zend\Db\Adapter\Platform\Sqlite;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use ZfSimpleMigrations\Library\Migration;
use ZfSimpleMigrations\Library\MigrationAbstractFactory;
use ZfSimpleMigrations\Library\OutputWriter;
use ZfSimpleMigrations\Model\MigrationVersionTable;

class MigrationAbstractFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ServiceManager */
    protected $serviceManager;

    public function setUp()
    {
        parent::setUp();
        $this->serviceManager = new ServiceManager(new Config([
            'allow_override' => true]));
        $this->serviceManager->setService('Config', [
            'migrations' => [
                'foo' => [
                    'dir' => __DIR__,
                    'namespace' => 'Foo',
                    'adapter' => 'fooDb'
                ]
            ]
        ]);
        $this->serviceManager->setService(
            'migrations.versiontable.fooDb',
            $this->getMock(MigrationVersionTable::class, [], [], '', false)
        );
        $this->serviceManager->setService(
            'console',
            $this->getMock(Console::class, [], [], '', false)
        );
        $this->serviceManager->setService(
            'fooDb',
            $adapter = $this->getMock(Adapter::class, [], [], '', false)
        );

        $adapter->expects($this->any())
            ->method('getPlatform')
            ->willReturn(new Sqlite());
        $adapter->expects($this->any())
            ->method('getDriver')
            ->willReturn($driver = $this->getMock(Pdo::class, [], [], '', false));
        $driver->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->getMock(Connection::class, [], [], '', false));
    }

    public function testItIndicatesWhatServicesItCreates()
    {
        $factory = new MigrationAbstractFactory();
        $this->assertTrue(
            $factory->canCreateServiceWithName(
                $this->serviceManager,
                'migrations.migration.foo',
                'asdf'
            ),
            "should indicate it provides service for \$name"
        );

        $this->assertTrue(
            $factory->canCreateServiceWithName(
                $this->serviceManager,
                'asdf',
                'migrations.migration.foo'
            ),
            "should indicate it provides service for requestedName"
        );

        $this->assertFalse(
            $factory->canCreateServiceWithName(
                $this->serviceManager,
                'asdf',
                'asdf'
            ),
            "should indicate it does not provide service for name or requestedName"
        );
    }

    public function testItReturnsAMigration()
    {
        $controllerManager = new ControllerManager($this->serviceManager);

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
            $this->serviceManager,
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
        $this->serviceManager->setService('Config', [
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
            $this->serviceManager,
            'migrations.migration.foo',
            'asdf'
        );

        $this->assertInstanceOf(
            OutputWriter::class,
            $instance->getOutputWriter(),
            "factory should inject a " . OutputWriter::class
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testItComplainsIfNamedMigrationNotConfigured()
    {
        $factory = new MigrationAbstractFactory();
        $factory->createServiceWithName(
            $this->serviceManager,
            'migrations.migration.bar',
            'asdf'
        );
    }
}
