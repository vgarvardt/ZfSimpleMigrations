<?php


namespace ZfSimpleMigrations\Model;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MigrationVersionTableGatewayAbstractFactory implements AbstractFactoryInterface
{
    const FACTORY_PATTERN = '/migrations\.versiontablegateway\.(.*)/';

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return preg_match(self::FACTORY_PATTERN, $requestedName)
        || preg_match(self::FACTORY_PATTERN, $requestedName);
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = NULL)
    {
        preg_match(self::FACTORY_PATTERN, $requestedName, $matches)
        || preg_match(self::FACTORY_PATTERN, $requestedName, $matches);
        $adapter_name = $matches[1];

        /** @var $dbAdapter \Zend\Db\Adapter\Adapter */
        $dbAdapter = $container->get($adapter_name);
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new MigrationVersion());
        return new TableGateway(MigrationVersion::TABLE_NAME, $dbAdapter, null, $resultSetPrototype);
    }


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

    }
}
