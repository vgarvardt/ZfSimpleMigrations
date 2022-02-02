<?php

namespace ZfSimpleMigrations\Controller;

use ReflectionException;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\Console\Request as ConsoleRequest;
use ZfSimpleMigrations\Library\Migration;
use ZfSimpleMigrations\Library\MigrationException;
use ZfSimpleMigrations\Library\MigrationSkeletonGenerator;

/**
 * Migration commands controller
 */
class MigrateController extends AbstractActionController
{
    /**  @var Migration */
    protected $migration;
    /** @var  MigrationSkeletonGenerator */
    protected $skeletonGenerator;

    /**
     * @return MigrationSkeletonGenerator
     */
    public function getSkeletonGenerator(): MigrationSkeletonGenerator
    {
        return $this->skeletonGenerator;
    }

    /**
     * @param MigrationSkeletonGenerator $skeletonGenerator
     * @return self
     */
    public function setSkeletonGenerator(MigrationSkeletonGenerator $skeletonGenerator): self
    {
        $this->skeletonGenerator = $skeletonGenerator;
        return $this;
    }

    public function onDispatch(MvcEvent $e)
    {
        if (!$this->getRequest() instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        return parent::onDispatch($e);
    }

    /**
     * Overridden only for PHPDoc return value for IDE code helpers
     *
     * @return ConsoleRequest
     */
    public function getRequest(): ConsoleRequest
    {
        return parent::getRequest();
    }

    /**
     * Get current migration version
     *
     * @return string
     */
    public function versionAction(): string
    {
        return sprintf("Current version %s\n", $this->getMigration()->getCurrentVersion());
    }

    /**
     * List migrations - not applied by default, all with 'all' flag.
     *
     * @return string
     * @throws ReflectionException
     */
    public function listAction(): string
    {
        $migrations = $this->getMigration()->getMigrationClasses($this->getRequest()->getParam('all'));
        $list = [];
        foreach ($migrations as $m) {
            $list[] = sprintf("%s %s - %s", $m['applied'] ? '-' : '+', $m['version'], $m['description']);
        }
        return (empty($list) ? 'No migrations available.' : implode("\n", $list)) . "\n";
    }

    /**
     * Apply migration
     * @throws ReflectionException
     * @throws MigrationException
     */
    public function applyAction(): string
    {
        $migrations = $this->getMigration()->getMigrationClasses();
        $currentMigrationVersion = $this->getMigration()->getCurrentVersion();

        $version = $this->getRequest()->getParam('version');
        $force = $this->getRequest()->getParam('force');
        $down = $this->getRequest()->getParam('down');
        $fake = $this->getRequest()->getParam('fake');

        if (is_null($version) && $force) {
            return "Can't force migration apply without migration version explicitly set.";
        }
        if (is_null($version) && $fake) {
            return "Can't fake migration apply without migration version explicitly set.";
        }
        if (!$force && is_null($version) && $currentMigrationVersion >= $this->getMigration()->getMaxMigrationVersion($migrations)) {
            return "No migrations to apply.\n";
        }

        $this->getMigration()->migrate($version, $force, $down, $fake);
        return "Migrations applied!\n";
    }

    /**
     * Generate new migration skeleton class
     * @throws MigrationException
     */
    public function generateSkeletonAction(): string
    {
        $classPath = $this->getSkeletonGenerator()->generate();

        return sprintf("Generated skeleton class @ %s\n", realpath($classPath));
    }

    /**
     * @return Migration
     */
    public function getMigration(): Migration
    {
        return $this->migration;
    }

    /**
     * @param Migration $migration
     * @return self
     */
    public function setMigration(Migration $migration): self
    {
        $this->migration = $migration;
        return $this;
    }


}
