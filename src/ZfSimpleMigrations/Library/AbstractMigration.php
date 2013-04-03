<?php

namespace ZfSimpleMigrations\Library;

use Zend\Db\Metadata\MetadataInterface;
use ZfSimpleMigrations\Library\MigrationInterface;

abstract class AbstractMigration implements MigrationInterface
{
    private $sql = array();
    private $metadata;
    private $writer;

    public function __construct(MetadataInterface $metadata, OutputWriter $writer)
    {
        $this->metadata = $metadata;
        $this->writer = $writer;
    }

    /**
     * Add migration query
     *
     * @param string $sql
     */
    protected function addSql($sql)
    {
        $this->sql[] = $sql;
    }

    /**
     * Get migration queries
     *
     * @return array
     */
    public function getUpSql()
    {
        $this->sql = array();
        $this->up($this->metadata);

        return $this->sql;
    }

    /**
     * Get migration rollback queries
     *
     * @return array
     */
    public function getDownSql()
    {
        $this->sql = array();
        $this->down($this->metadata);

        return $this->sql;
    }

    /**
     * @return OutputWriter
     */
    protected function getWriter()
    {
        return $this->writer;
    }
}
