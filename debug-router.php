<?php
/**
 * Debug Router - Check registered routes
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/router.php';

$router = new Router();

// Define routes (same as index.php)
// ... (abbreviated - just testing career routes)
$router->get('/api/search-careers', function() {
    echo json_encode(['test' => 'search-careers route matched']);
});
$router->get('/api/career-categories', function() {
    echo json_encode(['test' => 'career-categories route matched']);
});

echo "<h1>Router Debug</h1>";

// Simulate the request
$method = 'GET';
$uri = '/api/career-categories';

echo "<h2>Testing Route: $uri</h2>";

// Check if route exists
$reflection = new ReflectionClass($router);
$property = $reflection->getProperty('routes');
$property->setAccessible(true);
$routes = $property->getValue($router);

echo "<h3>Registered Routes:</h3>";
echo "<pre>" . print_r($routes, true) . "</pre>";

echo "<h3>Looking for: $uri</h3>";
if (isset($routes[$method][$uri])) {
    echo "<p style='color: green;'>✓ Route found!</p>";
    echo "<p>Handler: " . print_r($routes[$method][$uri], true) . "</p>";
} else {
    echo "<p style='color: red;'>✗ Route NOT found!</p>";
    echo "<p>Available GET routes:</p>";
    echo "<ul>";
    foreach ($routes[$method] ?? [] as $route => $handler) {
        echo "<li>$route</li>";
    }
    echo "</ul>";
}

// Test dispatch
echo "<hr>";
echo "<h2>Simulating Dispatch</h2>";
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = $uri;

ob_start();
try {
    $router->dispatch();
    $output = ob_get_clean();
    echo "<p>Output:</p>";
    echo "<pre>$output</pre>";
} catch (Exception $e) {
    $output = ob_get_clean();
    echo "<p style='color: red;'>Exception: " . $e->getMessage() . "</p>";
    echo "<pre>$output</pre>";
}
?>
