<?php

namespace app\core;

/*
 * И так почему HTMLTag() переехал сюда и почему Widget() умер
 *
 * Как мне казалось класс widget() был довольно бесполезен,
 * поэтому избавиться от него было не грустно,
 * его единственная функция переехала в HTMLTag()
 *
 * Теперь с какого перепугу HTMLTag() переехал в core?
 *
 * Ну все началось с того что loader() как то странно грузил файлы
 * видимо не в том порядке или что то вроде того
 * в итоге файл HTMLTag() почему то не загружался и из-за этого возникал ряд ошибок.
 * Ну я подумал подумал, ничего не предумал и в итоге пришел к тому
 * что можно вынуть этот класс и положить в core
 * тогда он будет загружаться самый первый
 * и никаких ошибок не будет => можно будет использовать его всеми виджетами
 * которые отрисовывают html теги.
 *
 * Ну и в следствие этого же умер класс Widget()
 * так как стал окончательно бесполезен
 *
 * Едем дальше, че по обновам, ну тут ничего масштабного:
 * 1) Добавилось несколько функций позволяющих более гибко управлять контентом
 * и атрибутами тегов.
 * 2) Тег отрисовывается отдельной функцией (render())
 * За счет этого можно будет не создавать дополнительные объекты для копирования тегов в коде
 * а отрисовывать уже созданый объект много раз
 *
 * 17.07.19
 * Обнова:
 * Теперь во все set и push функции в качестве $value и $content
 * можно передавать объект HTMLTag
*/

class HTMLTag
{
    protected $content = "";

    protected $attributes = [];

    protected $tag = "";

    protected $closed = true;

    /**
     * public function create.
     * @param string $tag
     * @param array $attr
     * @param string $content
     * @param bool $closed
     */
    public function __construct($tag, $attr = array(), $content = "", $closed = true)
    {
        $this->set_attributes($attr);
        $this->set_content($content);
        $this->tag = $tag;
        $this->closed = $closed;
    }

    /**
     * @return string
     */
    public function render()
    {
        $attr = $this->attr_into_str($this->attributes);
        return ($this->closed) ? "<$this->tag $attr>$this->content</$this->tag>" : "<$this->tag $attr>";
    }

    /**
     * @param $attributes
     * @return string
     */
    public function attr_into_str($attributes)
    {
        $output = "";
        foreach ($attributes as $attr => $content) {
            $output .= " $attr='$content' ";
        }
        return $output;
    }

    /**
     * @return string
     */
    public function get_content()
    {
        return $this->content;
    }

    /**
     * @param $content
     * @return string
     */
    public function set_content($content)
    {
        if ($content instanceof HTMLTag)
        {
            $this->content = $content->content;
            return $this->content;
        }
        else
        {
            $this->content = $content;
            return $this->content;
        }
    }

    /**
     * @param $content
     * @return string
     */
    public function push_back_content($content)
    {
        if ($content instanceof HTMLTag)
        {
            $this->content .= $content->render();
            return $this->content;
        }
        else
        {
            $this->content .= $content;
            return $this->content;
        }
    }

    /**
     * @param $content
     * @return string
     */
    public function push_front_content($content)
    {

        if ($content instanceof HTMLTag)
        {
            $this->content = $content->render() . $this->content;
            return $this->content;
        }
        else
        {
            $this->content = $content . $this->content;
            return $this->content;
        }
    }

    /**
     * @return array
     */
    public function get_attributes()
    {
        return $this->attributes;
    }

    /**
     * @param array|string $value
     * @param null $attribute
     * @return array
     *
     * Тут по подробнее, эта функция
     * 1) Может добавлять новые атрибуты
     * 2) Добавлять в старые новое значение
     * (Типо было style='top: 10px;' стало style='bottom: 20px;')
     * Что бы использовать первый вариант
     * нужно просто отдать массив с новыми атрибутами и не заполнять $attribute
     * Для второго варианта
     * нужно отдать на место $value строку с правками для атрибута
     * А в $attribute отдать имя редактируемого атрибута
     *
     * Всё аналогично для функции push_front_attributes() и push_back_attributes() их отличия лишь в том
     * что все "правки" push_front_attributes() добавит в начало а push_back_attributes() в конец
     */
    public function set_attributes($value, $attribute = NULL)
    {
        if ($value instanceof HTMLTag)
        {
            $this->attributes = $value->attributes;
            return $this->attributes;
        }
        else
        {
            if (is_null($attribute))
            {
                $this->attributes = $value;
            } else
            {
                if (isset($this->attributes[$attribute]))
                {
                    $this->attributes[$attribute] = $value;
                }
            }
            return $this->attributes;
        }
    }

    /**
     * @param array|string $value
     * @param null $attribute
     * @return array
     *
     * см. коммент к set_attributes()
     */
    public function push_back_attributes($value, $attribute = NULL)
    {
        if ($value instanceof HTMLTag)
        {
            $this->attributes = array_merge($this->attributes, $value->attributes);
            return $this->attributes;
        }
        else
        {
            if (is_null($attribute))
            {
                $this->attributes = array_merge($this->attributes, $value);
            }
            else
            {
                if (isset($this->attributes[$attribute]))
                {
                    $this->attributes[$attribute] .= $value;
                }
            }
        }
        return $this->attributes;
    }

    /**
     * @param array|string $value
     * @param null $attribute
     * @return array
     *
     * см. коммент к set_attributes()
     */
    public function push_front_attributes($value, $attribute = NULL)
    {
        if ($value instanceof HTMLTag)
        {
            $this->attributes = array_merge($value->attributes, $this->attributes);
            return $this->attributes;
        }
        if (is_null($attribute))
        {
            $this->attributes = array_merge($value, $this->attributes);
        } else
        {
            if (isset($this->attributes[$attribute]))
            {
                $this->attributes[$attribute] = $value . $this->attributes[$attribute];
            }
        }
        return $this->attributes;
    }
}