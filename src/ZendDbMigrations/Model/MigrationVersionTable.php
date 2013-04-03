<?php
/**
 * User: Vladimir Garvardt
 * Date: 2/14/13
 * Time: 5:40 PM
 */

namespace ZendDbMigrations\Model;

use Zend\Db\TableGateway\TableGateway;

class MigrationVersionTable
{
    /**
     * @var \Zend\Db\TableGateway\TableGateway
     */
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function save($version)
    {
        $this->tableGateway->insert(array('version' => $version));
        return $this->tableGateway->lastInsertValue;
    }

    public function delete($version)
    {
        $this->tableGateway->delete(array('version' => $version));
    }

    public function applied($version)
    {
        $result = $this->tableGateway->select(array('version' => $version));
        return $result->count() > 0;
    }

    public function getCurrentVersion()
    {
        $result = $this->tableGateway->select(function ($select) {
            $select->order('version DESC')->limit(1);
        });
        if (!$result->count()) return 0;
        return $result->current()->getVersion();
    }
}