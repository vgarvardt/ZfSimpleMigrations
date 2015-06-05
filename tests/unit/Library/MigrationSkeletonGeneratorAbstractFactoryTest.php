<?php


namespace ZfSimpleMigrations\UnitTest\Library;


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
        $this->service_manager = new ServiceManager();
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
            'migrations.skeletongenerator.foo', 'asdf'), "should indicate it provides service for \$name");

        $factory = new MigrationSkeletonGeneratorAbstractFactory();
        $this->assertTrue($factory->canCreateServiceWithName($this->service_manager,
            'asdf', 'migrations.skeletongenerator.foo'), "should indicate it provides service for \$requestedName");

        $factory = new MigrationSkeletonGeneratorAbstractFactory();
        $this->assertFalse($factory->canCreateServiceWithName($this->service_manager,
            'asdf', 'asdf'), "should indicate it does not provide service for \$name or \$requestedName");
    }

    public function test_it_returns_a_table_gateway()
    {
        $factory = new MigrationSkeletonGeneratorAbstractFactory();
        $instance = $factory->createServiceWithName($this->service_manager, 'migrations.skeletongenerator.foo', 'asdf');
        $this->assertInstanceOf(MigrationSkeletonGenerator::class, $instance,
            "factory should return an instance of " . MigrationSkeletonGenerator::class . " when asked by \$name");

        $instance2 = $factory->createServiceWithName($this->service_manager, 'asdf', 'migrations.skeletongenerator.foo');
        $this->assertInstanceOf(MigrationSkeletonGenerator::class, $instance2,
            "factory should return an instance of " . MigrationSkeletonGenerator::class . " when asked by \$requestedName");
    }
}
