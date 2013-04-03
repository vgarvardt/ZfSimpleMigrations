<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendDbMigrations;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;

class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    ConsoleUsageProviderInterface
{
    public function onBootstrap(MvcEvent $e)
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
                'ZendDbMigrations\Model\MigrationVersionTable' => function ($sm) {
                    /** @var $sm */
                    $tableGateway = $sm->get('MigrationVersionTableGateway');
                    $table = new Model\MigrationVersionTable($tableGateway);
                    return $table;
                },
                'MigrationVersionTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
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
            'Migrations',

            'migration version' => 'Get current migration version',

            'migration list [--all]' => 'List available migrations',
            array('--all', 'Include applied migrations'),

            'migration migrate [<version>]' => 'Execute migrate',
            array(
                '--force',
                'Force apply migration even if it\'s older than the last migrated. Works only with <version> explicitly set.'
            ),
            array('--down', 'Force apply down migration. Works only with --force flag set.'),

            'migration generate' => 'Generate new migration class'
        );
    }
}
