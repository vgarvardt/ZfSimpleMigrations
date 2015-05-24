<?php
namespace ZfSimpleMigrations;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\ServiceManager\ServiceLocatorInterface;

class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    ConsoleUsageProviderInterface,
    ServiceProviderInterface,
    BootstrapListenerInterface
{
    /**
     * @param EventInterface|\Zend\Mvc\MvcEvent $e
     * @return array|void
     */
    public function onBootstrap(EventInterface $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'abstract_factories' => array(
                'ZfSimpleMigrations\\Library\\MigrationAbstractFactory',
                'ZfSimpleMigrations\\Model\\MigrationVersionTableAbstractFactory',
                'ZfSimpleMigrations\\Model\\MigrationVersionTableGatewayAbstractFactory',
                'ZfSimpleMigrations\\Library\\MigrationSkeletonGeneratorAbstractFactory'
            ),
        );
    }

    public function getConsoleUsage(Console $console)
    {
        return array(
            'Get last applied migration version',
            'migration version [<name>]' => '',
            array('[<name>]', 'specify which configured migrations to run, defaults to `default`'),

            'List available migrations',
            'migration list [<name>] [--all]' => '',
            array('--all', 'Include applied migrations'),
            array('[<name>]', 'specify which configured migrations to run, defaults to `default`'),

            'Generate new migration skeleton class',
            'migration generate [<name>]' => '',
            array('[<name>]', 'specify which configured migrations to run, defaults to `default`'),

            'Execute migration',
            'migration apply [<name>] [<version>] [--force] [--down] [--fake]' => '',
            array('[<name>]', 'specify which configured migrations to run, defaults to `default`'),
            array(
                '--force',
                'Force apply migration even if it\'s older than the last migrated. Works only with <version> explicitly set.'
            ),
            array(
                '--down',
                'Force apply down migration. Works only with --force flag set.'
            ),
            array(
                '--fake',
                'Fake apply or apply down migration. Adds/removes migration to the list of applied w/out really applying it. Works only with <version> explicitly set.'
            ),
        );
    }
}
