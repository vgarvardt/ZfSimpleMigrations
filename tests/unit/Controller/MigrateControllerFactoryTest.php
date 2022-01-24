<?php

/**
 * @category WebPT
 * @copyright Copyright (c) 2015 WebPT, INC
 * @author jgiberson
 * 6/4/15 2:20 PM
 */

namespace ZfSimpleMigrations\UnitTest\Controller;

use Zend\Mvc\Application;
use Zend\Mvc\Controller\ControllerManager;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\ServiceManager\ServiceManager;
use ZfSimpleMigrations\Controller\MigrateController;
use ZfSimpleMigrations\Controller\MigrateControllerFactory;
use ZfSimpleMigrations\Library\Migration;
use ZfSimpleMigrations\Library\MigrationSkeletonGenerator;

class MigrateControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ServiceManager */
    protected $serviceManager;

    protected function setUp()
    {
        parent::setUp();

        $this->serviceManager = new ServiceManager();
        $this->serviceManager->setService(
            'migrations.migration.foo',
            $this->getMock(Migration::class, [], [], '', false)
        );
        $this->serviceManager->setService(
            'migrations.skeleton-generator.foo',
            $this->getMock(MigrationSkeletonGenerator::class, [], [], '', false)
        );
        $this->serviceManager->setService(
            'Application',
            $application = $this->getMock(Application::class, [], [], '', false)
        );

        $application->expects($this->any())
            ->method('getMvcEvent')
            ->willReturn($mvcEvent = new MvcEvent());
        $mvcEvent->setRouteMatch(new RouteMatch(['name' => 'foo']));
    }

    public function testItReturnsAController()
    {
        $controllerManager = new ControllerManager($this->serviceManager);

        $factory = new MigrateControllerFactory();
        $instance = $factory->createService($controllerManager);

        $this->assertInstanceOf(
            MigrateController::class,
            $instance,
            'factory should return an instance of ' . MigrateController::class
        );
    }
}
