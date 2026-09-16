<?php
/**
 * Router Class for School Learning Platform
 */

class Router {
    private $routes = [];

    public function get($path, $handler) {
        $this->routes['GET'][$path] = $handler;
    }

    public function post($path, $handler) {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH);

        // Remove trailing slash except for root
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }

        // Check for exact match
        if (isset($this->routes[$method][$uri])) {
            $this->callHandler($this->routes[$method][$uri]);
            return;
        }

        // Check for parameterized routes
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = $this->convertToRegex($route);
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                $this->callHandler($handler, $matches);
                return;
            }
        }

        // 404
        http_response_code(404);
        echo "404 - Page Not Found";
    }

    private function callHandler($handler, $params = []) {
        if (is_string($handler)) {
            // Controller method format: "ControllerClass@method"
            list($controller, $method) = explode('@', $handler);

            require_once __DIR__ . '/controllers/' . $controller . '.php';

            $controllerInstance = new $controller();
            call_user_func_array([$controllerInstance, $method], $params);
        } elseif (is_callable($handler)) {
            call_user_func_array($handler, $params);
        }
    }

    private function convertToRegex($route) {
        // Convert route parameters to regex
        $route = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $route);
        return '#^' . $route . '$#';
    }
}

// Support running the app directly with: php -S localhost:8000 router.php
// This lets the root router act as a front controller and load the real app routes.
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

    $staticPaths = ['/css/', '/js/', '/images/', '/fonts/', '/public/uploads/', '/icon-192.png', '/icon-512.png', '/manifest.json', '/service-worker.js'];
    foreach ($staticPaths as $path) {
        if (strpos($uri, $path) === 0) {
            $file = __DIR__ . '/public' . $uri;

            if (file_exists($file)) {
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $mimeTypes = [
                    'css' => 'text/css',
                    'js' => 'application/javascript',
                    'png' => 'image/png',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'gif' => 'image/gif',
                    'svg' => 'image/svg+xml',
                    'ico' => 'image/x-icon',
                    'json' => 'application/json',
                    'woff' => 'font/woff',
                    'woff2' => 'font/woff2',
                    'ttf' => 'font/ttf',
                    'otf' => 'font/otf'
                ];

                if (isset($mimeTypes[$ext])) {
                    header('Content-Type: ' . $mimeTypes[$ext]);
                }
                return false;
            }

            http_response_code(404);
            echo '404 - File not found: ' . $file;
            return true;
        }
    }

    $_SERVER['SCRIPT_NAME'] = '/public/index.php';
    require __DIR__ . '/public/index.php';
    return true;
}
