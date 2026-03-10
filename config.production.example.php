<?php
/**
 * Production Configuration Example
 * Copy this to config/config.production.php and adjust settings
 */

return [
    // Database
    'db' => [
        'driver' => 'sqlite',
        'database' => __DIR__ . '/../database.sqlite',
    ],
    
    // File Storage
    'storage' => [
        // Option 1: Local filesystem (for small/medium deployments)
        'driver' => 'local',
        'root' => __DIR__ . '/../public/uploads',
        'url' => '/uploads',
        
        // Option 2: AWS S3 (for production scale)
        // 'driver' => 's3',
        // 'key' => env('AWS_ACCESS_KEY_ID'),
        // 'secret' => env('AWS_SECRET_ACCESS_KEY'),
        // 'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        // 'bucket' => env('AWS_BUCKET'),
        // 'url' => env('AWS_URL'),
    ],
    
    // Session
    'session' => [
        'lifetime' => 120,
        'secure' => true,  // true in production (HTTPS only)
        'http_only' => true,
    ],
    
    // App
    'debug' => false,  // Always false in production
    'url' => 'https://yourdomain.com',
];
