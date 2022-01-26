<?php

namespace ZfSimpleMigrations\UnitTest\Library;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use ZfSimpleMigrations\Library\MigrationSkeletonGenerator;
use ZfSimpleMigrations\Library\MigrationSkeletonGeneratorAbstractFactory;

class MigrationSkeletonGeneratorAbstractFactoryTest extends TestCase
{
    public function testItIndicatesWhatServicesItCreates()
    {
        $serviceManager = $this->buildServiceManager();

        $factory = new MigrationSkeletonGeneratorAbstractFactory();
        $this->assertTrue(
            $factory->canCreateServiceWithName(
                $serviceManager,
                'migrations.skeletongenerator.foo',
                'asdf'
            ),
            "should indicate it provides service for \$name"
        );

        $this->assertTrue(
            $factory->canCreateServiceWithName(
                $serviceManager,
                'asdf',
                'migrations.skeletongenerator.foo'
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

    public function testItReturnsASkeletonGenerator()
    {
        $serviceManager = $this->buildServiceManager();

        $controllerManager = new ControllerManager();
        $controllerManager->setServiceLocator($serviceManager);

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
            $serviceManager,
            'asdf',
            'migrations.skeletongenerator.foo'
        );
        $this->assertInstanceOf(
            MigrationSkeletonGenerator::class,
            $instance2,
            "factory should return an instance of " . MigrationSkeletonGenerator::class . " when asked by requestedName"
        );
    }

    public function testItComplainsIfNamedMigrationIsNotConfigured()
    {
        $serviceManager = $this->buildServiceManager();

        $this->expectException(RuntimeException::class);

        $factory = new MigrationSkeletonGeneratorAbstractFactory();
        $factory->createServiceWithName(
            $serviceManager,
            'migrations.skeletongenerator.bar',
            'asdf'
        );
    }

    public function testItComplainsIfDirIsNotConfigured()
    {
        $serviceManager = $this->buildServiceManager();

        $serviceManager->setService('Config', [
            'migrations' => [
                'bar' => [
                    'namespace' => 'Bar'
                ]
            ]
        ]);

        $this->expectException(RuntimeException::class);

        $factory = new MigrationSkeletonGeneratorAbstractFactory();
        $factory->createServiceWithName(
            $serviceManager,
            'migrations.skeletongenerator.bar',
            'asdf'
        );
    }

    public function testItComplainsIfNamespaceIsNotConfigured()
    {
        $serviceManager = $this->buildServiceManager();

        $serviceManager->setService('Config', [
            'migrations' => [
                'bar' => [
                    'dir' => __DIR__
                ]
            ]
        ]);

        $this->expectException(RuntimeException::class);

        $factory = new MigrationSkeletonGeneratorAbstractFactory();
        $factory->createServiceWithName(
            $serviceManager,
            'migrations.skeletongenerator.bar',
            'asdf'
        );
    }

    private function buildServiceManager(): ServiceManager
    {
        $migrationSkeletonGenerator = $this->prophesize(MigrationSkeletonGenerator::class);

        $serviceManager = new ServiceManager(new Config(['allow_override' => true]));
        $serviceManager->setService('Config', [
            'migrations' => [
                'foo' => [
                    'dir' => __DIR__,
                    'namespace' => 'Foo'
                ]
            ]
        ]);
        $serviceManager->setService(
            'migrations.skeletongenerator.foo',
            $migrationSkeletonGenerator->reveal()
        );

        return $serviceManager;
    }
}
