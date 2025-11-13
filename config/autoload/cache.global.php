<?php

use Laminas\Cache\Storage\Adapter\Filesystem;

return [
    'caches' => [
        'default' => [
            'adapter' => [
                'name' => Filesystem::class,
                'options' => [
                    'cache_dir' => 'data/cache',
                ],
            ],
        ],
    ],

    'service_manager' => [
        'aliases' => [
            // força substituição se alguém pedir Memory
            'Laminas\Cache\Storage\Adapter\Memory' => Filesystem::class,
        ],
    ],
];
