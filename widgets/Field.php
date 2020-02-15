<?php


namespace app\widgets;

/*
 * $model - Модель из которой берутся данные и в которую записываются
 * $model_attribute - конкретная колонка из модели ($model)
 * $tag_attr - атрибуты для тега обыные html атрибуты передаются массивом потом парсяться в строку
 * $value - инфа берется из $model и суется в value html тега
 *
 * Короче я решил не разбивать каждый тег поля форм на класс, что бы отрисовать какой либо нужно позвать функцию у класса Field()
 * А Field() - и есть единый класс поля формы
 *
 * Правда пока только 3 типа поля)))
 * Но добавить их не проблема))
 */

use app\core\HTMLTag;

class Field
{
    public $model;

    public $model_attribute;

    public $tag_attr;

    public $field_name;

    public $value;

    /**
     * Field constructor.
     * @param $model
     * @param $model_attribute
     * @param $tag_attr
     */
    public function create_classic_input($type, $required)
    {
        $input = new HTMLTag(
            "input",
            [
                'name' => $this->field_name,
                'type' => $type,
                'value' => $this->value
            ],
            "",
            false
        );
        $input->push_back_attributes($this->tag_attr);
        if ($required) {$input->push_back_attributes(['required' => "true"]);}
        return $input->render();
    }

    public function __construct($model, $model_attribute, $tag_attr = [])
    {
        $this->model = $model;
        $this->model_attribute = $model_attribute;
        $this->tag_attr = $tag_attr;
        if (isset($this->tag_attr['name']))
        {
            $this->field_name = $this->tag_attr['name'];
            unset($this->tag_attr['name']);
        }
        else
        {
            $this->field_name = $model_attribute;
        }
        if (isset($this->model->columns[$this->model_attribute]))
            $this->value = $this->model->columns[$this->model_attribute];
        else if (isset($this->model->extra_columns[$this->model_attribute]))
            $this->value = $this->model->extra_columns[$this->model_attribute];
        else
            $this->value = "";
        $this->value = (is_null($this->value)) ? "" : $this->value;
    }

    /**
     * @return string
     */
    public function textarea($required = true)
    {
        $textarea = new HTMLTag("textarea", [
                'name' => $this->field_name,
            ],
            $this->value
        );
        $textarea->push_back_attributes($this->tag_attr);
        if ($required) {$textarea->push_back_attributes(['required' => "true"]);}
        return $textarea->render();
    }

    /**
     * @param $list
     * @return string
     */
    public function dropDown($list, $required = true)
    {
        $selector = new HTMLTag(
            'select',
            [
                'name' => $this->field_name
            ]
        );

        $selector->push_back_attributes($this->tag_attr);

        foreach ($list as $title => $value)
        {
            $option = new HTMLTag('option', ['value' => $value], $title);

            if ($value == $this->value)
            {
                $option->push_back_attributes(['selected' => ""]);
            }

            $selector->push_back_content($option->render());
        }
        if ($required) {$selector->push_back_attributes(['required' => "true"]);}
        return $selector->render();
    }

    /**
     * @return string
     */
    public function text($required = true)
    {
        return $this->create_classic_input("text", $required);
    }

    public function password($required = true)
    {
        return $this->create_classic_input("password", $required);
    }

    public function date($required = true)
    {
        return $this->create_classic_input("date", $required);
    }
}