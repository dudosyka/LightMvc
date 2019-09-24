<?php

namespace app\core;

use app\configs\AppConfig;
use app\widgets\HTML5Layout;

/*
 * 17.07.19
 * Обнова:
 * 1) Теперь нельзя передать в render() другой шаблон
 * Что бы изменить шаблон надо менять конфиг, по другому никак.
 *
 * 2) Теперь в render() передается $head.
 * Что такое head? Это объект HTMLTag, всё из него попадёт в тег head() html страницы
 * И будет отрисовано с помощью HTML5Layout()
 *
 */

class View
{
    public function render($view_name, $data = [], HTMLTag $head = NULL)
    {
        $view_dirname = AppConfig::$default_view_dir;
        $view_filename = AppConfig::$default_view;
        $view_name = explode("/", trim($view_name, "/"));
        if (isset($view_name[1]))
        {
            $view_dirname = $view_name[0];
            $view_filename = $view_name[1];
        }
        else if (isset($view_name[0]))
        {
            $view_filename = $view_name[0];
        }
        $view_name = "$view_dirname/view_$view_filename.php";
        $template_path = '../views/templates/'.AppConfig::$layout;
        if (!file_exists('../views/'.$view_name))
        {
            $data['view_error'] = "Вида с именем $view_name не существует";
            $template_path = "../views/templates/template_error.php";
            $view_name = "view_error.php";
        }
        extract($data);
        $head = (is_null($head)) ? new HTMLTag("head") : $head;
        $layout = new HTML5Layout($head);
        include $template_path;
    }
}