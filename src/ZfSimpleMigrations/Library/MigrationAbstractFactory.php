<?php


namespace ZfSimpleMigrations\Library;

use RuntimeException;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfSimpleMigrations\Model\MigrationVersionTable;

class MigrationAbstractFactory implements AbstractFactoryInterface
{
    const FACTORY_PATTERN = '/migrations\.migration\.(.*)/';
    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return preg_match(self::FACTORY_PATTERN, $name);
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if($serviceLocator instanceof AbstractPluginManager)
        {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        $config = $serviceLocator->get('Config');

        preg_match(self::FACTORY_PATTERN, $name, $matches);
        $name = $matches[1];

        if(!isset($config['migrations'][$name]))
        {
            throw new RuntimeException(sprintf("`%s` does not exist in migrations configuration", $name));
        }

        $migration_config = $config['migrations'][$name];

        $adapter_name = isset($migration_config['adapter'])
            ? $migration_config['adapter'] : 'Zend\Db\Adapter\Adapter';
        /** @var $adapter \Zend\Db\Adapter\Adapter */
        $adapter = $serviceLocator->get($adapter_name);


        $output = null;
        if (isset($migration_config['show_log']) && $migration_config['show_log']) {
            $console = $serviceLocator->get('console');
            $output = new OutputWriter(function ($message) use ($console) {
                $console->write($message . "\n");
            });
        }

        /** @var MigrationVersionTable $version_table */
        $version_table = $serviceLocator->get('migrations.versiontable.' . $adapter_name);

        $migration = new Migration($adapter, $migration_config, $version_table, $output);

        $migration->setServiceLocator($serviceLocator);

        return $migration;
    }
}