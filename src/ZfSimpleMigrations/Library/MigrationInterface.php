<?php

namespace ZfSimpleMigrations\Library;

use Zend\Db\Metadata\MetadataInterface;

interface MigrationInterface
{
    /**
     * Get migrations queries
     *
     * @return array
     */
    public function getUpSql();

    /**
     * Get migration rollback queries
     *
     * @return array
     */
    public function getDownSql();

    /**
     * Apply migration
     *
     * @param MetadataInterface $schema
     */
    public function up(MetadataInterface $schema);

    /**
     * Rollback migration
     *
     * @param MetadataInterface $schema
     */
    public function down(MetadataInterface $schema);
}
