<?php

namespace app\core;

use app\configs\AppConfig;
use ReflectionException;
use ReflectionMethod;

/*
 * Ограничение запроса по длинне предлагаю в конфигах прописать,
 * и там если что не так будем отдавать 414
 */

class Route
{
    /*
     * Получает GET параметры из url
     * */
    public static function routes()
    {
        return [
            'name' => 'api/login'
        ];
    }

    public static function check_routes($route)
    {
        if (array_key_exists($route, self::routes()))
        {
            return self::routes()[$route];
        }
        else
        {
            return null;
        }
    }

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

    public static function start()
    {
        $controller = AppConfig::$route['default_controller'];
        $action = AppConfig::$route['default_action'];

        $url = parse_url($_SERVER['REQUEST_URI']);

        $routes = trim($url['path'], "/");

        $routes = (is_null(self::check_routes($routes))) ? $routes : self::check_routes($routes);

        $routes = explode("/", $routes);

        // Гениальное решении по роутингу)
        // Типо если первое api то просто
        // Соединяем category/method и получаем:
        // category/method => category_method, ну и соответсвенно
        // зовётся api->action_category_method
        // Так что новый формат для экшенов апи контроллера я предлагаю вот такой:
        // api/имя модели/её метод
        //TODO Ну как тебе? :)
        if (isset($routes[2]) && $routes[0] == "api" && !empty($routes[2]))
        {
            $routes[1] .= "_".$routes[2];
        }

        if (!empty($routes[0]))
        {
            $controller = ($routes[0] == "..") ? "" : $routes[0];
        }

        if (!empty($routes[1]))
        {
            $action = ($routes[1] == "..") ? "" : $routes[1];
        }


        $controller_name = strtolower($controller)."Controller"; // получаем имя класса контроллера

        $controller_file = "../controllers/".$controller_name.".php"; // получаем имя файла с классом контроллера

        if (file_exists($controller_file))
        {
            include $controller_file;
        }
        else
        {
            route::page404(); //если такого файла нет отдаем 404
            return;
        }

        //self::getParams(); //получаем GET параметры

        //if (isset($_GET[''])) unset($_GET['']);

        $controller_name = "app\\controllers\\".$controller_name;

        $action_name = "action_".strtolower($action); // получаем имя action в контроллере

        try {
            $controller = new ReflectionMethod($controller_name, $action_name);
            $controller->invokeArgs(new $controller_name(), $_GET);
        } catch (ReflectionException $e) {
            route::page404();
            return;
        }
        //$controller->$action_name(); // если такой экшен есть в контроллере то вызываем его, если нет опять 404
    }

    public static function page404($stage = 1)
    {
        var_dump($stage);die;
        header("HTTP/1.0 404");
    }
}