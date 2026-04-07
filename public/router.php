<?php
/**
 * Router for PHP Built-in Server
 * Start server with: php -S localhost:8000 public/router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Debug: log the URI being requested
// error_log("Router received URI: " . $uri);

// Static files - serve directly from public/
$staticPaths = ['/css/', '/js/', '/images/', '/fonts/', '/uploads/', '/icon-192.png', '/icon-512.png', '/manifest.json', '/service-worker.js'];
foreach ($staticPaths as $path) {
    if (strpos($uri, $path) === 0) {
        $file = __DIR__ . $uri;
        
        // Debug: log the file path
        // error_log("Looking for file: " . $file . " exists: " . (file_exists($file) ? 'yes' : 'no'));
        
        if (file_exists($file)) {
            // Set correct MIME type for service worker and manifest
            if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                header('Content-Type: application/json');
            } elseif (pathinfo($file, PATHINFO_EXTENSION) === 'js' && basename($file) === 'service-worker.js') {
                header('Content-Type: application/javascript');
            } elseif (pathinfo($file, PATHINFO_EXTENSION) === 'png') {
                header('Content-Type: image/png');
            }
            return false; // PHP serves it directly
        } else {
            // File doesn't exist - return 404
            http_response_code(404);
            echo "404 - File not found: " . $file;
            return true;
        }
    }
}

// Everything else goes through index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/index.php';
return true;
