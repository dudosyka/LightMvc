<?php

namespace app\assets;

use app\configs\AppConfig;
use app\core\loader;

/*
 * Короче суть данной фичи, если тебе нужно добавить новые стили или скрипты на страницу
 * тебе достаточно добавить их в соответсвующий массив придумав ему название
 * а потом отрисовать его в коде с помощью виджетов Css() и Script()
 *
 * Теперь о том как добавлять новые ассеты, тут всё просто:
 * Начнём с css.
 * Что бы добавить новый css тебе нужно совершить 4 шага)))
 * 1) сунь его куда нибудь в пределах папки web/css/
 * 2) придумай ему имя (можно любое под каким именем файл роли не играет)
 * 3) пораскинь мозгами и реши он должен автоматически грузиться на всех страницах
 * или он тебе нужен в конкретном месте
 * 4) добавь его в массив css
 * Теперь о том как добавлять в массив
 * "ключ" => ["загружать автоматически или нет?", "путь"]
 * Где ключ - имя которое ты придумал, а путь - путь к файлу относительно web/css/
 * Ну для добавления скриптов все анологично отличие лишь в том что путь надо писать относительно web/js
 *
 * Теперь про картинки тут вс ещё проще, всего 3 шага))))
 * 1) опять же сунь его только теперь в web/img/
 * 2) придумай имя
 * 3) добавь в массив
 *
 * С добавлением эллементарно
 * "ключ" => "путь"
 * Где ключ - придуманое имя, а путь - путь относительно web/img/
 *
 * За отрисовку всей этой красоты в HTML занимаются классы $css Script() Image()
 */

class AppAssets
{
    protected static $css = [

    ];

    protected static $js = [

    ];

    protected static $img = [

    ];

    public static $libs = [];

    protected static function load_ccs_lib($lib_name)
    {
        $result = [];
        foreach (loader::load_css_libs($lib_name) as $file)
        {
            $result[$file] = [true, $file];
        }
        self::$libs = array_merge(self::$libs, $result);
    }

    /**
     * @return array
     */
    public static function get_auto_load_css()
    {
        $output = [];
        $output = array_merge(loader::load_css_libs('bootstrap'), $output);
        self::load_ccs_lib('bootstrap');
        foreach (self::$css as $css => $value) {
            if ($value[0])
            {
                $output[] = $css;
            }
        }
        return $output;
    }

    /**
     * @return array
     */
    public static function get_auto_load_js()
    {
        $output = [];
        foreach (self::$js as $js => $value) {
            if ($value[0])
            {
                $output[] = $js;
            }
        }
        return $output;
    }

    /**
     * @param $name
     * @return string
     */
    public static function get_js($name)
    {
        return (isset($css[$name])) ? AppConfig::assets()."/js/".self::$js[$name][1] : AppConfig::assets()."/js/".self::$libs[$name][1];
    }

    /**
     * @param $name
     * @return string
     */
    public static function get_css($name)
    {
        return (isset($css[$name])) ? AppConfig::assets()."/css/".self::$css[$name][1] : AppConfig::assets()."/css/".self::$libs[$name][1];
    }

    /**
     * @param $name
     * @return string
     */
    public static function get_img($name){
        return AppConfig::assets()."/img/".self::$img[$name];
    }
}