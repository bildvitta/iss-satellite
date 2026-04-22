<?php

// config for Nave/IssSatellite
return [
    'mega' => [
        'db' => [
            'driver'         => 'oracle',
            'tns'            => env('MS_SATELLITE_MEGA_DB_TNS', ''),
            'host'           => env('MS_SATELLITE_MEGA_DB_HOST', ''),
            'port'           => env('MS_SATELLITE_MEGA_DB_PORT', '1521'),
            'database'       => env('MS_SATELLITE_MEGA_DB_DATABASE', ''),
            'service_name'   => env('MS_SATELLITE_MEGA_DB_DATABASE', ''),
            'username'       => env('MS_SATELLITE_MEGA_DB_USERNAME', ''),
            'password'       => env('MS_SATELLITE_MEGA_DB_PASSWORD', ''),
            'charset'        => env('MS_SATELLITE_MEGA_DB_CHARSET', 'AL32UTF8'),
            'prefix'         => env('MS_SATELLITE_MEGA_DB_PREFIX', ''),
            'prefix_schema'  => env('MS_SATELLITE_MEGA_DB_SCHEMA_PREFIX', ''),
            'edition'        => env('MS_SATELLITE_MEGA_DB_EDITION', 'ora$base'),
            'server_version' => env('MS_SATELLITE_MEGA_DB_SERVER_VERSION', '11g'),
            'load_balance'   => env('MS_SATELLITE_MEGA_DB_LOAD_BALANCE', 'yes'),
            'dynamic'        => [],
            'max_name_len'   => env('MS_SATELLITE_MEGA_ORA_MAX_NAME_LEN', 30),
        ],
    ],

    'wscarteira' => [
        'wsdl'     => env('MS_SATELLITE_WSCARTEIRA_WSDL', ''),
        'login'    => env('MS_SATELLITE_WSCARTEIRA_LOGIN', ''),
        'password' => env('MS_SATELLITE_WSCARTEIRA_PASSWORD', ''),
    ],

    'finnet' => [
        'url'        => env('MS_SATELLITE_FINNET_URL', ''),
        'url_qrcode' => env('MS_SATELLITE_FINNET_URL_QRCODE', ''),
    ],

    'multidados' => [
        'wsdl' => env('MS_SATELLITE_MULTDADDOS_WSDL', ''),
        'username' => env('MS_SATELLITE_MULTDADDOS_USERNAME', ''),
        'password' => env('MS_SATELLITE_MULTDADDOS_PASSWORD', ''),
    ],

    'ssh' => [
        'host' => env('SSH_HOST'),
        'port' => env('SSH_PORT', 22),
        'username' => env('SSH_USERNAME'),
        'password' => env('SSH_PASSWORD'),

        'default_connection' => env('SSH_DEFAULT_CONNECTION', 'mega'),

        'mega' => [
            'tunnel' => env('MEGA_TUNNEL'),
            'tunnel_local_port' => env('MEGA_TUNNEL_LOCAL_PORT', 1521),
            'tunnel_destination_port' => env('MEGA_TUNNEL_DESTINATION_PORT', 1521),
        ],

        'mega_cloud_bild' => [
            'tunnel' => env('BILD_MEGA_CLOUD_MEGA_TUNNEL'),
            'tunnel_local_port' => env('BILD_MEGA_TUNNEL_LOCAL_PORT', 36700),
            'tunnel_destination_port' => env('BILD_MEGA_TUNNEL_DESTINATION_PORT', 36700),
        ],
    ],

    'mega_cloud' => [
        'default_connection' => env('MEGA_CLOUD_DEFAULT_CONNECTION', 'bild'),
        'connect_timeout' => env('MEGA_CLOUD_CONNECTION_TIMEOUT', 120),
        'timeout' => env('MEGA_CLOUD_TIMEOUT', 120),

        'bild' => [
            'url' => env('BILD_MEGA_CLOUD_URL', 'http://127.0.0.1:36700'),
            'prefix' => env('BILD_MEGA_CLOUD_URL_PREFIX', '/api'),
            'username' => env('BILD_MEGA_CLOUD_USERNAME', ''),
            'password' => env('BILD_MEGA_CLOUD_PASSWORD', ''),
            'cache_key' => env('BILD_MEGA_CLOUD_CACHE_KEY', 'bildIssMegaCloudToken'),
        ],
    ],
];
