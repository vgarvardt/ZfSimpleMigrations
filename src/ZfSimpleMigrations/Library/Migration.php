<?php

namespace ZfSimpleMigrations\Library;

use ArrayIterator;
use Exception;
use FilesystemIterator;
use GlobIterator;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;
use SplFileInfo;
use Throwable;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Adapter\Driver\ConnectionInterface;
use Zend\Db\Adapter\Exception\InvalidQueryException;
use Zend\Db\Metadata\Metadata;
use Zend\Db\Sql\Ddl;
use Zend\Db\Sql\Sql;
use ZfSimpleMigrations\Library\OutputWriter;
use ZfSimpleMigrations\Model\MigrationVersion;
use ZfSimpleMigrations\Model\MigrationVersionTable;
use Interop\Container\ContainerInterface;
/**
 * Main migration logic
 */
class Migration
{
    /** @var string */
    protected $migrationsDir;
    /** @var string */
    protected $migrationsNamespace;
    /** @var Adapter */
    protected $adapter;
    /** @var ConnectionInterface */
    protected $connection;
    /** @var Metadata */
    protected $metadata;
    /** @var MigrationVersionTable */
    protected $migrationVersionTable;
    /** @var OutputWriter */
    protected $outputWriter;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @return OutputWriter
     */
    public function getOutputWriter(): OutputWriter
    {
        return $this->outputWriter;
    }

    /**
     * @param Adapter $adapter
     * @param array $config
     * @param MigrationVersionTable $migrationVersionTable
     * @param OutputWriter $writer
     * @throws MigrationException
     */
    public function __construct(
        Adapter $adapter,
        array $config,
        MigrationVersionTable $migrationVersionTable,
        OutputWriter $writer = null
    ) {
        $this->adapter = $adapter;
        $this->metadata = new Metadata($this->adapter);
        $this->connection = $this->adapter->getDriver()->getConnection();
        $this->migrationsDir = $config['dir'];
        $this->migrationsNamespace = $config['namespace'];
        $this->migrationVersionTable = $migrationVersionTable;
        $this->outputWriter = is_null($writer) ? new OutputWriter() : $writer;

        if (is_null($this->migrationsDir)) {
            throw new MigrationException('Migrations directory not set!');
        }

        if (is_null($this->migrationsNamespace)) {
            throw new MigrationException('Unknown namespaces!');
        }

        if (!is_dir($this->migrationsDir)) {
            if (!mkdir($this->migrationsDir, 0775)) {
                throw new MigrationException(sprintf('Failed to create migrations directory %s', $this->migrationsDir));
            }
        }

        $this->checkCreateMigrationTable();
    }

