<?php

namespace ZfSimpleMigrations\Model;

use PHPUnit\Framework\TestCase;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceManager;

/**
 * @group unit
 */
class MigrationVersionTableGatewayAbstractFactoryTest extends TestCase
{
    public function testItIndicatesWhatServicesItCreates()
    {
        $serviceManager = $this->buildServiceManager();

        $factory = new MigrationVersionTableGatewayAbstractFactory();
        $this->assertTrue(
            $factory->canCreateServiceWithName(
                $serviceManager,
                'migrations.versiontablegateway.foo',
                'asdf'
            ),
            "should indicate it provides service for \$name"
        );

        $this->assertTrue(
            $factory->canCreateServiceWithName(
                $serviceManager,
                'asdf',
                'migrations.versiontablegateway.foo'
            ),
            "should indicate it provides service for \$requestedName"
        );

        $this->assertFalse(
            $factory->canCreateServiceWithName(
                $serviceManager,
                'asdf',
                'asdf'
            ),
            "should indicate it does not provide service for \$name or \$requestedName"
        );
    }

    public function testItReturnsATableGateway()
    {
        $serviceManager = $this->buildServiceManager();

        $factory = new MigrationVersionTableGatewayAbstractFactory();
        $instance = $factory->createServiceWithName($serviceManager, 'migrations.versiontablegateway.foo', 'asdf');
        $this->assertInstanceOf(
            TableGateway::class,
            $instance,
            "factory should return an instance of " . TableGateway::class . " when asked by \$name"
        );

        $instance2 = $factory->createServiceWithName($serviceManager, 'asdf', 'migrations.versiontablegateway.foo');
        $this->assertInstanceOf(
            TableGateway::class,
            $instance2,
            "factory should return an instance of " . TableGateway::class . " when asked by \$requestedName"
        );
    }

    private function buildServiceManager(): ServiceManager
    {
        $adapter = $this->prophesize(Adapter::class);

        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'foo',
            $adapter->reveal()
        );

        return $serviceManager;
    }
}
