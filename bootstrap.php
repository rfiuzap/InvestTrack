<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

spl_autoload_register(function (string $class): void {
    $paths = [
        BASE_PATH . '/models/' . $class . '.php',
        BASE_PATH . '/services/' . $class . '.php',
        BASE_PATH . '/controllers/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }
});

$db = Database::getConnection();
