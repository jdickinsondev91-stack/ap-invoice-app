<?php

require __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/../bootstrap/container.php';

$app = new FrameworkX\App($container);

require __DIR__ . '/../bootstrap/routes.php';

$app->run();
