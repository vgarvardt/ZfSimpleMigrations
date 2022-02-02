<?php


namespace ZfSimpleMigrations\Model;


use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Interop\Container\ContainerInterface;

class MigrationVersionTableAbstractFactory implements AbstractFactoryInterface
{
    const FACTORY_PATTERN = '/migrations\.versiontable\.(.*)/';
        
    /**
     *  Determine if we can create a service with name
     *
     * @param  Interop\Container\ContainerInterface $container
     * @param  mixed $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName) {
        return preg_match(self::FACTORY_PATTERN, $requestedName);
    }
    
    /**
     * Create service with name
     *
     * @param  Interop\Container\ContainerInterface $container
     * @param  mixed $requestedName
     * @param  mixed $options
     * @return MigrationVersionTable
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {
        // $matches will be set by first preg_match if it matches, or second preg_match if it doesnt
        preg_match(self::FACTORY_PATTERN, $requestedName, $matches)
        || preg_match(self::FACTORY_PATTERN, $requestedName, $matches);

        $adapter_name = $matches[1];

        /** @var $tableGateway TableGateway */
        $tableGateway = $container->get('migrations.versiontablegateway.' . $adapter_name);
        $table = new MigrationVersionTable($tableGateway);
        return $table;
    }
}
