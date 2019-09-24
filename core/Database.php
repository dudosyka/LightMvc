<?php

namespace app\core;

use app\configs\AppConfig;

/*
 * $table_name - таблица к которой "привязана" модель.
 * $columns - массив с колонками таблицы
 * $connect - хранит в себе подключение к бд
 * column_exists() - проверяет есть ли такая колонка ($needle) в таблице ($table_name)
 * get_columns() - получает колонки таблицы ($table_name) по идее она юзается всего один раз в в конструкторе
 * load() - клевая функция должна довольно сильно облегчить добавления новой инфы туда передается массив который мержиться с текущим columns модели
 * create() - сохраняет новую запись в бд используя значения хранящиеся в columns модели
 * fetchAll() - приводит данные пришедшие из бд в нормальное состояние
 *
 * И так теперь немного о новой системе Update Insert и Search в бд
 *
 * Начну с Search
 * Теперь что бы найти что то в бд можно вообще не писать запрос самому достаточно просто
 * воспользоваться несколькими функциями.
 *
 * Например:
 * Нам надо получить всех людей из таблицы юзеров (user) с именем (name) "Саша"
 * Мы берем модель таблицы user и пишем там нечто такое:
 *
 * $this->find()->where(['name' => ['=', 'Саша']])->all();
 *
 * Сдесь было использовано три новых функции find(), where() и all()
 * Что делает find() да по сути просто создает "базовый" запрос (SELECT * FROM `table_name`)
 * А вот уже where() приделывает к нему условия поиска
 * Теперь подробнее про шаблон параметра функции where(),
 * Туда всегда должен идти массив такого вида:
 *
 * "имя_колонки_к_которой_пишется_условие" => ['= / LIKE / > / < / <= / >=' (Используй любой из этих операторов), 'value']
 *
 * all() - этой функцией мы говорим "Нам нужны все результаты", она создает на каждую запись модель и формирует из них массив,
 * который возвращает как результат своей работы.
 *
 * И так теперь попробуем сузить круг поиска теперь нам нужны не все Саши,
 * а только с номером +12345678901
 *
 * Для этого добавим еще одно условие к нашему запросу
 * Это можно сделать по разному:
 *
 * 1 способ, его можно использовать если нам нужно строгое AND
 * В таком случае просто добавим в текущий where еще одно условие
 * $this->find()->where(['name' => ['=', 'Саша'], 'phone_number' => ['=', '+12345678901']])->all();
 *
 * 2 способ, тоже для строгого AND можно использовать ещё одну новую функцию andWhere() - она добавляет новое/ые условия в запрос используя AND
 * В таком случае запрос будет выглядеть вот так:
 * $this->find()->where(['name' => ['=', 'Саша']])->andWhere(['phone_number' => ['=', '+12345678901']])->all();
 *
 * 3 способ его нужно использовать в том случае если нам нужно не строгое AND, а OR
 * Тоесть если бы мы хотели получить не всех Саш с номером +12345678901, а либо Саш либо любых других людей с номером +1234567890
 * В таком случае запрос будет выглядеть примерно так:
 * $this->find()->where(['name' => ['=', 'Саша']])->orWhere(['phone_number' => ['=', '+12345678901']])->all()
 *
 * Если же мы знаем id (Наш_Id) эллемента таблицы, то мы можем не писать длинный запрос типа:
 *
 * $this->find()->where(['id' => ['=', "Наш_Id"])->one();
 *
 * А просто воспользоваться функцией findOne() передав в неё Наш_Id, вот так:
 *
 * $this->findOne(Наш_Id);
 *
 * Резульат выполнения в обоих случаях будет одинаковым и там и там вернётся модель с полученными данными
 *
 * Ну и последнее нововведение в Search это функция one()
 * Это противоположность all() она возвращает не массив со всеми записями из резултата запроса
 * а только один - первый.
 *
 * Теперь нюансы:
 *
 * 1) условия внутри функций where(), andWhere(), orWhere(), всегда скрепляются с помощью AND тоесть
 *
 * если мы имеем запрос вида:
 * $this->find()->where(['name' => ['=', 'Саша'], 'phone_number' => ['=', '+12345678901']])->all();
 * То он конвертируется в:
 * SELECT * FROM `user` WHERE (`name` = 'Саша' AND `phone_number` = '+12345678901')
 *
 * 2)
 * Если есть какой то запрос который
 * городить через эти функции пока невозможно
 * то не надо отчаиваться!)
 * Просто не используй этот запрос!
 * И жизнь твоя гарантировано заиграет новыми красками!
 * Ну а если серьезно то есть функция query
 * через неё можно выполнить любой запрос.
 *
 * И так вроде все про Search про сами функции можно в кратце посмотреть в комментариях
 * рядом с ними
 *
 * Теперь Update
 * Ну тут всё намного более скудно в плане обновлений
 * Первое (и последнее XD) теперь есть функция update() - самое лучшее название для функция которая занимается update`ом базы XD
 * Её надо просто позвать у модели и данные из её поля columns попадут прямиком в таблицу базы данных
 *
 * Теперь Insert
 * Тут всё также грустно как и с update()
 * Первое (и опять же последнее) есть такая клевая штука (или нет) как функция create()
 * (В этот раз с названием подкачал, но я писал это в 5 утра так что не суди строго XD)
 * Что же она делает ну в принципе почти тоже что и update() тоже хватает данные из columns модели и сует все это дело в базу, правда здорово?)
 *
 * В общем это и есть вся обнова.
 *
 * А, чуть не забыл! Функции update() и create() никак не проверяют что они собираются в базу запихать))
 * Так что очень советую воспользоваться функцией validate() ну или как то по другому данные проверять, заранее
 *
 *
 * 05.08.2019
 * Короче find() теперь легаси, так как весь запрос целиком собирается в make_query() надобности в find() нет
 * Я её не убрал что бы все что было написано не умерло
 *
 * Итак теперь немного примеров запросов :) (Пройдусь тут по CRUD)
 * 1) Создание новой записи в бд
 *  $model->load($data, $aliases)
 *  $model->insert();
 * Теперь подробнее про $data и $aliases
 * ------------------------------------------------------------------------
 * $data
 * ------------------------------------
 * Чем может быть $data, $data это либо массив либо другая модель
 * Если она массив то он должен быть следующего вида
 * ['ключ' => 'данные']
 * ключ - это по сути любой ключ
 * но предпочтительнее что бы это было реальное имя одной из колонок
 * потому что иначе придется прописывать алиасы для этого ключа соединяя его уже с реальным именем колонки
 * ------------------------------------
 * $aliases
 * ------------------------------------
 * Алиас это массив вида ['значение ключа по факту' => 'чем этот ключ по сути должен являться']
 * Звучит странно) Но в принципе с примером всё ясно, пример как раз таки ниже:
 *
 * Допустим у нас есть таблица в бд "table" мы создали для неё модель $table
 * В этой таблице есть следующие колонки id, col1, col2
 *
 * Мы получили $data следующего вида
 * $data = ['col1' => 'data1', 'key2' => 'data2']
 *
 * Мы знаем что в $data есть 'key2' и мы так же знаем что его значение это значение для col2
 * Для того что бы сказать об этом ф-ции load формируем массив $aliases следующего вида:
 *
 * $aliases = ['key2' => 'col2']
 *
 * Далее загружаем данные в модель:
 *
 * $table->load($data, $aliases);
 *
 * И созраняем их в бд:
 *
 * $table->insert();
 *
 * ( Ну или сразу так: $table->load($data, $aliases)->insert() )
 *
 * Теперь о том куда денуться данные если не задать для них алиасы тоест ьесли бы мы сделали так:
 *
 * $table->load($data);
 *
 * Данные которые мы загружаем никуда не пропадут они попадут в специальный массив
 * $table->extra_columns;
 *
 * Что бы потом получить их оттуда просто обратитесь к нему как и к любому другому ассоциативному массиву
 *
 * $table->extra_columns['key2'] // 'data2'
 *
 * 2) Апдейт записи в бд
 *
 * И так предположим что мы обратились к бд c помощью всё той же модели table: (см. пример выше)
 *
 * $col1_value = "value";
 * $result = $table->where(['col1' => ['=', $col1_value])->one();
 *
 * $result - это модель таблицы table
 *
 * Допустим я решил изменить у этой записи значение для 'col2' я могу сделать это по разному
 *
 * 1 и самый простой способ (если изменений надо делать не много например только у одной колонки как сейчас)
 * Напрямую обратиться к массиву columns модели и поменять значение:
 *
 * $result->columns['col2'] = $new_value;
 *
 * Этот способ прост и удобен только в том случае если надо изменить небольшое кол-во данных для 3 ну максимум для 4 столбцов
 * Но если надо изменить много данный то вполне можно использовать ф-цию load как пользоваться ею я довольно подробно объяснил выше
 *
 * $result->load($new_value);
 *
 * Теперь на остается просто позвать ф-цию update и она апдейтнет наши данные
 * Но обрати внимание на такой нюанс
 * Если ты зовешь у модели update то ты должен быть уверен в том что там записан id той строки которую ты меняешь
 * Иными словами $model->columns['id'] != NULL это важно иначе ты ничего не сохранишь и с 90% вероятностью
 * Вызовешь гнев богов
 * Но это не точно :)
 *
 * (На самом деле update просто вернёт false в таком случае)
 *
 * Короче просто помни про это $model->columns['id'] != NULL
 *
 * 3) Удаление записей из бд
 *
 * И так снова вспомним про нашу любимую $table допустим решили удалить оттуда строку с col1 == 2
 *
 * Всё просто:
 *
 * $table->where(['col1' => ['=', 2]])->delete();
 *
 * Если у нас уже откуда то есть id записи которую мы хотим удалить то можно сделать ещё таким способом
 *
 * $table->load(['id' => $id_value])->delete();
 *
 * Это проимходит потому что delete работает следующим способом он смотрит были ли заданы какие либо условия
 * Если да удаляет по ним (как в первом способе), а если никаких условий задано не было то он начинает искать
 * id и удаляет по нему (2 способ)
 *
 * Но если мы позовём delete у модели не задав никаких условий и не задав перед этим id то delete отдаст false
 * И не удалит ничего, ну и опять же есть где то 83% вероятность что ты вызовешь гнев богов
 *
 * 4) Поиск данных в бд
 *
 * Стоило бы поставить этот пункт на 1 место но что то в конце только про него подумал
 * Итак:
 *
 * для задания условий есть следующие ф-ции where() andWhere() orWhere()
 *
 * Сперва про них скажу
 *
 * Работаю они все одинаково и аргументы у них у всех одинаковые их различие лишь в строгости условия
 * Сперва про аргументы:
 * 1) это список условий, синтаксис у него такой:
 * ['имя колонки' => ['оператор сравнения(=, >, <, LIKE и т д)' => значение]]
 * 2) это метод связи ВНУТРИ условия либо OR либо AND
 *
 * Теперь о главной разнице между where, andWhere() и orWhere()
 * andWhere и where() будет формировать блок условия (`id` = `значение`) AND который будет соединяться с другими СНАРУЖИ c помощью AND
 * тоесть andWhere и where это блоки строгих условий
 * а orWhere будет использовать OR - (`id` = `значение`) OR
 *
 * Теперь о том в каком виде ты хочешь получить данные из бд
 *
 * Если ты хочешь получить массив моделей то юзай all()
 *
 * Если ты хочешь получить в ответ просто одну модель one() это функция иногда выполняет роль LIMIT 1
 * ибо она всегда возвращает 1 модель неважно скок строк пришло из бд
 *
 * Если ты хочешь получить данные из бд в виде массива а не в виде модели[ей] то юзай ф-цию as_array()
 *
 * Она может вернуть даннные в двух видах в зависимости от того что вернула база если база вернула 1 запись
 * То as_array вернёт массив следующего вида ['имя_колонки' => 'значение' .....]
 *
 * Если бд отдала > 1 записи то она вернёт массив вида [0 => ['имя_колонки' => 'значение' .....] 1 => [....]]
 *
 * Про джойны пока не буду писать раз они нам не нужны если про них нужна будет инфа скажи я всё опишу ниже
 */

