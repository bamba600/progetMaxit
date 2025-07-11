<?php

namespace App\Core;

use App\Config\Middleware;

class Router
{
    public static array $routes = [];

    public static function resolve()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (array_key_exists($uri, self::$routes)) {
            $route = self::$routes[$uri];
            
            // Exécution des middlewares s'ils existent
            if (isset($route['middleware'])) {
                foreach ($route['middleware'] as $middleware) {
                    $middlewareClass = Middleware::get($middleware);
                    if ($middlewareClass && class_exists($middlewareClass)) {
                        $middlewareInstance = new $middlewareClass();
                        $middlewareInstance();
                    }
                }
            }
            
            $controllerName = $route['controller'];
            $actionName = $route['action'];
            
            if (class_exists($controllerName) && method_exists($controllerName, $actionName)) {
                $controller = new $controllerName();
                return $controller->$actionName();
            } else {
                self::notFound();
            }
        } else {
            self::notFound();
        }
    }

    private static function notFound()
    {
        http_response_code(404);
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>404 - Page non trouvée</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                h1 { color: #333; }
                p { color: #666; }
                a { color: #007bff; text-decoration: none; }
            </style>
        </head>
        <body>
            <h1>404 - Page non trouvée</h1>
            <p>La page que vous recherchez n'existe pas.</p>
            <a href='/connexion'>Retourner à la connexion</a>
        </body>
        </html>";
        exit;
    }
}