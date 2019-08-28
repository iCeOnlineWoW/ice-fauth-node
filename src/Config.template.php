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
            'driver' => '${DB_DBMS}',
            'port' => ${DB_PORT},
            'host' => '${DB_HOST}',
            'username' => '${DB_USERNAME}',
            'password' => '${DB_PASSWORD}',
            'database' => '${DB_DBNAME}',
        ]
    ],
];
