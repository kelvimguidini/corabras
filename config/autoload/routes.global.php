<?php

/**
 * Centralized routes configuration.
 *
 * Merged routes from module/Application and root config. Keep only this file
 * as the single source of truth for routes. Other module.config.php files
 * should not define 'router' anymore.
 */

use Application\Controller\IndexController;

return [
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

            'editar' => [
                'type' => 'Laminas\\Router\\Http\\Segment',
                'options' => [
                    'route'    => '/home[/:id][/]',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],

            'login' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route'    => '/login/',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action'     => 'login',
                    ],
                ],
            ],

            'tramitar' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route'    => '/tramitar/',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action'     => 'tramitar',
                    ],
                ],
            ],

            'pedidos' => [
                'type' => 'Laminas\\Router\\Http\\Segment',
                'options' => [
                    'route' => '/pedidos[/:offset][/:situacao][/]',
                    '"' => '',
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

            'carregamento' => [
                'type' => 'Laminas\\Router\\Http\\Segment',
                'options' => [
                    'route' => '/carregamento[/]',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'carregamento',
                    ],
                ],
            ],

            'cadastrarcarga' => [
                'type' => 'Laminas\\Router\\Http\\Segment',
                'options' => [
                    'route' => '/cadastrarcarga[/:id][/]',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'cadastrarcarga',
                    ],
                ],
            ],

            'abrir' => [
                'type' => 'Laminas\\Router\\Http\\Segment',
                'options' => [
                    'route' => '/abrir[/:id][/]',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'abrir',
                    ],
                ],
            ],

            'carregarcargas' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route' => '/carregarcargas/',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'carregarcargas',
                    ],
                ],
            ],

            'carregardadoscarga' => [
                'type' => 'Laminas\\Router\\Http\\Segment',
                'options' => [
                    'route' => '/carregardadoscarga[/:id][/]',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'carregardadoscarga',
                    ],
                ],
            ],

            'cadastrarcidade' => [
                'type' => 'Laminas\\Router\\Http\\Segment',
                'options' => [
                    'route' => '/cadastrarcliente[/:id][/]',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'cadastrarcidade',
                    ],
                ],
            ],

            'excluircidade' => [
                'type' => 'Laminas\\Router\\Http\\Segment',
                'options' => [
                    'route' => '/excluircidade[/:id][/]',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'excluircidade',
                    ],
                ],
            ],

            'imprimir' => [
                'type' => 'Laminas\\Router\\Http\\Segment',
                'options' => [
                    'route' => '/imprimir[/:id][/]',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'imprimir',
                    ],
                ],
            ],

            'recibo' => [
                'type' => 'Laminas\\Router\\Http\\Segment',
                'options' => [
                    'route' => '/recibo[/:id][/]',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'recibo',
                    ],
                ],
            ],

            'sair' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route'    => '/sair/',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action'     => 'sair',
                    ],
                ],
            ],

            'desmembrar' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route'    => '/desmembrar/',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action'     => 'desmembrar',
                    ],
                ],
            ],
        ],
    ],
];
