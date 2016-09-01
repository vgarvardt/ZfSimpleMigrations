<?php
namespace ZfSimpleMigrations\Controller;

use Zend\Mvc\Console\Controller\AbstractConsoleController;

use Zend\Mvc\MvcEvent;
use Zend\Console\Request as ConsoleRequest;
use ZfSimpleMigrations\Library\Migration;
use ZfSimpleMigrations\Library\MigrationSkeletonGenerator;

/**
 * Migration commands controller
 */
class MigrateController extends AbstractConsoleController
{
    /**
     * @var \ZfSimpleMigrations\Library\Migration
     */
    protected $migration;
    /** @var  MigrationSkeletonGenerator */
    protected $skeleton_generator;

    /**
     * @return MigrationSkeletonGenerator
     */
    public function getSkeletonGenerator()
    {
        return $this->skeleton_generator;
    }

    /**
     * @param MigrationSkeletonGenerator $skeleton_generator
     * @return self
     */
    public function setSkeletonGenerator($skeleton_generator)
    {
        $this->skeleton_generator = $skeleton_generator;
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
     * @return \Zend\Stdlib\RequestInterface
     */
    public function getRequest()
    {
        return parent::getRequest();
    }

    /**
     * Get current migration version
     *
     * @return int
     */
    public function versionAction()
    {
        return sprintf("Current version %s\n", $this->getMigration()->getCurrentVersion());
    }

    /**
     * List migrations - not applied by default, all with 'all' flag.
     *
     * @return string
     */
    public function listAction()
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
     */
    public function applyAction()
    {
        $migrations = $this->getMigration()->getMigrationClasses();
        $currentMigrationVersion = $this->getMigration()->getCurrentVersion();

        $version = $this->getRequest()->getParam('version');
        $force = $this->getRequest()->getParam('force');
        $down = $this->getRequest()->getParam('down');
        $fake = $this->getRequest()->getParam('fake');
        $name = $this->getRequest()->getParam('name');

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
     */
    public function generateSkeletonAction()
    {
        $classPath = $this->getSkeletonGenerator()->generate();

        return sprintf("Generated skeleton class @ %s\n", realpath($classPath));
    }

    /**
     * @return Migration
     */
    public function getMigration()
    {
        return $this->migration;
    }

    /**
     * @param Migration $migration
     * @return self
     */
    public function setMigration(Migration $migration)
    {
        $this->migration = $migration;
        return $this;
    }


}
