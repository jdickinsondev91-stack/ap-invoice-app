<?php

require __DIR__ . '/../vendor/autoload.php';

use FrameworkX\Container;
use React\Mysql\MysqlClient;

$db = new MysqlClient(
    getenv('DB_USER') . ':' .
    getenv('DB_PASSWORD') . '@' .
    getenv('DB_HOST') . ':' .
    getenv('DB_PORT') . '/' .
    getenv('DB_NAME')
);

$container = new Container([
    MysqlClient::class => fn() => $db,
]);

$app = new FrameworkX\App($container);


// MOVE TO CONTROLLER
$app->get('/health', function () {
    return React\Http\Message\Response::json(['status' => 'ok']);
});

$app->get('/health/db', function () use ($db) {
    $result = \React\Async\await($db->query('SELECT COUNT(*) as count FROM vendors'));

    return React\Http\Message\Response::json([
        'status' => 'ok',
        'vendor_count' => $result->resultRows[0]['count'],
    ]);
});

$app->run();