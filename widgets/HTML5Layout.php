<?php


namespace app\widgets;


use app\assets\AppAssets;
use app\core\HTMLTag;

/*
 * Короче эта штука что бы в layout`ах отрисовывать css и js
 * (которые в автозагрузке лежат)
 *
 * Соответсвенно head() рисует <link`и а endPage() <script
 */

class HTML5Layout
{
    /**
     * @return string
     */

    public $head_extra_content;

    /**
     * HTML5Layout constructor.
     * @param HTMLTag $head
     */
    public function __construct(HTMLTag $head)
    {
        $this->head_extra_content = $head;
    }

    /**
     * @return string
     */
    public function head()
    {
        $head = new HTMLTag('head', [], "");
        $css = AppAssets::get_auto_load_css();
        foreach ($css as $item)
        {
            echo "<pre>";
            var_dump($item);
            $link = new Css($item);
            $head->push_back_content($link->render());
        }
        $head->push_back_content($this->head_extra_content->get_content());
        $head->push_back_attributes($this->head_extra_content->get_attributes());
        return $head->render();
    }

    /**
     * @return string
     */
    public function endPage()
    {
        $footer = "";
        $js = AppAssets::get_auto_load_js();
        foreach ($js as $item)
        {
            $script = new Script($item);
            $footer .= $script->render();
        }
        return $footer;
    }
}