<?php


namespace ZfSimpleMigrations\Controller;


use Zend\Console\Request;
use Zend\Mvc\Router\Console\RouteMatch;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZfSimpleMigrations\Library\Migration;
use ZfSimpleMigrations\Library\MigrationSkeletonGenerator;
use Interop\Container\ContainerInterface;


class MigrateControllerFactory implements FactoryInterface
{

    /**
     * canCreate
     *
     * @param  mixed $container
     * @param  mixed $requestedName
     * @return void
     */
    public function canCreate(ContainerInterface $container, $requestedName) {
        return preg_match(self::FACTORY_PATTERN, $name);
    }
    
    /**
     * __invoke
     *
     * @param  mixed $container
     * @param  mixed $requestedName
     * @param  mixed $options
     * @return void
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {
        
        /** @var RouteMatch $routeMatch */
        $routeMatch = $container->get('Application')->getMvcEvent()->getRouteMatch();
        $name = $routeMatch->getParam('name', 'default');

        /** @var Migration $migration */
        $migration = $container->get('migrations.migration.' . $name);
        
        /** @var MigrationSkeletonGenerator $generator */
        $generator = $container->get('migrations.skeleton-generator.' . $name);
        $controller = new MigrateController();
   
        $controller->setMigration($migration);
        $controller->setSkeletonGenerator($generator);
        
        return $controller;
    }
}
