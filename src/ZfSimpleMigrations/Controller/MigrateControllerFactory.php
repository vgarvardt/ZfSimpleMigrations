<?php


namespace ZfSimpleMigrations\Controller;


use Zend\Console\Request;
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
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if($serviceLocator instanceof AbstractPluginManager)
        {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        /** @var Request $request */
        $request = $serviceLocator->get('Request');

        $name = $request->getParam('name', 'default');

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