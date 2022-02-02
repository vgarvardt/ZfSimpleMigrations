<?php

namespace ZfSimpleMigrations\Controller;

use Zend\Mvc\Router\Console\RouteMatch;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfSimpleMigrations\Library\Migration;
use ZfSimpleMigrations\Library\MigrationSkeletonGenerator;

class MigrateControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return MigrateController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): MigrateController
    {
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        /** @var RouteMatch $routeMatch */
        $routeMatch = $serviceLocator->get('Application')->getMvcEvent()->getRouteMatch();

        $name = $routeMatch->getParam('name', 'default');

        /** @var Migration $migration */
        $migration = $serviceLocator->get('migrations.migration.' . $name);
        /** @var MigrationSkeletonGenerator $generator */
        $generator = $serviceLocator->get('migrations.skeleton-generator.' . $name);

        $controller = new MigrateController();

        $controller->setMigration($migration);
        $controller->setSkeletonGenerator($generator);

        return $controller;
    }
}
