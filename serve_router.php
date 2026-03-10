<?php
/**
 * Router Script for PHP Built-in Server
 * Usage: php -S localhost:8000 serve_router.php
 */

// Get the request URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Static files - serve directly from public/
$staticFiles = ['/css/', '/js/', '/images/', '/fonts/'];

foreach ($staticFiles as $path) {
    if (strpos($uri, $path) === 0) {
        $file = __DIR__ . '/public' . $uri;
        if (file_exists($file)) {
            return false; // PHP serves it directly
        }
    }
}

// Everything else goes through public/index.php
$_SERVER['SCRIPT_NAME'] = '/public/index.php';
require __DIR__ . '/public/index.php';
return true;
