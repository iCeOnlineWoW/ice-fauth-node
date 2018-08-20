<?php

require __DIR__ . '/../vendor/autoload.php';

session_start();

$config = require __DIR__.'/../src/Config.php';
$app = new \Slim\App($config);

require __DIR__ . '/../src/Dependencies.php';
require __DIR__ . '/../src/Loader.php';
require __DIR__ . '/../src/Routes.php';

$app->run();
