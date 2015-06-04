<?php
/**
 * @category WebPT
 * @copyright Copyright (c) 2015 WebPT, INC
 * @author jgiberson
 * 6/4/15 2:54 PM
 */

namespace ZfSimpleMigrations\UnitTest\Model;


use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceManager;
use ZfSimpleMigrations\Model\MigrationVersionTable;
use ZfSimpleMigrations\Model\MigrationVersionTableAbstractFactory;

class MigrationVersionTableAbstractFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ServiceManager */
    protected $service_manager;

    protected function setUp()
    {
        parent::setUp();

        $this->service_manager = new ServiceManager();
        $this->service_manager->setService('migrations.versiontablegateway.foo',
            $this->getMock(TableGateway::class, [], [], '', false));
    }


    public function test_it_indicates_what_services_it_creates()
    {
        $factory = new MigrationVersionTableAbstractFactory();
        $this->assertTrue($factory->canCreateServiceWithName($this->service_manager,
        'migrations.versiontable.foo', 'asdf'), "should indicate it provides service for \$name");

        $factory = new MigrationVersionTableAbstractFactory();
        $this->assertTrue($factory->canCreateServiceWithName($this->service_manager,
            'asdf', 'migrations.versiontable.foo'), "should indicate it provides service for \$requestedName");

        $factory = new MigrationVersionTableAbstractFactory();
        $this->assertFalse($factory->canCreateServiceWithName($this->service_manager,
            'asdf', 'asdf'), "should indicate it does not provide service for \$name or \$requestedName");
    }

    public function test_it_returns_a_migration_version_table()
    {
        $factory = new MigrationVersionTableAbstractFactory();
        $instance = $factory->createServiceWithName($this->service_manager, 'migrations.versiontable.foo', 'asdf');
        $this->assertInstanceOf(MigrationVersionTable::class, $instance,
        "factory should return an instance of " . MigrationVersionTable::class . " when asked by \$name");

        $instance2 = $factory->createServiceWithName($this->service_manager, 'asdf', 'migrations.versiontable.foo');
        $this->assertInstanceOf(MigrationVersionTable::class, $instance2,
            "factory should return an instance of " . MigrationVersionTable::class . " when asked by \$requestedName");
    }
}
