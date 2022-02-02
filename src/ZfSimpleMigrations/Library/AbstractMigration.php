<?php

namespace ZfSimpleMigrations\Library;

use Zend\Db\Metadata\MetadataInterface;
use ZfSimpleMigrations\Library\MigrationInterface;
use Interop\Container\ContainerInterface;

abstract class AbstractMigration implements MigrationInterface
{
    private $sql = [];
    private $metadata;
    private $writer;
    protected $serviceLocator;

    public function __construct(MetadataInterface $metadata, OutputWriter $writer)
    {
        $this->metadata = $metadata;
        $this->writer = $writer;
    }
    
    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function setServiceLocator(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
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
        $this->sql = [];
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
        $this->sql = [];
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
