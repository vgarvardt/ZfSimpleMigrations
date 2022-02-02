<?php

namespace ZfSimpleMigrations\Library;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\AbstractPluginManager;
use ZfSimpleMigrations\Model\MigrationVersionTable;
use Interop\Container\ContainerInterface;

class MigrationAbstractFactory implements AbstractFactoryInterface
{
    const FACTORY_PATTERN = '/migrations\.migration\.(.*)/';

    /**
     * canCreate
     *
     * @param  Interop\Container\ContainerInterface $container
     * @param  mixed $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName) {
        return preg_match(self::FACTORY_PATTERN, $requestedName) ;
    }
    
    /**
     * __invoke
     *
     * @param  Interop\Container\ContainerInterface $container
     * @param  mixed $requestedName
     * @param  mixed $options
     * @return Migration
     * @throws MigrationException
     */

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {
        $config = $container->get('Config');

        preg_match(self::FACTORY_PATTERN, $requestedName, $matches)
        || preg_match(self::FACTORY_PATTERN, $requestedName, $matches);

        $name = $matches[1];

        if (! isset($config['migrations'][$name])) {
            throw new RuntimeException(sprintf("`%s` does not exist in migrations configuration", $requestedName));
        }

        $migration_config = $config['migrations'][$name];

        $adapter_name = isset($migration_config['adapter'])
            ? $migration_config['adapter'] : 'Zend\Db\Adapter\Adapter';
        /** @var $adapter \Zend\Db\Adapter\Adapter */
        $adapter = $container->get($adapter_name);


        $output = null;
        if (isset($migration_config['show_log']) && $migration_config['show_log']) {
            $console = $container->get('console');
            $output = new OutputWriter(function ($message) use ($console) {
                $console->write($message . "\n");
            });
        }

        /** @var MigrationVersionTable $version_table */
        $version_table = $container->get('migrations.versiontable.' . $adapter_name);

        $migration = new Migration($adapter, $migration_config, $version_table, $output);

        $migration->setServiceLocator($container);

        return $migration;
    }
}
