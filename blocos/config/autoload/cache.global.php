<?php

use Laminas\Cache\Storage\Adapter\Memory;

/**
 * Register a simple service that DoctrineModule expects: 'doctrine.cache.memory'.
 *
 * DoctrineModule resolves cache names like 'memory' into service names
 * such as 'doctrine.cache.memory'. The service manager must be able to
 * create that service. Here we provide a minimal factory that returns
 * a Memory adapter instance.
 */

return [
    'service_manager' => [
        'factories' => [
            // Service for DoctrineModule: returns DoctrineCacheAdapter wrapping Memory
            'doctrine.cache.memory' => function ($container) {
                $mem = $container->get('Laminas\Cache\Storage\Adapter\Memory');
                return new \Application\Cache\DoctrineCacheAdapter($mem);
            },

            // Register the Laminas Memory adapter itself
            'Laminas\Cache\Storage\Adapter\Memory' => function ($container) {
                return new Memory();
            },

            // Also make the concrete class resolvable if requested
            Memory::class => function ($container) {
                return new Memory();
            },
        ],
    ],
];
