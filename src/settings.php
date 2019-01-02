<?php
return [
    'settings' => [
        'displayErrorDetails' => env('DEBUG_MODE', false), // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        "determineRouteBeforeAppMiddleware" => true,
        'routerCacheFile'=> __DIR__ . '/../logs/cache.php',

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],
        
        // DB settings
        'db' => require('db/db.php'),
        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
    ],
];
