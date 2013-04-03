<?php

namespace ZendDbMigrations\Library;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\Pdo\Pdo;
use Zend\Db\Adapter\Exception\InvalidQueryException;
use Zend\Db\Metadata\Metadata;
use ZendDbMigrations\Library\OutputWriter;
use ZendDbMigrations\Model\MigrationVersionTable;

/**
 * Основная логика работы с миграциями
 */
class Migration
{
    const MIGRATION_TABLE = 'migration_version';

    protected $migrationClassFolder;
    protected $namespaceMigrationsClasses;
    protected $adapter;
    /**
     * @var \Zend\Db\Adapter\Driver\ConnectionInterface
     */
    protected $connection;
    protected $metadata;
    protected $migrationVersionTable;
    protected $outputWriter;

    /**
     * @param \Zend\Db\Adapter\Adapter $adapter
     * @param array $config
     * @param \ZendDbMigrations\Model\MigrationVersionTable $migrationVersionTable
     * @param OutputWriter $writer
     * @throws \Exception
     */
    public function __construct(Adapter $adapter, array $config, MigrationVersionTable $migrationVersionTable, OutputWriter $writer = null)
    {
        $this->adapter = $adapter;
        $this->metadata = new Metadata($this->adapter);
        $this->connection = $this->adapter->getDriver()->getConnection();
        $this->migrationClassFolder = $config['dir'];
        $this->namespaceMigrationsClasses = $config['namespace'];
        $this->migrationVersionTable = $migrationVersionTable;
        $this->outputWriter = is_null($writer) ? new OutputWriter() : $writer;

        if (is_null($this->migrationClassFolder))
            throw new \Exception('Unknown directory!');

        if (is_null($this->namespaceMigrationsClasses))
            throw new \Exception('Unknown namespaces!');

        if (!file_exists($this->migrationClassFolder))
            if (!mkdir($this->migrationClassFolder, 0775))
                throw new \Exception(sprintf('Not permitted to created directory %s',
                    $this->migrationClassFolder));

        $this->checkCreateMigrationTable();
    }

