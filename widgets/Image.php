<?php


namespace app\widgets;


use app\assets\AppAssets;
use app\core\HTMLTag;

/*
 * Рисует тег img
 */

class Image extends HTMLTag
{
    /**
     * Image constructor.
     * @param $name (Имя img ассета, то, что, как ключ идет, а не имя файла)
     * @param $attr
     * @param $alt
     */
    public function __construct($name, $alt, $attr = [])
    {
        $src = AppAssets::get_img($name);
        $attr['src'] = $src;
        $attr['alt'] = $alt;
        parent::__construct("img", $attr, "", false);
    }
}