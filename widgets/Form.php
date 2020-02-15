<?php


namespace app\widgets;

/*
 * $model - объект данных\модель короче из названия понятно)
 * FormStart() - рисует начало формы и её атрибуты
 * field() - создает объект Field() (поле формы)
 * submitButton() - хотел сунуть в Field(), но потом передумал, просто рисует кнопку submit
 * FormEnd() - закрывает форму
 */

use app\core\HTMLTag;

class Form extends HTMLTag
{
    public $model;

    /**
     * Form constructor.
     */
    public function __construct()
    {
        parent::__construct("form");
    }

    /**
     * @param $model
     * @param array $attributes
     * @param string $action
     * @return string
     */
    public function FormStart($model, $attributes = [], $action = "")
    {
        $this->model = $model;
        $attributes['action'] = "http://".$_SERVER['HTTP_HOST']."/".$action;
        $attributes = $this->attr_into_str($attributes);
        return "<form $attributes>";
    }

    /**
     * @param $model_attr
     * @param array $tag_attr
     * @return Field
     */
    public function field($model_attr, $tag_attr = [])
    {
        return new Field($this->model, $model_attr, $tag_attr);
    }

    /**
     * @param $value
     * @param array $attributes
     * @return string
     */
    public function submitButton($value, $attributes = [])
    {
        $attributes = $this->attr_into_str($attributes);
        return "<input type='submit' value='$value' $attributes>";
    }

    /**
     * @return string
     */
    public function FormEnd()
    {
        return "</form>";
    }
}