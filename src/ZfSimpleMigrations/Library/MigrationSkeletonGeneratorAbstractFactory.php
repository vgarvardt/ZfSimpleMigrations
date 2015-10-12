<?php


namespace ZfSimpleMigrations\Library;


use RuntimeException;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class MigrationSkeletonGeneratorAbstractFactory implements AbstractFactoryInterface
{
    const FACTORY_PATTERN = '/migrations\.skeletongenerator\.(.*)/';
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
        return preg_match(self::FACTORY_PATTERN, $name)
            || preg_match(self::FACTORY_PATTERN, $requestedName);
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
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        preg_match(self::FACTORY_PATTERN, $name, $matches)
            || preg_match(self::FACTORY_PATTERN, $requestedName, $matches);
        $migration_name = $matches[1];


        $config = $serviceLocator->get('Config');

        if (! isset($config['migrations'][$migration_name])) {
            throw new RuntimeException(sprintf("`%s` is not in migrations configuration", $migration_name));
        }

        $migration_config = $config['migrations'][$migration_name];

        if (! isset($migration_config['dir'])) {
            throw new RuntimeException(sprintf("`dir` has not be specified in `%s` migrations configuration", $migration_name));
        }

        if (! isset($migration_config['namespace'])) {
            throw new RuntimeException(sprintf("`namespace` has not be specified in `%s` migrations configuration", $migration_name));
        }

        $generator = new MigrationSkeletonGenerator($migration_config['dir'], $migration_config['namespace']);

        return $generator;
    }
}
