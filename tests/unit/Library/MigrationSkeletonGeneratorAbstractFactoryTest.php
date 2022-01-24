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
    protected $serviceManager;

    public function setUp()
    {
        parent::setUp();
        $this->serviceManager = new ServiceManager(new Config(['allow_override' => true]));
        $this->serviceManager->setService('Config', [
            'migrations' => [
                'foo' => [
                    'dir' => __DIR__,
                    'namespace' => 'Foo'
                ]
            ]
        ]);
        $this->serviceManager->setService(
            'migrations.skeletongenerator.foo',
            $this->getMock(MigrationSkeletonGenerator::class, [], [], '', false)
        );
    }

    public function testItIndicatesWhatServicesItCreates()
    {
        $factory = new MigrationSkeletonGeneratorAbstractFactory();
        $this->assertTrue(
            $factory->canCreateServiceWithName(
                $this->serviceManager,
                'migrations.skeletongenerator.foo',
                'asdf'
            ),
            "should indicate it provides service for \$name"
        );

        $this->assertTrue(
            $factory->canCreateServiceWithName(
                $this->serviceManager,
                'asdf',
                'migrations.skeletongenerator.foo'
            ),
            "should indicate it provides service for \$requestedName"
        );

        $this->assertFalse(
            $factory->canCreateServiceWithName(
                $this->serviceManager,
                'asdf',
                'asdf'
            ),
            "should indicate it does not provide service for \$name or \$requestedName"
        );
    }

    public function testItReturnsASkeletonGenerator()
    {
        $controllerManager = new ControllerManager($this->serviceManager);

        $factory = new MigrationSkeletonGeneratorAbstractFactory();
        $instance = $factory->createServiceWithName(
            $controllerManager,
            'migrations.skeletongenerator.foo',
            'asdf'
        );
        $this->assertInstanceOf(
            MigrationSkeletonGenerator::class,
            $instance,
            "factory should return an instance of " . MigrationSkeletonGenerator::class . " when asked by name"
        );

        $instance2 = $factory->createServiceWithName(
            $this->serviceManager,
            'asdf',
            'migrations.skeletongenerator.foo'
        );
        $this->assertInstanceOf(
            MigrationSkeletonGenerator::class,
            $instance2,
            "factory should return an instance of " . MigrationSkeletonGenerator::class . " when asked by requestedName"
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testItComplainsIfNamedMigrationIsNotConfigured()
    {
        $factory = new MigrationSkeletonGeneratorAbstractFactory();
        $factory->createServiceWithName(
            $this->serviceManager,
            'migrations.skeletongenerator.bar',
            'asdf'
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testItComplainsIfDirIsNotConfigured()
    {
        $this->serviceManager->setService('Config', [
            'migrations' => [
                'bar' => [
                    'namespace' => 'Bar'
                ]
            ]
        ]);

        $factory = new MigrationSkeletonGeneratorAbstractFactory();
        $factory->createServiceWithName(
            $this->serviceManager,
            'migrations.skeletongenerator.bar',
            'asdf'
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testItComplainsIfNamespaceIsNotConfigured()
    {
        $this->serviceManager->setService('Config', [
            'migrations' => [
                'bar' => [
                    'dir' => __DIR__
                ]
            ]
        ]);

        $factory = new MigrationSkeletonGeneratorAbstractFactory();
        $factory->createServiceWithName(
            $this->serviceManager,
            'migrations.skeletongenerator.bar',
            'asdf'
        );
    }
}
