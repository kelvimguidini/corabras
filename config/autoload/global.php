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


    'console' => [
        'router' => [
            'routes' => [],
        ],
    ],

    'caches' => [
        'memory' => [
            'adapter' => [
                'name' => 'DoctrineCacheAdapter', // funciona em qualquer versão
            ],
        ],
    ],

    'cache' => [
        'adapter' => 'memory',
    ],

    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                'metadata_cache' => 'array',
                'query_cache'    => 'array',
                'result_cache'   => 'array',
            ],
        ],

        'connection' => [
            'orm_default' => [
                'driverClass' => \Doctrine\DBAL\Driver\PDO\MySQL\Driver::class,
                'params' => [
                    'host' => '69.6.212.253',
                    'port' => '3306',
                    'user' => 'fddd5815_corabras',
                    'password' => 'Corabras*2020',
                    'dbname' => 'fddd5815_corabras'
                ]
            ]
        ],

        // ⭐ ESTA PARTE FALTAVA! ⭐
        'entitymanager' => [
            'orm_default' => [
                'connection'    => 'orm_default',
                'configuration' => 'orm_default',
            ],
        ],

        'driver' => [
            'my_annotation_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [
                    __DIR__ . "/src/Application/Model"
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    'Application\Model' => 'my_annotation_driver'
                ]
            ]
        ]
    ]
];