    /**
     * Создать таблицу миграций
     */
    protected function checkCreateMigrationTable()
    {
        if (strpos($this->connection->getDriverName(), 'mysql') !== false) {
            $sql = <<<TABLE
CREATE TABLE IF NOT EXISTS `%s` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `version` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `version` (`version`)
);
TABLE;
        } else {
            $sql = <<<TABLE
CREATE TABLE IF NOT EXISTS "%s" (
  "id"  SERIAL NOT NULL,
  "version" bigint NOT NULL,
  PRIMARY KEY ("id")
);
TABLE;
        }
        $this->connection->execute(sprintf($sql, Migration::MIGRATION_TABLE));
    }

    /**
     * @param int $version Номер версии к которой нужно мигрировать, если не указано то будут выполнены все новые миграции
     * @param bool $force Применять указанную миграцию без лишних вопросов
     * @param bool $down Применить откат миграции указанной версии
     * @throws MigrationException
     */
    public function migrate($version = null, $force = false, $down = false)
    {
        $migrations = $this->getMigrationClasses($force);

        if (!is_null($version) && !$this->hasMigrationVersion($migrations, $version)) {
            throw new MigrationException(sprintf('Migration version %s is not found!', $version));
        }

        $currentMigrationVersion = $this->migrationVersionTable->getCurrentVersion();
        if (!is_null($version) && $version == $currentMigrationVersion && !$force) {
            throw new MigrationException(sprintf('Migration version %s is current version!', $version));
        }

        $this->connection->beginTransaction();
        try {
            if ($version && $force) {
                foreach ($migrations as $migration) {
                    if ($migration['version'] == $version) {
                        // if existing migration is forced to apply - delete it's information from migrated
                        // to avoid duplicate key error
                        if (!$down) $this->migrationVersionTable->delete($migration['version']);
                        $this->applyMigration($migration, $down);
                        break;
                    }
                }
                //номер миграции не указан либо указанный номер больше последней выполненной миграции -> миграция добавления
            } elseif (is_null($version) || (!is_null($version) && $version > $currentMigrationVersion)) {
                foreach ($migrations as $migration) {
                    if ($migration['version'] > $currentMigrationVersion) {
                        if (is_null($version) || (!is_null($version) && $version >= $migration['version'])) {
                            $this->applyMigration($migration);
                        }
                    }
                }
                //номер миграции указан и версия ниже текущей -> откат миграции
            } elseif (!is_null($version) && $version < $currentMigrationVersion) {
                $migrationsByDesc = $this->sortMigrationByVersionDesc($migrations);
                foreach ($migrationsByDesc as $migration) {
                    if ($migration['version'] > $version && $migration['version'] <= $currentMigrationVersion) {
                        $this->applyMigration($migration, true);
                    }
                }
            }

            $this->connection->commit();
        } catch (InvalidQueryException $e) {
            $this->connection->rollback();
            $msg = sprintf('%s: "%s"; File: %s; Line #%d', $e->getMessage(), $e->getPrevious()->getMessage(), $e->getFile(), $e->getLine());
            throw new MigrationException($msg);
        } catch (\Exception $e) {
            $this->connection->rollback();
            $msg = sprintf('%s; File: %s; Line #%d', $e->getMessage(), $e->getFile(), $e->getLine());
            throw new MigrationException($msg);
        }
    }

    /**
     * Отсортировать миграции по версии в обратном порядке
     * @param \ArrayIterator $migrations
     * @return \ArrayIterator
     */
    public function sortMigrationByVersionDesc(\ArrayIterator $migrations)
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
     * Проверить существование класса для номера миграции
     * @param \ArrayIterator $migrations
     * @param integer $version
     * @return boolean
     */
    public function hasMigrationVersion(\ArrayIterator $migrations, $version)
    {
        foreach ($migrations as $migration) {
            if ($migration['version'] == $version) return true;
        }

        return false;
    }

    /**
     * Получить номер максимальной версии миграции
     * @param \ArrayIterator $migrations
     * @return integer
     */
    public function getMaxMigrationNumber(\ArrayIterator $migrations)
    {
        $versions = array();
        foreach ($migrations as $migration) {
            $versions[] = $migration['version'];
        }

        sort($versions, SORT_NUMERIC);
        $versions = array_reverse($versions);

        return count($versions) > 0 ? $versions[0] : 0;
    }

    /**
     * Найти список классов миграций
     *
     * @param bool $all
     * @return \ArrayIterator
     */
    public function getMigrationClasses($all = false)
    {
        $classes = new \ArrayIterator();

        $iterator = new \GlobIterator(sprintf('%s/Version*.php', $this->migrationClassFolder), \FilesystemIterator::KEY_AS_FILENAME);
        foreach ($iterator as $item) {
            /** @var $item \SplFileInfo */
            if (preg_match('/(Version(\d+))\.php/', $item->getFilename(), $matches)) {
                $applied = $this->migrationVersionTable->applied($matches[2]);
                if ($all || !$applied) {
                    $className = $this->namespaceMigrationsClasses . '\\' . $matches[1];

                    if (!class_exists($className))
                        require_once $this->migrationClassFolder . '/' . $item->getFilename();

                    if (class_exists($className)) {
                        $reflectionClass = new \ReflectionClass($className);
                        $reflectionDescription = new \ReflectionProperty($className, 'description');

                        if ($reflectionClass->implementsInterface('ZendDbMigrations\Library\MigrationInterface')) {
                            $classes->append(array(
                                'version' => $matches[2],
                                'class' => $className,
                                'description' => $reflectionDescription->getValue(),
                                'applied' => $applied,
                            ));
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

    protected function applyMigration(array $migration, $down = false)
    {
        /** @var $migrationObject AbstractMigration */
        $migrationObject = new $migration['class']($this->metadata);

        $this->outputWriter->write(sprintf("Execute migration class %s %s", $migration['class'], $down ? 'down' : 'up'));

        $sqlList = $down ? $migrationObject->getDownSql() : $migrationObject->getUpSql();
        foreach ($sqlList as $sql) {
            $this->outputWriter->write("Execute sql code  \n\n" . $sql . "\n");
            $this->connection->execute($sql);
        }

        if ($down) {
            $this->migrationVersionTable->delete($migration['version']);
        } else {
            $this->migrationVersionTable->save($migration['version']);
        }
    }
}

?>
