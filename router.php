<?php
/**
 * Simple Router for School Learning Platform
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
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
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
