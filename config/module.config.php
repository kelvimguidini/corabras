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

    'router' => [
        'routes' => [
            'home' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'login' => [
                'type' => 'Laminas\Router\Http\Literal',
                'options' => [
                    'route'    => '/login',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action'     => 'login',
                    ],
                ],
            ],
            'tramitar' => [
                'type' => 'Laminas\Router\Http\Literal',
                'options' => [
                    'route'    => '/tramitar',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action'     => 'tramitar',
                    ],
                ],
            ],
            'pedidos' => [
                'type' => 'Laminas\Router\Http\Segment',
                'options' => [
                    'route' => '/pedidos[/:offset][/:situacao]',
                    'constraints' => [
                        'offset' => '[0-9]+',
                        'situacao' => '[a-zA-Z0-9_-]+',
                    ],
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'pedidos',
                    ],
                ],
            ],
        ],
    ],
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
