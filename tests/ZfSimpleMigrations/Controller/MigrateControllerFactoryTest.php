<?php

/**
 * @category WebPT
 * @copyright Copyright (c) 2015 WebPT, INC
 * @author jgiberson
 * 6/4/15 2:20 PM
 */

namespace ZfSimpleMigrations\Controller;

use PHPUnit\Framework\TestCase;
use Zend\Mvc\Application;
use Zend\Mvc\Controller\ControllerManager;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\ServiceManager\ServiceManager;
use ZfSimpleMigrations\Library\Migration;
use ZfSimpleMigrations\Library\MigrationSkeletonGenerator;

/**
 * @group unit
 */
class MigrateControllerFactoryTest extends TestCase
{
    public function testItReturnsAController()
    {
        $controllerManager = new ControllerManager();
        $controllerManager->setServiceLocator($this->buildServiceManager());

        $factory = new MigrateControllerFactory();
        $instance = $factory->createService($controllerManager);

        $this->assertInstanceOf(
            MigrateController::class,
            $instance,
            'factory should return an instance of ' . MigrateController::class
        );
    }

    private function buildServiceManager(): ServiceManager
    {
        $mvcEvent = new MvcEvent();

        $migration = $this->prophesize(Migration::class);
        $migrationSkeletonGenerator = $this->prophesize(MigrationSkeletonGenerator::class);
        $application = $this->prophesize(Application::class);

        $application->getMvcEvent()
            ->shouldBeCalled()
            ->willReturn($mvcEvent);

        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'migrations.migration.foo',
            $migration->reveal()
        );
        $serviceManager->setService(
            'migrations.skeleton-generator.foo',
            $migrationSkeletonGenerator->reveal()
        );
        $serviceManager->setService(
            'Application',
            $application->reveal()
        );

        $mvcEvent->setRouteMatch(new RouteMatch(['name' => 'foo']));

        return $serviceManager;
    }
}
