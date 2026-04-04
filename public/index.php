<?php

use React\Mysql\MysqlClient;

require __DIR__ . '/../vendor/autoload.php';

$db = new MysqlClient(
    getenv('DB_USER') . ':' .
    getenv('DB_PASSWORD') . '@' .
    getenv('DB_HOST') . ':' .
    getenv('DB_PORT') . '/' .
    getenv('DB_NAME')
);

$container = require __DIR__ . '/../bootstrap/container.php';
$routes = require __DIR__ . '/../bootstrap/routes.php';

$app = new FrameworkX\App($container($db));
$routes($app);

$app->run();
