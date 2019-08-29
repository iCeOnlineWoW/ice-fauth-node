<?php

$container = $app->getContainer();

// init Monolog logger
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// init Dibi database connection
$container['db'] = function ($c) {
    $settings = $c->get('settings')['dibi'];
    $dibi = new Dibi\Connection($settings);
    return $dibi;
};

$container['guard_model'] = function($c) {
    $cfg = $c->get('settings')['guard'];
    if ($cfg)
    {
        if ($cfg['driver'] == 'redis')
            return new GuardRedisModel($cfg);
        else if ($cfg['driver'] == 'sql')
            return new GuardSQLModel($cfg);
    }

    return null;
};
