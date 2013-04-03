<?php

return array(
    'migrations' => array(
        'dir' => dirname(__FILE__) . '/../../../../migrations',
        'namespace' => 'ZendDbMigrations\Migrations',
        'show_log' => true
    ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'migration-version' => array(
                    'type' => 'simple',
                    'options' => array(
                        'route' => 'migration version [--env=]',
                        'defaults' => array(
                            'controller' => 'ZendDbMigrations\Controller\Migrate',
                            'action' => 'version'
                        )
                    )
                ),
                'migration-list' => array(
                    'type' => 'simple',
                    'options' => array(
                        'route' => 'migration list [--env=] [--all]',
                        'defaults' => array(
                            'controller' => 'ZendDbMigrations\Controller\Migrate',
                            'action' => 'list'
                        )
                    )
                ),
                'migration-migrate' => array(
                    'type' => 'simple',
                    'options' => array(
                        'route' => 'migration migrate [<version>] [--env=] [--force] [--down]',
                        'defaults' => array(
                            'controller' => 'ZendDbMigrations\Controller\Migrate',
                            'action' => 'migrate'
                        )
                    )
                ),
                'migration-generate' => array(
                    'type' => 'simple',
                    'options' => array(
                        'route' => 'migration generate [--env=]',
                        'defaults' => array(
                            'controller' => 'ZendDbMigrations\Controller\Migrate',
                            'action' => 'generateMigrationClass'
                        )
                    )
                )
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'ZendDbMigrations\Controller\Migrate' => 'ZendDbMigrations\Controller\MigrateController'
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
