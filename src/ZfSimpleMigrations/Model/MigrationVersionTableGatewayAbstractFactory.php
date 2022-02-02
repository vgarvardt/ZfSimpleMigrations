<?php

namespace ZfSimpleMigrations\Model;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Interop\Container\ContainerInterface;

class MigrationVersionTableGatewayAbstractFactory implements AbstractFactoryInterface
{
    const FACTORY_PATTERN = '/migrations\.versiontablegateway\.(.*)/';
        
    /**
     * Determine if we can create a service with name
     *
     * @param  mixed $container
     * @param  mixed $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName) {
        return preg_match(self::FACTORY_PATTERN, $requestedName);
    }
    
    /**
     * Create service with name
     *
     * @param  mixed $container
     * @param  mixed $requestedName
     * @param  mixed $options
     * @return TableGateway 
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {
        preg_match(self::FACTORY_PATTERN, $requestedName, $matches)
        || preg_match(self::FACTORY_PATTERN, $requestedName, $matches);
        $adapter_name = $matches[1];

        /** @var $dbAdapter \Zend\Db\Adapter\Adapter */
        $dbAdapter = $container->get($adapter_name);
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new MigrationVersion());
        return new TableGateway(MigrationVersion::TABLE_NAME, $dbAdapter, null, $resultSetPrototype);
    }
}
