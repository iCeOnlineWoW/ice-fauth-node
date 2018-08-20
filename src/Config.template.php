<?php

return [
    'settings' => [
        'displayErrorDetails' => true,
        'addContentLengthHeader' => false,

        // Monolog settings
        'logger' => [
            'name' => 'ice-fauth-node',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // Dibi settings
        'dibi' => [
            'driver' => '${DBDRIVER}',
            'port' => ${DBPORT},
            'host' => '${DBHOST}',
            'username' => '${DBUSER}',
            'password' => '${DBPASS}',
            'database' => '${DBNAME}',
        ]
    ],
];
