<?php


namespace app\widgets;


use app\assets\AppAssets;
use app\core\HTMLTag;

/*
 * Рисует за тебя <script> вот и всё)
 */

class Script extends HTMLTag
{
    /**
     * Script constructor.
     * @param $name (Имя js ассета, то, что, как ключ идет, а не имя файла)
     */
    public function __construct($name)
    {
        $attr = ['src' => AppAssets::get_js($name)];
        parent::__construct("script", $attr, "");
    }
}