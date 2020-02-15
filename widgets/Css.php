<?php


namespace app\widgets;


use app\assets\AppAssets;
use app\core\HTMLTag;

/*
 * И так этот класс призван дабы облегчить жизнь
 * во время добавления новых линков на страницу
 *
 * Он наследует HTMLTag() и по сути ничего сам не делает
 * Глянь конструктор и всё станет ясно))
 */

class Css extends HTMLTag
{
    /**
     * Css constructor.
     * @param $name (Имя css ассета, то, что, как ключ идет, а не имя файла)
     */
    public function __construct($name)
    {
        $attr = ['rel' => 'stylesheet', 'href' => AppAssets::get_css($name)];
        parent::__construct("link", $attr, "", false);
    }
}