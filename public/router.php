<?php
/**
 * Router for PHP Built-in Server
 * Start server with: php -S localhost:8000 public/router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Static files - serve directly from public/
$staticPaths = ['/css/', '/js/', '/images/', '/fonts/'];
foreach ($staticPaths as $path) {
    if (strpos($uri, $path) === 0) {
        $file = __DIR__ . $uri;
        if (file_exists($file)) {
            return false; // PHP serves it directly
        }
    }
}

// Everything else goes through index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/index.php';
return true;
