# ZfSimpleMigrations

Simple Migrations for Zend Framework 2. Project originally based on [ZendDbMigrations](https://github.com/vadim-knyzev/ZendDbMigrations) but module author did not response for issues and pull-requests so fork became independent project.

## Installation

### Using composer

```bash
php composer.phar require vgarvardt/zf-simple-migrations:dev-master
php composer.phar update
```
add `ZfSimpleMigrations` to the `modules` array in application.config.php

## Usage

### Available commands

* `migration version` - show last applied migration
* `migration list [--all]` - list available migrations (`all` includes applied migrations)
* `migration apply [<version>] [--force] [--down] [--fake]` - apply or rollback migration
* `migration generate` - generate migration skeleton class

Migration classes are stored in `/path/to/project/migrations/` dir by default.

Generic migration class has name `Version<YmdHis>` and implement `ZfSimpleMigrations\Library\MigrationInterface`.

### Migration class example

``` php
<?php

namespace ZfSimpleMigrations\Migrations;

use ZfSimpleMigrations\Library\AbstractMigration;
use Zend\Db\Metadata\MetadataInterface;

class Version20130403165433 extends AbstractMigration
{
    public static $description = "Migration description";

    public function up(MetadataInterface $schema)
    {
        //$this->addSql(/*Sql instruction*/);
    }

    public function down(MetadataInterface $schema)
    {
        //$this->addSql(/*Sql instruction*/);
    }
}
```

### Accessing ServiceLocator In Migration Class

By implementing the `Zend\ServiceManager\ServiceLocatorAwareInterface` in your migration class you get access to the
ServiceLocator used in the application.

``` php
<?php

namespace ZfSimpleMigrations\Migrations;

use ZfSimpleMigrations\Library\AbstractMigration;
use Zend\Db\Metadata\MetadataInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class Version20130403165433 extends AbstractMigration
                            implements ServiceLocatorAwareInterface
{
    public static $description = "Migration description";

    /** @var ServiceLocatorInterface */
    protected $serviceLocator;

    public function up(MetadataInterface $schema)
    {
         //$this->getServiceLocator()->get(/*Get service by alias*/);
         //$this->addSql(/*Sql instruction*/);

    }

    public function down(MetadataInterface $schema)
    {
        //$this->getServiceLocator()->get(/*Get service by alias*/);
        //$this->addSql(/*Sql instruction*/);
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
```
