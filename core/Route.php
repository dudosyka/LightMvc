<?php

namespace app\core;

use app\configs\AppConfig;
use ReflectionException;
use ReflectionMethod;

class Route
{
    protected static $routes;

    protected static $url;

    public static function getParams()
    {
        $params = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        $params = explode("&", $params);
        foreach ($params as $param)
        {
            $p = explode("=", $param)[0];
            $v = isset(explode("=", $param)[1]) ? explode("=", $param)[1] : "";
            $_GET[$p] = $v;
        }
    }

    private static function getRouteParams($route)
    {
        $route = trim($route);
        $route = explode(" ",$route);
        $path = $route[0];
        $methods = [];

        if (count($route) > 2)
        {
            throw new \Exception("Invalid route");
        }

        if (count($route) == 2)
        {
            $path = $route[1];
            $methods = explode(",", $route[0]);
        }

        $path = explode("/", $path);
        $length = count($path);
        $urls = [];
        $params = [];

        for($i = 1; $i <= $length; $i++)
        {
            $urls[$i] = $path[$i - 1];
            $item = $path[$i - 1];
            if (preg_match("/[<]{1}.*?[>]{1}/u", $item, $matches))
            {
                foreach ($matches as $match)
                {
                    $route = trim($match);
                    $route = trim($route, "<");
                    $route = trim($route, ">");
                    $route = explode(":",$route);
                    if (count($route) != 2)
                    {
                        throw new \Exception("Invalid route");
                    }
                    $params[$i] = [
                        'name' => $route[0],
                        'regex' => $route[1]
                    ];
                }
            }
        }

        return [
            'length' => $length,
            'params' => $params,
            'url' => $urls,
            'methods' => $methods
        ];
    }

    private static function getRoutes()
    {
        self::$routes = [];
        foreach (AppConfig::$routes as $route => $controller)
        {
            $route = self::getRouteParams($route);
            $route["controller"] = $controller;
            $controller = explode("/", trim($route['controller'], "/"));
            if (count($controller) != 2)
            {
                throw new \Exception("Invalid router");
            }
            $controller_name = explode("/", trim($route['controller'], "/"))[0];
            $action_name = explode("/", trim($route['controller'], "/"))[1];
            $isset = self::isset_controller($controller_name);
            if (is_null($isset))
            {
                throw new \Exception("Invalid router controller '$controller_name' doesn`t exists");
            }
            if (!self::isset_action($controller_name, $action_name))
            {
                throw new \Exception("Invalid router action '$action_name' doesn`t exists in controller '$controller_name'");
            }
            self::$routes[] = $route;
        }
    }

    private static function isset_controller($name)
    {
        $controller_name = strtolower($name)."Controller"; // получаем имя класса контроллера

        $controller_file = "../controllers/".$controller_name.".php"; // получаем имя файла с классом контроллера

        if (file_exists($controller_file))
        {
            include_once $controller_file;
            return $controller_name;
        }

        return false;
    }

    private static function isset_action($controller, $action)
    {
        $controller_name = "app\\controllers\\".$controller."Controller";

        $action_name = "action_".strtolower($action); // получаем имя action в контроллере

        return (method_exists((new $controller_name()), $action_name));
    }

    private static function check_routes()
    {
        $url = self::$url;
        $length = count($url);
        $method = $_SERVER['REQUEST_METHOD'];

        $founded = null;
        $res_params = [];

        foreach (self::$routes as $route)
        {
            if (
                $length != $route['length']
            )
            {
                continue;
            }
            if (!empty($route['methods']))
            {
                if (!in_array($method, $route['methods']))
                {
                    continue;
                }
            }
            $all_similar = true;
            for ($i = 1; $i <= $length; $i++)
            {
                $params = $route['params'];
                $route_url = $route['url'];
                $item = $url[$i - 1];

                if (isset($params[$i]))
                {
                    if (preg_match("/".$params[$i]['regex']."/u", $item))
                    {
                        $res_params[] = ['name' => $params[$i]['name'], 'val' => $item];
                    }
                    else
                    {
                        $all_similar = false;
                        break;
                    }
                }
                else
                {
                    if (!preg_match("/".$route_url[$i]."/u", $item))
                    {
                        $all_similar = false;
                        break;
                    }
                }

            }
            if ($all_similar)
            {
                $founded = [
                    'controller' => $route['controller'],
                    'params' => $res_params
                ];
            }
        }
        return $founded;
    }

    public static function start()
    {
        $controller = AppConfig::$route['default_controller'];
        $action = AppConfig::$route['default_action'];

        $url = parse_url($_SERVER['REQUEST_URI']);

        $routes = trim($url['path'], "/");

        $routes = explode("/", $routes);

        self::$url = $routes;

        self::getRoutes();

        $route = self::check_routes();
        if (!is_null($route))
        {
            $controller = trim($route['controller'], "/");
            $controller = explode("/", $route['controller'])[0].'Controller';
            $action = "action_".explode("/", $route['controller'])[1];
            include_once "../controllers/".$controller.".php";
            $controller_name = "app\\controllers\\".$controller;
            foreach ($route['params'] as $param)
            {
                $_GET[$param['name']] = $param['val'];
            }
            try {
                $controller = new ReflectionMethod($controller_name, $action);
                $controller->invokeArgs(new $controller_name(), $_GET);
            } catch (ReflectionException $e) {
                route::page404(2);
                return;
            }
            return;
        }

        $isset = self::isset_controller($controller);
        if ($isset)
        {
            include_once $isset['file'];
        }
        else
        {
            route::page404(); //если такого файла нет отдаем 404
            return;
        }

        $controller_name = "app\\controllers\\".$isset['name'];

        $action_name = "action_".strtolower($action); // получаем имя action в контроллере

        try {
            $controller = new ReflectionMethod($controller_name, $action_name);
            $controller->invokeArgs(new $controller_name(), $_GET);
        } catch (ReflectionException $e) {
            route::page404(2);
            return;
        }
    }

    public static function page404($stage = 1)
    {
        header("HTTP/1.0 404");
    }
}