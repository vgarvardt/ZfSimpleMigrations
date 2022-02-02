<?php


namespace ZfSimpleMigrations\Library;


use RuntimeException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Interop\Container\ContainerInterface;

class MigrationSkeletonGeneratorAbstractFactory implements AbstractFactoryInterface
{
    const FACTORY_PATTERN = '/migrations\.skeleton-generator\.(.*)/';
        
    /**
     * canCreate
     *
     * @param  Interop\Container\ContainerInterface $container
     * @param  mixed $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName) {
        return preg_match(self::FACTORY_PATTERN, $requestedName);
    }
    
    /**
     * __invoke
     *
     * @param  Interop\Container\ContainerInterface $container
     * @param  mixed $requestedName
     * @param  mixed $options
     * @return MigrationSkeletonGenerator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {
        
        preg_match(self::FACTORY_PATTERN, $requestedName, $matches);
        $migration_name = $matches[1];

        $config = $container->get('Config');

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
