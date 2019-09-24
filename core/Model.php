<?php

namespace app\core;

use app\configs\AppConfig;
use PDO;

/*
 * rules() - возвращает массив с правилами для валидации полей
 * validate_rules() - проверяет верно ли указаны имена полей для проверки в rules()
 * validate() - очень веселая функция должна проверять поля используя rules()
 * и как будто бы даже это делает))
 * column_exists() - проверяет есть ли такая колонка ($needle) в таблице ($table_name)
 * get_columns() - получает колонки таблицы ($table_name) по идее она юзается всего один раз в в конструкторе
 * load() - клевая функция должна довольно сильно облегчить добавления новой инфы туда передается массив который мержиться с текущим columns модели
 * save() - сохраняет/апдейтит данные в бд
 * find() - по сути ничего сама не ищет а просто выполняет запрос ($str) и если надо используетв нем данные из $params
 * fetchAll() - приводит данные пришедшие из бд в нормальное состоянии она по сути нужна только в find()
 */

/*
 * Про статус коды:
 * - 200 = успех
 * - с 1000 и т.д. = кастомные ошибки
 */


class Model extends Database
{
    public $validate_errors = [];

    public function __construct($table = NULL)
    {
        $this->connect = new PDO('mysql:host=' . AppConfig::$db_config["host"] . ';dbname=' . AppConfig::$db_config["database"] . ';charset=utf8', AppConfig::$db_config['user'], AppConfig::$db_config['password']);
        if ($table != NULL) $this->columns = $this->get_columns($table);
    }

    public function rules()
    {
        /*
         * Они оверврайтяться в каждой отдельной модели и формируются по такому шаблону
         * [[массив_имен_колонок], [тип], размер, шаблон_для_регулярки
         * Причем не обязательно заполнять все тоесть оно может выглядеть и так
         * [[массив_имен_колонок], [тип], размер]
         * или так
         * [[массив_имен_колонок], [тип]]
         * главное соблюдать порядок
         * Вообще валидация сделана не очень удобной. Её можно будет переписать если надо.
         *
         * (16.07.19) Обнова:
         * Теперь тип - это массив если нужно просто указать str, int и т.д. то
         * Просто надо обернуть в квадратные скобки: [int], [str]
         * В чём суть обновы теперь можно через запятую указать что бы значение не было null, или наоборот что оно может быть null
         * В таком случае надо указать такой массив в rules ['int', 'is_null' => true] или ['int', 'is_null' => false]
         *
         * Если тебе пофиг на тип но жизненно необходимо что бы колонка была скажем null
         * то теперь можно просто указать вот так ['null'] ну или если наоборот ['not_null']
         *
         * (17.07.19) Обнова:
         * Два новых типа phone, datetime добавлены в валидацию
         *
         * (22.07.19)
         * Новый типы
         */
        return [];
    }

    public function validate($data = NULL)
    {
        //тут все страшно, так что держись)
        $this->validate_errors = [];
        $model = (is_null($data)) ? $this->columns : $data; // получаем данные
        $rules = $this->rules();
        foreach ($rules as $rule)
        {
            $columns = $rule[0]; //берем колонки из правила, это первый эллемент  (см шаблон rules())
            foreach ($columns as $column)
            {
                if (!array_key_exists($column, $model)) continue;
                if (isset($rule[1])) // если для него есть правило с типом идем дальше
                {
                    $trueType = false; // флажок с "самым крутым именем")))
                    $is_null = false;
                    if (array_key_exists('is_null', $rule[1]))
                    {
                        if ($rule[1]['is_null'])
                        {
                            $is_null = true;
                        }
                    }
                    switch ($rule[1][0]) // Знаю что довольно стремно так типы проверять, но что поделаешь)
                    {
                        case "int":
                            if (is_numeric($model[$column]))
                            {
                                $trueType = true;
                            }
                            break;
                        case "str":
                            if (is_string($model[$column]))
                            {
                                $trueType = true;
                            }
                            break;
                        case 'null':
                            if (is_null($model[$column]))
                            {
                                $trueType = true;
                            }
                            break;
                        case 'not_null':
                            if (!is_null($model[$column]))
                            {
                                $trueType = true;
                            }
                            break;
                        case 'datetime':
                            if (preg_match("/^[0-9]{4}[-][0-9]{2}[-][0-9]{2} [0-9]{2}[:][0-9]{2}[:][0-9]{2}$/", $model[$column]))
                            {
                                $trueType = true;
                            }
                            break;
                        case 'date':
                            if (preg_match("/^[0-9]{4}[-][0-9]{2}[-][0-9]{2}$/", $model[$column]))
                            {
                                $trueType = true;
                            }
                            break;
                        case 'phone':
                            if (preg_match("/^[+]?[1-9][0-9]{9,14}$/", $model[$column]))
                            {
                                $trueType = true;
                            }
                            break;
                        case 'email':
                            if (preg_match("/^[a-zA-Z0-9-_.]{1,}[@][a-zA-Z0-9-_.]{1,}[.]{1}[a-zA-Z]{1,}$/", $model[$column]))
                            {
                                $trueType = true;
                            }
                            break;
                        default:
                            break;
                    }
                    //как ты уже понял наверно, для добавления нового типа поля придется пихать его в этот switch
                    if (!$trueType)
                    {
                        if ($is_null && is_null($model[$column]))
                        {
                            $trueType = true;
                            continue;
                        }
                        else
                        {
                            $this->validate_errors[] = array($column => "failed format");
                        }
                    }
                    if ($trueType)
                    {
                        if (isset($rule[2])) //проверяем указано ли правило для размера
                        {
                            // тут кароч супер метод (нет) типо берем приводим к строке измеряем её длинну
                            $length = mb_strlen(settype($model[$column], "string"));
                            if (is_string($model[$column]))
                            {
                                $length = mb_strlen($model[$column]);
                            }
                            if ($length <= $rule[2])
                            {
                                if (isset($rule[3])) // если в правиле указан шаблон то идем его проверять
                                {
                                    if (!preg_match($rule[3], $model[$column]))
                                    {
                                        $this->validate_errors[] = array($column => "failed format");
                                        //Ну тут для "супер удобного дебага" возвращаем имя колонки которая не прошла проверку и тип ошибки
                                    }
                                }
                            }
                            else
                            {
                                $this->validate_errors[] = array($column => "failed size");
                            }
                        }
                    }
                }
            }
        }
        if (empty($this->validate_errors))
        {
            return true;
        }
        return false;
    }

    public function get_model_data()
    {
        return $this->columns;
    }

    public function isset_duplicates($items)
    {
        $result = [];
        foreach ($items as $column => $value)
        {
            if (is_array($value))
            {
                foreach ($value as $item)
                {
                    if (!empty($this->find()->where([$column => ['=', $item]])->all()))
                    {
                        $result[] = $column;
                    }
                }
            }
            else
            {
                if (!empty($this->find()->where([$column => ['=', $value]])->all())) {$result[] = $column;}
            }
        }
        return $result;
    }

    /**
     * @param $name
     * @return bool
     * -----------------------------------------------
     * Думаю так проще обращаться, чем $this->columns['....'];
     * Так что теперь что бы скажем получить id модели $model
     * Надо только написать $model->get("id");
     * Только, это, оно вернёт false, если такой колонки нет.
     */
    public function get($name)
    {
        return (isset($this->columns[$name])) ? $this->columns[$name] : false;
    }

    /**
     * @return array
     *
     * Незнаю зачем он, но пусть будет :)
     * TODO понять зачем это, хех :)
     */
    public function get_as_array()
    {
        return $this->columns;
    }
}