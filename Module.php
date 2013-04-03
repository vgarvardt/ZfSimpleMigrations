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
    public function onBootstrap(EventInterface $e)
    {
        $e->getApplication()->getServiceManager()->get('translator');
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
            'factories' => array(
                'ZfSimpleMigrations\Model\MigrationVersionTable' => function (ServiceLocatorInterface $serviceLocator) {
                    /** @var $tableGateway TableGateway */
                    $tableGateway = $serviceLocator->get('ZfSimpleMigrationsVersionTableGateway');
                    $table = new Model\MigrationVersionTable($tableGateway);
                    return $table;
                },
                'ZfSimpleMigrationsVersionTableGateway' => function (ServiceLocatorInterface $serviceLocator) {
                    /** @var $dbAdapter \Zend\Db\Adapter\Adapter */
                    $dbAdapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\MigrationVersion());
                    return new TableGateway(Library\Migration::MIGRATION_TABLE, $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }

    public function getConsoleUsage(Console $console)
    {
        return array(
            'ZF2 Simple Migrations',

            'migration version' => 'Get current migration version',

            'migration list [--all]' => 'List available migrations',
            array('--all', 'Include applied migrations'),

            'migration apply [<version>] [--force]' => 'Execute migrate',
            array(
                '--force',
                'Force apply migration even if it\'s older than the last migrated. Works only with <version> explicitly set.'
            ),
            array('--down', 'Force apply down migration. Works only with --force flag set.'),

            'migration generate' => 'Generate new migration class'
        );
    }
}
