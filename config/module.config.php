<?php

return array(
    'migrations' => array(
        'dir' => dirname(__FILE__) . '/../../../../migrations',
        'namespace' => 'ZfSimpleMigrations\Migrations',
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
                            'controller' => 'ZfSimpleMigrations\Controller\Migrate',
                            'action' => 'version'
                        )
                    )
                ),
                'migration-list' => array(
                    'type' => 'simple',
                    'options' => array(
                        'route' => 'migration list [--env=] [--all]',
                        'defaults' => array(
                            'controller' => 'ZfSimpleMigrations\Controller\Migrate',
                            'action' => 'list'
                        )
                    )
                ),
                'migration-apply' => array(
                    'type' => 'simple',
                    'options' => array(
                        'route' => 'migration apply [<version>] [--env=] [--force] [--down]',
                        'defaults' => array(
                            'controller' => 'ZfSimpleMigrations\Controller\Migrate',
                            'action' => 'apply'
                        )
                    )
                ),
                'migration-generate' => array(
                    'type' => 'simple',
                    'options' => array(
                        'route' => 'migration generate [--env=]',
                        'defaults' => array(
                            'controller' => 'ZfSimpleMigrations\Controller\Migrate',
                            'action' => 'generateSkeleton'
                        )
                    )
                )
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'ZfSimpleMigrations\Controller\Migrate' => 'ZfSimpleMigrations\Controller\MigrateController'
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
