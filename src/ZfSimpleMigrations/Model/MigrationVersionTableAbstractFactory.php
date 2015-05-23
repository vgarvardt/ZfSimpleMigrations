<?php


namespace ZfSimpleMigrations\Model;


use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MigrationVersionTableAbstractFactory implements AbstractFactoryInterface
{
    const FACTORY_PATTERN = '/migrations\.version-table\.(.*)+/';
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
        preg_match(self::FACTORY_PATTERN, $name, $matches);
        $adapter_name = $matches[0];

        /** @var $tableGateway TableGateway */
        $tableGateway = $serviceLocator->get('migrations.version-table-gateway.' . $adapter_name);
        $table = new MigrationVersionTable($tableGateway);
        return $table;
    }
}