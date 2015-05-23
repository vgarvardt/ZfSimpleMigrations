<?php


namespace ZfSimpleMigrations\Model;


use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MigrationVersionTableGatewayAbstractFactory implements AbstractFactoryInterface
{
    const FACTORY_PATTERN = '/migrations\.version-table-gateway\.(.*)+/';
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

        /** @var $dbAdapter \Zend\Db\Adapter\Adapter */
        $dbAdapter = $serviceLocator->get($adapter_name);
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new MigrationVersion());
        return new TableGateway(MigrationVersion::TABLE_NAME, $dbAdapter, null, $resultSetPrototype);
    }
}