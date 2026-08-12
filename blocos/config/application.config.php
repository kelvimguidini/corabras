<?php
return [
    'modules' => [
        'Laminas\Router',
        'Laminas\Validator',
        'Laminas\I18n',
        'Laminas\Log',
        'Laminas\Cache',
        'DoctrineModule',
        'DoctrineORMModule',
        'Application',
        'Laminas\Mvc\Plugin\FlashMessenger',
    ],
    'module_listener_options' => [
        'config_glob_paths'    => [
            'config/autoload/{,*.}{global,local}.php',
        ],
        'module_paths' => [
            './module',
            './vendor',
        ],
    ],
];
