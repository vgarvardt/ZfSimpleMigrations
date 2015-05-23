<?php

return array(
    'migrations' => array(
        'default' => array(
            'dir' => dirname(__FILE__) . '/../../../../migrations',
            'namespace' => 'ZfSimpleMigrations\Migrations',
            'show_log' => true,
            'adapter' => 'Zend\Db\Adapter\Adapter'
        ),
    ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'migration-version' => array(
                    'type' => 'simple',
                    'options' => array(
                        'route' => 'migration version [<name>] [--env=]',
                        'defaults' => array(
                            'controller' => 'ZfSimpleMigrations\Controller\Migrate',
                            'action' => 'version',
                            'name' => 'default'
                        )
                    )
                ),
                'migration-list' => array(
                    'type' => 'simple',
                    'options' => array(
                        'route' => 'migration list [<name>] [--env=] [--all]',
                        'defaults' => array(
                            'controller' => 'ZfSimpleMigrations\Controller\Migrate',
                            'action' => 'list',
                            'name' => 'default'
                        )
                    )
                ),
                'migration-apply' => array(
                    'type' => 'simple',
                    'options' => array(
                        'route' => 'migration apply [<name>] [<version>] [--env=] [--force] [--down] [--fake]',
                        'defaults' => array(
                            'controller' => 'ZfSimpleMigrations\Controller\Migrate',
                            'action' => 'apply',
                            'name' => 'default'
                        )
                    )
                ),
                'migration-generate' => array(
                    'type' => 'simple',
                    'options' => array(
                        'route' => 'migration generate [<name>] [--env=]',
                        'defaults' => array(
                            'controller' => 'ZfSimpleMigrations\Controller\Migrate',
                            'action' => 'generateSkeleton',
                            'name' => 'default'
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
