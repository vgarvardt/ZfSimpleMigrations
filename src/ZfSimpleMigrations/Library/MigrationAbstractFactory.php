<?php


namespace ZfSimpleMigrations\Library;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use RuntimeException;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfSimpleMigrations\Model\MigrationVersionTable;

class MigrationAbstractFactory implements AbstractFactoryInterface
{
    const FACTORY_PATTERN = '/migrations\.migration\.(.*)/';


    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return preg_match(self::FACTORY_PATTERN, $requestedName) || preg_match(self::FACTORY_PATTERN, $requestedName);
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = NULL)
    {


        $config = $container->get('Config');

        preg_match(self::FACTORY_PATTERN, $requestedName, $matches)
        || preg_match(self::FACTORY_PATTERN, $requestedName, $matches);

        $name = $matches[1];

        if (! isset($config['migrations'][$name])) {
            throw new RuntimeException(sprintf("`%s` does not exist in migrations configuration", $name));
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


        return $migration;
    }



}
