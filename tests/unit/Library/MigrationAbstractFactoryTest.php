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
    protected $service_manager;

    public function setUp()
    {
        parent::setUp();
        $this->service_manager = new ServiceManager(new Config([
            'allow_override' => true]));
        $this->service_manager->setService('Config', [
            'migrations' => [
                'foo' => [
                    'dir' => __DIR__,
                    'namespace' => 'Foo',
                    'adapter' => 'fooDb'
                ]
            ]
        ]);
        $this->service_manager->setService('migrations.versiontable.fooDb',
            $this->getMock(MigrationVersionTable::class, [], [], '', false));
        $this->service_manager->setService('console',
            $this->getMock(Console::class, [], [], '', false));
        $this->service_manager->setService('fooDb',
            $adapter = $this->getMock(Adapter::class, [], [], '', false));

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

    public function test_it_indicates_what_services_it_creates()
    {
        $factory = new MigrationAbstractFactory();
        $this->assertTrue($factory->canCreateServiceWithName($this->service_manager,
            'migrations.migration.foo', 'asdf'),
            "should indicate it provides service for \$name");

        $this->assertTrue($factory->canCreateServiceWithName($this->service_manager,
            'asdf', 'migrations.migration.foo'),
            "should indicate it provides service for \$requestedName");

        $this->assertFalse($factory->canCreateServiceWithName($this->service_manager,
            'asdf', 'asdf'),
            "should indicate it does not provide service for \$name or \$requestedName");
    }

    public function test_it_returns_a_migration()
    {
        $controller_manager = new ControllerManager();
        $controller_manager->setServiceLocator($this->service_manager);


        $factory = new MigrationAbstractFactory();
        $instance = $factory->createServiceWithName($controller_manager,
            'migrations.migration.foo', 'asdf');
        $this->assertInstanceOf(Migration::class, $instance,
            "factory should return an instance of "
            . Migration::class . " when asked by \$name");

        $instance2 = $factory->createServiceWithName($this->service_manager,
            'asdf', 'migrations.migration.foo');
        $this->assertInstanceOf(Migration::class, $instance2,
            "factory should return an instance of "
            . Migration::class . " when asked by \$requestedName");
    }

    public function test_it_injects_an_output_writer()
    {
        $this->service_manager->setService('Config', [
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
        $instance = $factory->createServiceWithName($this->service_manager,
            'migrations.migration.foo', 'asdf');

        $this->assertInstanceOf(OutputWriter::class, $instance->getOutputWriter(),
            "factory should inject a " . OutputWriter::class);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function test_it_complains_if_named_migration_not_configured()
    {
        $factory = new MigrationAbstractFactory();
        $factory->createServiceWithName($this->service_manager,
            'migrations.migration.bar', 'asdf');
    }
}
