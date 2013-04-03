<?php

namespace ZfSimpleMigrations\Library;

/**
 * Migration skeleton class generator
 */
class MigrationSkeletonGenerator
{
    protected $migrationsDir;
    protected $migrationNamespace;

    /**
     * @param string $migrationsDir migrations working directory
     * @param string $migrationsNamespace migrations namespace
     * @throws MigrationException
     */
    public function __construct($migrationsDir, $migrationsNamespace)
    {
        $this->migrationsDir = $migrationsDir;
        $this->migrationNamespace = $migrationsNamespace;

        if (!is_dir($this->migrationsDir)) {
            if (!mkdir($this->migrationsDir, 0775)) {
                throw new MigrationException(sprintf('Failed to create migrations directory %s', $this->migrationsDir));
            }
        } elseif (!is_writable($this->migrationsDir)) {
            throw new MigrationException(sprintf('Migrations directory is not writable %s', $this->migrationsDir));
        }
    }

    /**
     * Generate new migration skeleton class
     *
     * @return string path to new skeleton class file
     * @throws MigrationException
     */
    public function generate()
    {
        $className = 'Version' . date('YmdHis', time());
        $classPath = $this->migrationsDir . DIRECTORY_SEPARATOR . $className . '.php';

        if (file_exists($classPath)) {
            throw new MigrationException(sprintf('Migration %s exists!', $className));
        }
        file_put_contents($classPath, $this->getTemplate($className));

        return $classPath;
    }

    /**
     * Get migration skeleton class raw text
     *
     * @param string $className
     * @return string
     */
    protected function getTemplate($className)
    {
        return sprintf('<?php

namespace %s;

use ZfSimpleMigrations\Library\AbstractMigration;
use Zend\Db\Metadata\MetadataInterface;

class %s extends AbstractMigration
{
    public static $description = "Migration description";

    public function up(MetadataInterface $schema)
    {
        //$this->addSql(/*Sql instruction*/);
    }

    public function down(MetadataInterface $schema)
    {
        //throw new \RuntimeException(\'No way to go down!\');
        //$this->addSql(/*Sql instruction*/);
    }
}
', $this->migrationNamespace, $className);
    }
}

?>
