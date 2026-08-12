<?php

namespace Application;

use Application\Controller\IndexController;
use Application\Controller\Factory\IndexControllerFactory;

return [
    'controllers' => [
        'factories' => [
            IndexController::class => IndexControllerFactory::class,
        ],

    ],

    // Routes moved to `config/autoload/routes.global.php` (single source of truth)
    'translator' => [
        'locale' => 'pt_BR',
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],




    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],

    'console' => [
        'router' => [
            'routes' => [],
        ],
    ],

    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                'metadata_cache' => 'memory',
                'query_cache'    => 'memory',
                'result_cache'   => 'memory',
            ],
        ],
    ],
];
