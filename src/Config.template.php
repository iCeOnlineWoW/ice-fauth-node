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
            // any valid PHP/dibi driver (mysql, mysqli, postgres, ...)
            'driver' => '${DB_DBMS}',
            'port' => ${DB_PORT},
            'host' => '${DB_HOST}',
            'username' => '${DB_USERNAME}',
            'password' => '${DB_PASSWORD}',
            'database' => '${DB_DBNAME}',
        ],

        // auth guard configuration
        'guard' => [
            // fauth-node specific dbms - sql, redis
            'driver' => '${GUARDDB_DBMS}',
            'host' => '${GUARDDB_HOST}',
            'port' => ${GUARDDB_PORT},
            'username' => '${GUARDDB_USERNAME}',
            'password' => '${GUARDDB_PASSWORD}',
            'database' => '${GUARDDB_DBNAME}',

            // TTL values are in seconds

            'username_ttl' => ${GUARDDB_USERNAME_TTL},
            'ip_ttl' => ${GUARDDB_IP_TTL},
            'serviceprovider_ip_ttl' => ${GUARDDB_IP_SERVICEPROVIDER_TTL},

            // attempts to login before banning for a little bit

            'username_attempts' => ${GUARDDB_USERNAME_ATTEMPTS},
            'ip_attempts' => ${GUARDDB_IP_ATTEMPTS},
            'serviceprovider_ip_attempts' => ${GUARDDB_IP_SERVICEPROVIDER_ATTEMPTS}
        ]
    ],
];
