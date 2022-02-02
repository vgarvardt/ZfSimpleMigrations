<?php

namespace ZfSimpleMigrations\Model;

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

class MigrationVersionTable
{
    /** @var TableGateway */
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function save($version): int
    {
        if ($this->tableGateway->getAdapter()->getPlatform()->getName() === 'PostgreSQL') {
            return $this->savePg($version);
        }

        $this->tableGateway->insert(['version' => $version]);
        return $this->tableGateway->lastInsertValue;
    }

    protected function savePg($version): int
    {
        $sql = sprintf('INSERT INTO "%s" ("version") VALUES (?) RETURNING "id"', $this->tableGateway->getTable());
        $stmt = $this->tableGateway->getAdapter()->getDriver()->createStatement($sql);
        $result = $stmt->execute([$version]);
        return $result->current()["id"];
    }

    public function delete($version)
    {
        $this->tableGateway->delete(['version' => $version]);
    }

    public function applied($version): bool
    {
        $result = $this->tableGateway->select(['version' => $version]);
        return $result->count() > 0;
    }

    public function getCurrentVersion(): int
    {
        $result = $this->tableGateway->select(function (Select $select) {
            $select->order('version DESC')->limit(1);
        });

        if (!$result->count()) {
            return 0;
        }

        return $result->current()->getVersion();
    }
}
