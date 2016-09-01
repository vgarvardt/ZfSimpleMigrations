<?php


namespace ZfSimpleMigrations\Model;


use Interop\Container\ContainerInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class MigrationVersionTableAbstractFactory implements AbstractFactoryInterface
{
    const FACTORY_PATTERN = '/migrations\.versiontable\.(.*)/';

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return preg_match(self::FACTORY_PATTERN, $requestedName) || preg_match(self::FACTORY_PATTERN, $requestedName);

    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = NULL)
    {
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
