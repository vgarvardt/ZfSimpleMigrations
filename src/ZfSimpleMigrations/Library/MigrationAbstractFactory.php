<?php

namespace ZfSimpleMigrations\Library;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
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
     * @param string $name
     * @param string $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName): bool
    {
        return preg_match(self::FACTORY_PATTERN, $name) || preg_match(self::FACTORY_PATTERN, $requestedName);
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return Migration
     * @throws MigrationException
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName): Migration
    {
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        $config = $serviceLocator->get('Config');

        preg_match(self::FACTORY_PATTERN, $name, $matches)
        || preg_match(self::FACTORY_PATTERN, $requestedName, $matches);

        $name = $matches[1];

        if (!isset($config['migrations'][$name])) {
            throw new RuntimeException(sprintf("`%s` does not exist in migrations configuration", $name));
        }

        $migrationConfig = $config['migrations'][$name];

        $adapterName = $migrationConfig['adapter'] ?: Adapter::class;
        /** @var $adapter Adapter */
        $adapter = $serviceLocator->get($adapterName);

        $output = null;
        if (isset($migrationConfig['show_log']) && $migrationConfig['show_log']) {
            $console = $serviceLocator->get('console');
            $output = new OutputWriter(function ($message) use ($console) {
                $console->write($message . "\n");
            });
        }

        /** @var MigrationVersionTable $versionTable */
        $versionTable = $serviceLocator->get('migrations.versiontable.' . $adapterName);

        $migration = new Migration($adapter, $migrationConfig, $versionTable, $output);

        $migration->setServiceLocator($serviceLocator);

        return $migration;
    }
}
