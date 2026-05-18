<?php

declare(strict_types=1);

class Router
{
    private $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $uri, $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    private function addRoute(string $method, string $uri, $action): void
    {
        $uri = $this->normalizeUri($uri);

        $this->routes[$method][] = [
            'uri' => $uri,
            'action' => $action,
            'pattern' => $this->makePattern($uri),
        ];
    }

    public function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $this->getCurrentUri();

        if (!isset($this->routes[$method])) {
            $this->abort(405, 'Method tidak didukung.');
        }

        foreach ($this->routes[$method] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches);

                $params = array_map('urldecode', $matches);
                $this->dispatch($route['action'], $params);
                return;
            }
        }

        $this->abort(404, 'Route tidak ditemukan.');
    }

    private function dispatch($action, array $params = []): void
    {
        if (is_callable($action)) {
            call_user_func_array($action, $params);
            return;
        }

        if (!is_string($action) || strpos($action, '@') === false) {
            $this->abort(500, 'Format action route tidak valid.');
        }

        [$controllerName, $methodName] = explode('@', $action, 2);

        // Load controller
        $controllerFile = APP_PATH . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            $this->abort(404, 'Controller tidak ditemukan: ' . $controllerName);
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            $this->abort(500, 'Class controller tidak ditemukan: ' . $controllerName);
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $methodName)) {
            $this->abort(404, 'Method tidak ditemukan: ' . $controllerName . '@' . $methodName);
        }

        call_user_func_array([$controller, $methodName], $params);
    }

    private function makePattern(string $uri): string
    {
        $pattern = preg_quote($uri, '#');

        // Support route model sederhana: /admin/barang/edit/{id}
        $pattern = preg_replace(
            '#\\\\\{[a-zA-Z_][a-zA-Z0-9_]*\\\\\}#',
            '([^/]+)',
            $pattern
        );

        return '#^' . $pattern . '$#';
    }

    private function getCurrentUri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri = $uri ?: '/';

        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $projectDir = preg_replace('#/public$#', '', $scriptDir);

        $bases = array_filter([
            rtrim($scriptDir, '/'),
            rtrim($projectDir, '/'),
        ]);

        usort($bases, static function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });

        foreach ($bases as $base) {
            if ($base !== '' && $base !== '/' && substr($uri, 0, strlen($base)) === $base) {
                $uri = substr($uri, strlen($base));
                break;
            }
        }

        return $this->normalizeUri($uri);
    }

    private function normalizeUri(string $uri): string
    {
        $uri = '/' . trim($uri, '/');

        return $uri === '/' ? '/' : rtrim($uri, '/');
    }

    private function abort(int $statusCode, string $message): void
    {
        http_response_code($statusCode);

        if (class_exists('Response')) {
            Response::abort($statusCode, $message);
            return;
        }

        echo '<h1>' . htmlspecialchars((string) $statusCode, ENT_QUOTES, 'UTF-8') . '</h1>';
        echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
        exit;
    }
}