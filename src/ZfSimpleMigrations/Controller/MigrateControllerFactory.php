<?php


namespace ZfSimpleMigrations\Controller;


use Interop\Container\ContainerInterface;
use Zend\Mvc\Console\Router\RouteMatch;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfSimpleMigrations\Library\Migration;
use ZfSimpleMigrations\Library\MigrationSkeletonGenerator;

class MigrateControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = NULL)
    {


        /** @var RouteMatch $routeMatch */
        $routeMatch = $container->get('Application')->getMvcEvent()->getRouteMatch();

        $name            = $routeMatch->getParam('name', 'default');
        $migrationConfig = $container->get('config')['migrations'][$name];
        $prefix          = isset($migrationConfig['prefix']) ? $migrationConfig['prefix'] : '';

        /** @var Migration $migration */
        $migration = $container->get('migrations.migration.' . $name);
        $migration->changeMigrationPrefix($prefix);

        /** @var MigrationSkeletonGenerator $generator */
        $generator = $container->get('migrations.skeleton-generator.' . $name);

        $controller = new MigrateController();

        $controller->setMigration($migration);
        $controller->setSkeletonGenerator($generator);

        return $controller;
    }


}
