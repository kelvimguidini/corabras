<?php

namespace Application;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type' => 'Laminas\Router\Http\Literal',
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ],
                ],
            ],
            'login' => [
                'type' => 'Laminas\Router\Http\Literal',
                'options' => [
                    'route'    => '/login',
                    'defaults' => [
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'login',
                    ],
                ],
            ],
            'tramitar' => [
                'type' => 'Laminas\Router\Http\Literal',
                'options' => [
                    'route'    => '/tramitar',
                    'defaults' => [
                        'controller' => 'Application\Controller\Index',
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
                        'controller' => 'Application\Controller\Index',
                        'action' => 'pedidos',
                    ],
                ],
            ],
        ],
    ],

    'service_manager' => [
        'abstract_factories' => [
            'Laminas\Cache\Service\StorageCacheAbstractServiceFactory',
            'Laminas\Log\LoggerAbstractServiceFactory',
        ],
        'factories' => [
            'translator' => 'Laminas\Mvc\Service\TranslatorServiceFactory',
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

    'controllers' => [
        'invokables' => [
            'Application\Controller\Index' => Controller\IndexController::class,
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
        'driver' => [
            'my_annotation_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [__DIR__ . "/src/Application/Model"],
            ],
            'orm_default' => [
                'drivers' => [
                    'Application\Model' => 'my_annotation_driver',
                ],
            ],
        ],
    ],
];
