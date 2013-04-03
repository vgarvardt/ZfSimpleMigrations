<?php
namespace ZfSimpleMigrations\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\Console\Request as ConsoleRequest;
use ZfSimpleMigrations\Library\Migration;
use ZfSimpleMigrations\Library\MigrationException;
use ZfSimpleMigrations\Library\GeneratorMigrationClass;
use ZfSimpleMigrations\Library\OutputWriter;

/**
 * Контроллер обеспечивает вызов комманд миграций
 */
class MigrateController extends AbstractActionController
{
    /**
     * @var \ZfSimpleMigrations\Library\Migration
     */
    protected $migration;
    /**
     * @var \ZfSimpleMigrations\Model\MigrationVersionTable
     */
    protected $migrationVersionTable;
    /**
     * @var OutputWriter
     */
    protected $output;

    public function onDispatch(MvcEvent $e)
    {
        if (!$this->getRequest() instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        /** @var $adapter \Zend\Db\Adapter\Adapter */
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $config = $this->getServiceLocator()->get('Configuration');

        $console = $this->getServiceLocator()->get('console');

        if ($config['migrations']['show_log']) {
            $this->output = new OutputWriter(function ($message) use ($console) {
                $console->write($message . "\n");
            });
        }


        $this->migration = new Migration($adapter, $config['migrations'], $this->getMigrationVersionTable(), $this->output);

        return parent::onDispatch($e);
    }

    /**
     * Overrided only for PHPDoc return value for IDE code helpers
     *
     * @return ConsoleRequest
     */
    public function getRequest()
    {
        return parent::getRequest();
    }

    /**
     * Получить текущую версию миграции
     * @return integer
     */
    public function versionAction()
    {
        return sprintf("Current version %s\n", $this->getMigrationVersionTable()->getCurrentVersion());
    }

    public function listAction()
    {
        $migrations = $this->migration->getMigrationClasses($this->getRequest()->getParam('all'));
        $list = array();
        foreach ($migrations as $m) {
            $list[] = sprintf("%s %s - %s", $m['applied'] ? '-' : '+', $m['version'], $m['description']);
        }
        return (empty($list) ? 'No migrations to execute.' : implode("\n", $list)) . "\n";
    }

    /**
     * Мигрировать
     */
    public function applyAction()
    {
        $version = $this->getRequest()->getParam('version');

        $migrations = $this->migration->getMigrationClasses();
        $currentMigrationVersion = $this->getMigrationVersionTable()->getCurrentVersion();
        $force = $this->getRequest()->getParam('force');

        if (is_null($version) && $force) {
            return "Can't force migrate without migration version explicitly set.";
        }
        if (!$force && is_null($version) && $currentMigrationVersion >= $this->migration->getMaxMigrationNumber($migrations)) {
            return "No migrations to execute.\n";
        }

        try {
            $this->migration->migrate($version, $force, $this->getRequest()->getParam('down'));
            return "Migrations executed!\n";
        } catch (MigrationException $e) {
            return "ZfSimpleMigrations\\Library\\MigrationException\n" . $e->getMessage() . "\n";
        }
    }

    /**
     * Сгенерировать каркасный класс для новой миграции
     */
    public function generateMigrationClassAction()
    {
        $config = $this->getServiceLocator()->get('Configuration');

        $generator = new GeneratorMigrationClass($config['migrations']['dir'], $config['migrations']['namespace']);
        $classPath = $generator->generate();

        return sprintf("Generated class %s\n", realpath($classPath));
    }

    /**
     * @return \ZfSimpleMigrations\Model\MigrationVersionTable
     */
    protected function getMigrationVersionTable()
    {
        if (!$this->migrationVersionTable) {
            $this->migrationVersionTable = $this->getServiceLocator()->get('ZfSimpleMigrations\Model\MigrationVersionTable');
        }
        return $this->migrationVersionTable;
    }
}