use PDO;



class Database
{
    public $table_name;

    public $connect;

    public $columns;

    public $model_name;

    private $db_query;

    private $db_where_conditions = [];

    private $db_params;

    private $db_result;

    public $db_request_errors;

    private $extra_columns = [];

    private $join_conditions = [];

    private $sort = [];

    private $update_query = "";

    /**
     * @param $needle
     * @return bool
     */
    public function column_exists($needle)
    {
        if (!array_key_exists($needle, $this->columns)) //ну тут вроде ясно все смотрим если такая колонка если да true иначе false
        {
            return false;
        }
        return true;
    }

    /**
     * @param $table_name
     */
    public function get_columns($table_name)
    {
        //короче я решил с конфигом ничего не мудрить а руками тут базу сменить
        $connect = new PDO('mysql:host=localhost;dbname=information_schema;charset=utf8', AppConfig::$db_config['user'], AppConfig::$db_config['password']);
        $res = $this->query("SELECT `COLUMN_NAME` FROM `COLUMNS` WHERE `TABLE_SCHEMA` = ? AND `TABLE_NAME` = ?", array(AppConfig::$db_config['database'], $table_name), $connect)->fetchAll();
        $columns = [];
        //массив с колонками записываем в public $columns
        foreach ($res as $item)
        {
            $columns[$item['COLUMN_NAME']] = NULL;
        }
        return $columns;
    }

