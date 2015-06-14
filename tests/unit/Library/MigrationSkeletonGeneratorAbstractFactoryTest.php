<?php


namespace ZfSimpleMigrations\UnitTest\Library;


use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use ZfSimpleMigrations\Library\MigrationSkeletonGenerator;
use ZfSimpleMigrations\Library\MigrationSkeletonGeneratorAbstractFactory;

class MigrationSkeletonGeneratorAbstractFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ServiceManager */
    protected $service_manager;

    public function setUp()
    {
        parent::setUp();
        $this->service_manager = new ServiceManager(new Config(
            ['allow_override' => true]));
        $this->service_manager->setService('Config', [
            'migrations' => [
                'foo' => [
                    'dir' => __DIR__,
                    'namespace' => 'Foo'
                ]
            ]
        ]);
        $this->service_manager->setService('migrations.skeletongenerator.foo',
            $this->getMock(MigrationSkeletonGenerator::class, [], [], '', false));

    }

    public function test_it_indicates_what_services_it_creates()
    {
        $factory = new MigrationSkeletonGeneratorAbstractFactory();
        $this->assertTrue($factory->canCreateServiceWithName($this->service_manager,
            'migrations.skeletongenerator.foo', 'asdf'),
            "should indicate it provides service for \$name");

        $this->assertTrue($factory->canCreateServiceWithName($this->service_manager,
            'asdf', 'migrations.skeletongenerator.foo'),
            "should indicate it provides service for \$requestedName");

        $this->assertFalse($factory->canCreateServiceWithName($this->service_manager,
            'asdf', 'asdf'),
            "should indicate it does not provide service for \$name or \$requestedName");
    }

    public function test_it_returns_a_skeleton_generator()
    {
        $controller_manager = new ControllerManager();
        $controller_manager->setServiceLocator($this->service_manager);

        $factory = new MigrationSkeletonGeneratorAbstractFactory();
        $instance = $factory->createServiceWithName($controller_manager,
            'migrations.skeletongenerator.foo', 'asdf');
        $this->assertInstanceOf(MigrationSkeletonGenerator::class, $instance,
            "factory should return an instance of "
            . MigrationSkeletonGenerator::class . " when asked by \$name");

        $instance2 = $factory->createServiceWithName($this->service_manager,
            'asdf', 'migrations.skeletongenerator.foo');
        $this->assertInstanceOf(MigrationSkeletonGenerator::class, $instance2,
            "factory should return an instance of "
            . MigrationSkeletonGenerator::class . " when asked by \$requestedName");
    }

    /**
     * @expectedException \RuntimeException
     */
    public function test_it_complains_if_named_migration_is_not_configured()
    {
        $factory = new MigrationSkeletonGeneratorAbstractFactory();
        $factory->createServiceWithName($this->service_manager,
            'migrations.skeletongenerator.bar', 'asdf');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function test_it_complains_if_dir_is_not_configured()
    {
        $this->service_manager->setService('Config', [
            'migrations' => [
                'bar' => [
                    'namespace' => 'Bar'
                ]
            ]
        ]);

        $factory = new MigrationSkeletonGeneratorAbstractFactory();
        $factory->createServiceWithName($this->service_manager,
            'migrations.skeletongenerator.bar', 'asdf');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function test_it_complains_if_namespace_is_not_configured()
    {
        $this->service_manager->setService('Config', [
            'migrations' => [
                'bar' => [
                    'dir' => __DIR__
                ]
            ]
        ]);

        $factory = new MigrationSkeletonGeneratorAbstractFactory();
        $factory->createServiceWithName($this->service_manager,
            'migrations.skeletongenerator.bar', 'asdf');
    }
}
