<?php

use Laminas\Mvc\Application;
use Laminas\Stdlib\ArrayUtils;

error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Facilita o uso de caminhos relativos.
 */
chdir(dirname(__DIR__));

// Ignora requisições de arquivos estáticos quando usando o servidor embutido do PHP
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (__FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}

// Autoload do Composer
require __DIR__ . '/../vendor/autoload.php';

if (! class_exists(Application::class)) {
    throw new RuntimeException(
        "Não foi possível carregar a aplicação.\n"
            . "- Rode `composer install` se estiver desenvolvendo localmente.\n"
            . "- Rode `docker compose run --rm app composer install` se estiver usando Docker.\n"
    );
}

// Carrega configuração principal
$appConfig = require __DIR__ . '/../config/application.config.php';

// Se houver, mescla configuração de desenvolvimento
if (file_exists(__DIR__ . '/../config/development.config.php')) {
    $appConfig = ArrayUtils::merge($appConfig, require __DIR__ . '/../config/development.config.php');
}

// Executa a aplicação Laminas
Application::init($appConfig)->run();
