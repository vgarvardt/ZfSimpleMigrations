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
                        return new TableGateway(Model\MigrationVersion::TABLE_NAME, $dbAdapter, null, $resultSetPrototype);
                    },
            ),
        );
    }

    public function getConsoleUsage(Console $console)
    {
        return array(
            'Get last applied migration version',
            'migration version' => '',

            'List available migrations',
            'migration list [--all]' => '',
            array('--all', 'Include applied migrations'),

            'Generate new migration skeleton class',
            'migration generate' => '',

            'Execute migration',
            'migration apply [<version>] [--force] [--down] [--fake]' => '',
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