    /**
     * @param $data
     */
    public function load($data, $columns_aliases = [])
    {
        //тут все просто, прогоняем через цикл что бы убрать из входящих данных поля, которых нет в таблице
        if (!is_array($data))
        {
            $data = $data->columns;
        }
        $extra = [];
        foreach ($data as $item => $value)
        {
            if (!$this->column_exists($item))
            {
                if (array_key_exists($item, $columns_aliases))
                {
                    $extra[$columns_aliases[$item]] = $value;
                    continue;
                }
                $this->extra_columns[$item] = $value;
            }
        }
        //ну и мержим то что получилось, оно перезапишет старые данные
        $data = array_merge($data, $extra);
        foreach ($data as $item => $val)
        {
            if (!$this->column_exists($item))
            {
                unset($data[$item]);
            }
        }
        $this->columns = array_merge($this->columns, $data);

        return $this;
    }

    /**
     * @return array
     */
    public function fetchAll()
    {
        $result = array();
        if (!is_null($this->db_result))
        {
            while($item = $this->db_result->fetch(PDO::FETCH_ASSOC))
            {
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * Короче, она превращает ассоциативный массив $this->db_params в обычный,
     * иначе execute() ругается
     */
    private function cut_db_params()
    {
        $arr = $this->db_params;
        $this->db_params = [];
        if (is_array($arr))
        {
            foreach ($arr as $item)
            {
                $this->db_params[] = $item;
            }
        }
    }

    /**
     * очищает переменнные для where join и т.д.
     */
    private function clear_params()
    {
        $this->db_query = "";
        $this->db_where_conditions = [];
        $this->db_params = [];
        $this->join_conditions = [];
        $this->sort = [];
    }

    /**
     * @param string $query
     * @param array $params
     * @param null $connect
     * @return $this
     *
     * Выполняет запрос к бд, используя либо данные из модели либо переданные ей в параметры
     */
    public function query($query = "", $params = [], $connect = NULL)
    {

        if (!empty($query))
        {
            $this->db_query = $query;
        }
        if (!empty($params))
        {
            $this->db_params = $params;
        }
        $this->cut_db_params();
        $errors = [];
        if (is_null($connect))
        {
            $result = $this->connect->prepare($this->db_query);
            $result->execute($this->db_params);
            $errors = $result->errorInfo();
        }
        else
        {
            $result = $connect->prepare($this->db_query);
            $result->execute($this->db_params);
            $errors = $result->errorInfo();
        }

        $this->db_request_errors = $errors;
        $this->db_result = $result;
        $this->clear_params();

        return $this;
    }

    /**
     * @return $this
     *
     * Как и писалось выше создает "базовый" запрос к базе
     */
    public function find()
    {
        $this->db_query = "";
        $this->db_where_conditions = [];
        $this->db_params = [];
        $this->join_conditions = [];
        $this->sort = [];
        return $this;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findOne($id)
    {
        return $this->find()->where(['id' => ['=', $id]])->one();
    }

    /**
     * @return bool
     * Берет данные из модели и апдейтит базу.
     *
     * Правда тут одна не очень крутая фишка, эта функция использует`id` по умолчанию,
     * так что если его не окажется в таблице модели
     * то функция вернёт false.
     *
     * То же самое с функцией delete().
     */
    public function update()
    {
        $this->update_query = "SET ";
        $count = count($this->columns);

        foreach ($this->columns as $column => $value)
        {
            $count--;
            $this->update_query .= ($count > 0) ? "`$column` = ?, " : "`$column` = ?";
            $this->db_params[] = $value;
        }

        $this->make_query('update')->query();

        return ((is_null($this->db_request_errors[1]))) ? true : false;
    }

    /**
     * @return array
     *
     * Она берет массив $this->columns и убирает оттуда все колонки с NULL и возвращает полученное
     * Это нужно для функции create(). Что бы колонки с NULL не сохранялись в БД
     */
    private function cut_null_columns()
    {
        $result = [];
        foreach ($this->columns as $item => $value)
        {
            if ($value != NULL)
            {
                $result[$item] = $value;
            }
        }
        return $result;
    }

    /**
     * @return bool
     * 
     * Сохраняет данные в бд
     */
    public function insert()
    {
        $columns_name = "";
        $values = "";
        $columns = $this->cut_null_columns();
        $count = count($columns);
        foreach ($columns as $column => $val)
        {
            $count--;
            $columns_name .= ($count > 0) ? " `$column`, " : " `$column`";
            $values .= ($count > 0) ? "?, " : "?";
        }
        $this->query("INSERT INTO `$this->table_name` ($columns_name) VALUES ($values)", $columns);



        return (is_null($this->db_request_errors[1])) ? true : false;
    }

    /**
     * @return bool
     *
     * см. коммент к update()
     */
    public function delete()
    {
        if (empty($this->db_where_conditions))
        {
            $col_id = $this->columns['id'];

            if (is_null($col_id))
            {
                return false;
            }

            $this->db_where_conditions[] = $this->make_where_query(['id' => ['=', $col_id]], "AND");
        }

        $this->make_query("delete")->query();

        return (is_null($this->db_request_errors[1])) ? true : false;
    }

    /**
     * @return bool
     */
    public function deleteAll()
    {
        $this->make_query("delete")->query();
        return (is_null($this->db_request_errors[1])) ? true : false;
    }

    /**
     * @return $this
     * 
     * Она берет массив с условиями поиска и собирает в единую строку
     */
    public function make_query($type = "select")
    {
        $query_main_item = "";
        $query_join_item = "";
        $query_where_item = "";
        $query_sort_item = "";

        switch ($type)
        {
            case "select":
                $query_main_item = (count($this->join_conditions) > 0) ? "SELECT `$this->table_name`.*, " : "SELECT `$this->table_name`.* ";
                break;
            case "delete":
                $query_main_item = "DELETE ";
                break;
            case "update":
                $query_main_item = "UPDATE ";
                break;
        }

        foreach ($this->join_conditions as $join_table => $join_condition)
        {
            $columns = $this->get_columns($join_table);
            $count = count($columns);
            foreach ($columns as $column => $value)
            {
                $count--;
                $query_main_item .= ($count > 0) ? "`$join_table`.`$column` as `".$join_table."_".$column."`," : "`$join_table`.`$column` as `".$join_table."_".$column."`";
            }
            foreach ($join_condition as $item)
            {
                $query_join_item .= $item;
            }
        }
        if ($type != "update")
        {
            $query_main_item .= " FROM `$this->table_name` ";
        }
        else
        {
            $query_main_item .= "`$this->table_name` $this->update_query ";
        }
        foreach ($this->db_where_conditions as $attribute)
        {
            $query_where_item .= $attribute;
        }
        if (!empty($this->sort))
        {
            if (isset($this->sort['order']['col']) && isset($this->sort['order']['type']))
            {
                $col = $this->sort['order']['col'];
                $type = $this->sort['order']['type'];

                $query_sort_item .= "ORDER BY $col $type";
            }
            if (isset($this->sort['limit']))
            {
                $limit = $this->sort['limit'];
                $query_sort_item .= "LIMIT $limit";
            }
        }
        $this->db_query = $query_main_item.$query_join_item.$query_where_item.$query_sort_item;
        return $this;
    }

    /**
     * @return array
     *
     * Отдает все полученные данные из бд в виде массива моделей
     */
    public function all()
    {
        $pdo_result = $this->make_query()->query()->fetchAll();
        $result = [];
        foreach ($pdo_result as $item)
        {
            $class_name = "app\\models\\".$this->model_name;
            $model = new $class_name();
            $model->load($item);
            $result[] = $model;
        }
        $this->db_params = [];
        $this->db_query = "";
        $this->db_where_conditions = [];
        return $result;
    }

    /**
     * @return array
     */
    public function as_array()
    {
        $data = $this->make_query()->query()->fetchAll();
        if (isset($data[0]))
        {
            if (count($data) > 1)
            {
                return $data;
            }
            else
            {
                $this->load($data[0]); return $data[0];
            }
        }
    }

    /**
     * @return mixed
     *
     * Отдает всегда одну запись независимо от кол-ва полученных, причем всегда первую
     */
    public function one()
    {
        $pdo_result = $this->make_query()->query()->fetchAll();
        $class_name = "app\\models\\".$this->model_name;
        $model = new $class_name();
        if (!empty($pdo_result))
        {
            $model->load($pdo_result[0]);
        }
        else
        {
            return NULL;
        }
        $this->db_params = [];
        $this->db_query = "";
        $this->db_where_conditions = [];
        $this->load($model);
        return $model;
    }

    /**
     * @param string $delimetr
     * @param array $params
     * @return string
     *
     * Она парсит шаблон массива который юзается в фнк-циях where() andWhere() orWhere()
     */
    public function make_where_query($params, $delimetr)
    {
        $query = "(";
        $count = count($params);
        foreach ($params as $param => $condition)
        {
            $count--;
            if ($this->column_exists($param))
            {
                if (is_array($condition[1]))
                {
                    $count_ = count($condition[1]);
                    foreach ($condition[1] as $item)
                    {
                        $count_--;
                        $query .= ($count_ > 0) ? "`$param` ".$condition[0]." ? $delimetr " : "`$param` ".$condition[0]." ?";
                        $this->db_params[] = $item;
                    }
                }
                else
                {
                    $query .= ($count > 0) ? "`$param` ".$condition[0]." ? $delimetr " : "`$param` ".$condition[0]." ?";
                    $this->db_params[] = $condition[1];
                }
            }
        }
        return $query;
    }

    /**
     * @param string $delimetr
     * @param $params
     * @return $this
     *
     * Добавляет перечень строгих условий к запросу на выборку,
     * используется всего один раз после find(): ...find()->where()...
     */
    public function where($params, $delimetr = "AND")
    {
        if (empty($params)) return $this;
        $query = "WHERE (".$this->make_where_query($params, $delimetr).")";
        $this->db_where_conditions[] = $query;
        return $this;
    }

    /**
     * @param string $delimetr
     * @param array $params
     * @return $this
     *
     * Добавляет перечень строгих условий к запросу на выборку,
     * может использоваться неограниченное кол-во раз в любом порядке вместе с orWhere()
     */
    public function andWhere($params, $delimetr = "AND")
    {
        $start = " AND (";
        if (empty($this->db_where_conditions))
        {
            $start = " WHERE (";
        }
        $query = $start.$this->make_where_query($params, $delimetr).")";
        $this->db_where_conditions[] = $query;
        return $this;
    }

    /**
     * @param string $delimetr
     * @param $params
     * @return $this
     *
     * Добавляет перечень не строгих условий к запросу на выборку,
     * может использоваться неограниченное кол-во раз в любом порядке вместе с andWhere()
     */
    public function orWhere($params, $delimetr = "AND")
    {
        $start = " OR (";
        if (empty($this->db_where_conditions))
        {
            $start = " WHERE (";
        }
        $query = $start.$this->make_where_query($params, $delimetr).")";
        $this->db_where_conditions[] = $query;
        return $this;
    }

    /**
     * @param $type
     * @param $table
     * @param $on
     * @param $delimetr
     * @return string
     */
    protected function join($type, $table, $on, $delimetr)
    {
        $join = " $type JOIN `$table`";
        $count = count($on);
        if ($count > 0)
        {
            $join .= " ON ";
        }
        foreach ($on as $param => $condition)
        {
            $count--;
            if ($this->column_exists($param))
            {
                if (is_array($condition[1]))
                {
                    $count_ = count($condition[1]);
                    foreach ($condition[1] as $item)
                    {
                        $count_--;
                        $join .= ($count_ > 0) ? "`$this->table_name`.`$param` ".$condition[0]." `$table`.`$item` $delimetr " : "`$this->table_name`.`$param` ".$condition[0]." `$table`.`$item`";
                    }
                }
                else
                {
                    $join .= ($count > 0) ? "`$this->table_name`.`$param` ".$condition[0]." `$table`.`$condition[1]` $delimetr " : "`$this->table_name`.`$param` ".$condition[0]." `$table`.`$condition[1]`";
                    $this->db_params[] = $condition[1];
                }
            }
        }
        $join .= " ";
        return $join;
    }

    /**
     * @param $table
     * @param array $on
     * @param string $on_delimetr
     * @return $this
     */
    public function leftJoin($table, $on = [], $on_delimetr = "AND")
    {
        $this->join_conditions[$table][] = $this->join("LEFT", $table, $on, $on_delimetr);
        return $this;
    }

    /**
     * @param $table
     * @param array $on
     * @param string $on_delimetr
     * @return $this
     */
    public function innerJoin($table, $on = [], $on_delimetr = "AND")
    {
        $this->join_conditions[$table][] = $this->join("INNER", $table, $on, $on_delimetr);
        return $this;
    }

    /**
     * @param $table
     * @param array $on
     * @param string $on_delimetr
     * @return $this
     */
    public function rightJoin($table, $on = [], $on_delimetr = "AND")
    {
        $this->join_conditions[$table][] = $this->join("RIGHT", $table, $on, $on_delimetr);
        return $this;
    }

    /**
     * @param $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->sort['limit'] = $limit;
        return $this;
    }

    /**
     * @param $col
     * @param $type
     * @return $this
     */
    public function orderBy($col, $type)
    {
        $this->sort['order'] = ['col' => $col, 'type' => $type];
        return $this;
    }
}