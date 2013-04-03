ZfSimpleMigrations
==================

Simple Migrations for Zend Framework 2. Project originally based on [ZendDbMigrations](https://github.com/vadim-knyzev/ZendDbMigrations) but module author did not response for issues and pull-requests so fork became independent project.

Установка
-------------
Добавьте в composer.json проекта в секцию require
"knyzev/zend-db-migrations": "dev-master"

затем выполните
``` bash
php composer.phar self-update
php composer.phar update
```

Использование
-------------

Список добавляемых консольных комманд

``` bash
migration version - возвращает номер текущей версии
migration list [--all] - выводит список доступных миграций
migration migrate [<version>] [--force] [--down] - выполнить или откатить миграцию, номер версии необязательный параметр
migration generate - Сгенерировать каркас класса миграции
```

Все миграции по умолчанию будут хранится в каталоге
/project/migrations/*
поэтому нужно создать папку migrations или запускать команду генерации каркаса миграций с правами на запись в корневую директорию

В общем случае классы миграций должны иметь название вида 
Versionггггммддччммссс.php и реализовывать интерфейс ZfSimpleMigrations\Library\MigrationInterface

Пример класса миграции
``` php
<?php

namespace ZfSimpleMigrations\Migrations;

use ZfSimpleMigrations\Library\AbstractMigration;
use Zend\Db\Metadata\MetadataInterface;

class Version20121112230913 extends AbstractMigration
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

выполнить миграцию можно двумя способами
* запустив команду migration migrate без параметров
* или с указанием версии
    * `migration migrate 20121112230913`
    * `Version20121112230913` - здесь `20121112230913` будет версией миграции

http://vadim-knyzev.blogspot.com/