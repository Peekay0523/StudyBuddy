<?php
/**
 * Router Script for PHP Built-in Server
 * Usage: php -S localhost:8000 serve_router.php
 */

// Get the request URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Static files - serve directly from public/
$staticFiles = ['/css/', '/js/', '/images/', '/fonts/', '/uploads/'];

foreach ($staticFiles as $path) {
    if (strpos($uri, $path) === 0) {
        $file = __DIR__ . '\\public' . $uri;
        // Convert forward slashes to backslashes for Windows
        $file = str_replace('/', '\\', $file);
        if (file_exists($file)) {
            return false; // PHP serves it directly
        } else {
            // Debug: log the path
            error_log("File not found: " . $file);
            http_response_code(404);
            echo "404 - File not found: " . $file;
            return true;
        }
    }
}

// Everything else goes through public/index.php
$_SERVER['SCRIPT_NAME'] = '/public/index.php';
require __DIR__ . '\\public\\index.php';
return true;