    /**
     * Create migrations table of not exists
     */
    protected function checkCreateMigrationTable()
    {
        $table = new Ddl\CreateTable(MigrationVersion::TABLE_NAME);
        $table->addColumn(new Ddl\Column\Integer('id', false, null, ['autoincrement' => true]));
        $table->addColumn(new Ddl\Column\BigInteger('version'));
        $table->addConstraint(new Ddl\Constraint\PrimaryKey('id'));
        $table->addConstraint(new Ddl\Constraint\UniqueKey('version'));

        $sql = new Sql($this->adapter);

        try {
            $this->adapter->query($sql->getSqlStringForSqlObject($table), Adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            // currently there are no db-independent way to check if table exists
            // so we assume that table exists when we catch exception
        }
    }

    /**
     * @return int
     */
    public function getCurrentVersion(): int
    {
        return $this->migrationVersionTable->getCurrentVersion();
    }

    /**
     * @param int $version target migration version, if not set all not applied available migrations will be applied
     * @param bool $force force apply migration
     * @param bool $down rollback migration
     * @param bool $dryRun
     * @throws MigrationException
     * @throws ReflectionException
     */
    public function migrate($version = null, bool $force = false, bool $down = false, bool $dryRun = false)
    {
        $migrations = $this->getMigrationClasses($force);

        if (!is_null($version) && !$this->hasMigrationVersions($migrations, $version)) {
            throw new MigrationException(sprintf('Migration version %s is not found!', $version));
        }

        $currentMigrationVersion = $this->migrationVersionTable->getCurrentVersion();
        if (!is_null($version) && $version == $currentMigrationVersion && !$force) {
            throw new MigrationException(sprintf('Migration version %s is current version!', $version));
        }

        if ($version && $force) {
            foreach ($migrations as $migration) {
                if ($migration['version'] == $version) {
                    // if existing migration is forced to apply - delete its information from migrated
                    // to avoid duplicate key error
                    if (!$down) {
                        $this->migrationVersionTable->delete($migration['version']);
                    }
                    $this->applyMigration($migration, $down, $dryRun);
                    break;
                }
            }
            // target migration version not set or target version is greater than
            // the last applied migration -> apply migrations
        } elseif (is_null($version) || (!is_null($version) && $version > $currentMigrationVersion)) {
            foreach ($migrations as $migration) {
                if ($migration['version'] > $currentMigrationVersion) {
                    if (is_null($version) || (!is_null($version) && $version >= $migration['version'])) {
                        $this->applyMigration($migration, false, $dryRun);
                    }
                }
            }
            // target migration version is set -> rollback migration
        } elseif (!is_null($version) && $version < $currentMigrationVersion) {
            $migrationsByDesc = $this->sortMigrationsByVersionDesc($migrations);
            foreach ($migrationsByDesc as $migration) {
                if ($migration['version'] > $version && $migration['version'] <= $currentMigrationVersion) {
                    $this->applyMigration($migration, true, $dryRun);
                }
            }
        }
    }

    /**
     * @param ArrayIterator $migrations
     * @return ArrayIterator
     */
    public function sortMigrationsByVersionDesc(ArrayIterator $migrations): ArrayIterator
    {
        $sortedMigrations = clone $migrations;

        $sortedMigrations->uasort(function ($a, $b) {
            if ($a['version'] == $b['version']) {
                return 0;
            }

            return ($a['version'] > $b['version']) ? -1 : 1;
        });

        return $sortedMigrations;
    }

    /**
     * Check migrations classes existence
     *
     * @param ArrayIterator $migrations
     * @param int $version
     * @return bool
     */
    public function hasMigrationVersions(ArrayIterator $migrations, int $version): bool
    {
        foreach ($migrations as $migration) {
            if ($migration['version'] == $version) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ArrayIterator $migrations
     * @return int
     */
    public function getMaxMigrationVersion(ArrayIterator $migrations): int
    {
        $versions = [];
        foreach ($migrations as $migration) {
            $versions[] = $migration['version'];
        }

        sort($versions, SORT_NUMERIC);
        $versions = array_reverse($versions);

        return count($versions) > 0 ? $versions[0] : 0;
    }

    /**
     * @param bool $all
     * @return ArrayIterator
     * @throws ReflectionException
     */
    public function getMigrationClasses(bool $all = false): ArrayIterator
    {
        $classes = new ArrayIterator();

        $pattern = sprintf('%s/Version*.php', $this->migrationsDir);
        $iterator = new GlobIterator($pattern, FilesystemIterator::KEY_AS_FILENAME);
        foreach ($iterator as $item) {
            /** @var $item SplFileInfo */
            if (preg_match('/(Version(\d+))\.php/', $item->getFilename(), $matches)) {
                $applied = $this->migrationVersionTable->applied($matches[2]);
                if ($all || !$applied) {
                    $className = $this->migrationsNamespace . '\\' . $matches[1];

                    if (!class_exists($className)) {
                        require_once $this->migrationsDir . '/' . $item->getFilename();
                    }

                    if (class_exists($className)) {
                        $reflectionClass = new ReflectionClass($className);
                        $reflectionDescription = new ReflectionProperty($className, 'description');

                        if ($reflectionClass->implementsInterface(MigrationInterface::class)) {
                            $classes->append([
                                'version' => $matches[2],
                                'class' => $className,
                                'description' => $reflectionDescription->getValue(),
                                'applied' => $applied,
                            ]);
                        }
                    }
                }
            }
        }

        $classes->uasort(function ($a, $b) {
            if ($a['version'] == $b['version']) {
                return 0;
            }

            return ($a['version'] < $b['version']) ? -1 : 1;
        });

        return $classes;
    }

    /**
     * @throws MigrationException
     */
    protected function applyMigration(array $migration, $down = false, $dryRun = false)
    {
        $this->connection->beginTransaction();

        try {
            /** @var AbstractMigration $migrationObject */
            $migrationObject = new $migration['class']($this->metadata, $this->outputWriter);

            if ($migrationObject instanceof MigrationInterface) {
                if (is_null($this->serviceLocator)) {
                    throw new RuntimeException(
                        sprintf(
                            'Migration class %s requires a ServiceLocator, but there is no instance available.',
                            get_class($migrationObject)
                        )
                    );
                }

                $migrationObject->setServiceLocator($this->serviceLocator);
            }

            if ($migrationObject instanceof AdapterAwareInterface) {
                if (is_null($this->adapter)) {
                    throw new RuntimeException(
                        sprintf(
                            'Migration class %s requires an Adapter, but there is no instance available.',
                            get_class($migrationObject)
                        )
                    );
                }

                $migrationObject->setDbAdapter($this->adapter);
            }

            $this->outputWriter->writeLine(
                sprintf(
                    '%sExecute migration class %s %s',
                    $dryRun ? '[DRY RUN] ' : '',
                    $migration['class'],
                    $down ? 'down' : 'up'
                )
            );

            if (!$dryRun) {
                $sqlList = $down ? $migrationObject->getDownSql() : $migrationObject->getUpSql();
                foreach ($sqlList as $sql) {
                    $this->outputWriter->writeLine("Execute query:\n\n" . $sql);
                    $this->connection->execute($sql);
                }
            }

            if ($down) {
                $this->migrationVersionTable->delete($migration['version']);
            } else {
                $this->migrationVersionTable->save($migration['version']);
            }
            $this->connection->commit();
        } catch (InvalidQueryException $e) {
            $this->connection->rollback();
            throw new MigrationException('Could not apply migration because of the invalid query', 0, $e);
        } catch (Throwable $e) {
            $this->connection->rollback();
            throw new MigrationException('Could not apply migration', 0, $e);
        }
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return $this
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator): Migration
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator(): ServiceLocatorInterface
    {
        return $this->serviceLocator;
    }
}
