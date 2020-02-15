<?php

namespace app\core\database;

class ActiveQuery
{
    public $attributes = [];

    public $extra_attributes = [];

    protected $model_name = "";

    private $selected_columns = [];

    private $where_conditions = [];

    private $join_conditions = [];

    private $columns_on_update;

    private $sort;

    private $limit;

    private $connection;

    protected $table = "";

    protected $query = "";

    protected $params = [];

    public $result = null;

    protected $use_temp = false;

    private $aliases = [];

    protected $usages = [];

    private $query_type = null;

    private $joined_columns = [];

    private $related_attributes = [];

    private $used_columns = [];

    protected $temp_connection = [
        'table' => null,
        'database' => null
    ];

    protected $user = null;

    protected $user_password = null;

    private $insert_cols = [];

    /*
     * $db_config = [
     *      'host' => *your mysql host*,
     *      'user' => *your mysql user*,
     *      'password' => *your mysql user password*,
     *      'database' => *your database*
     * ];
     */
    public function __construct($db_config, $table, $set_columns = true, $set_usages = true)
    {
        $this->connection = new QueryConnection($db_config);

        $this->table = $table;
        if ($set_usages)
        {
            $this->setUsages();
        }
        if ($set_columns)
        {
            $this->setAttributes($this->getColumns());
        }
    }

    public function getParamsValues()
    {
        $result = [];

        foreach ($this->params as $param => $value)
        {
            $result[$param] = ($value instanceof ActiveAttribute) ? $value->value() : $value;
        }

        return $result;
    }

    public function query($query = null, $params = null, $database = null)
    {
        $this->query = (is_null($query)) ? $this->buildQuery() : $query;
        $this->params = (is_null($params)) ? array_values($this->params) : array_values($params);

        $query = new Query($this->connection);
        $this->result = $query->exec($this->query, $this->getParamsValues());
        return $this;
    }

    public function getUsages()
    {
        return $this->usages;
    }

    private function setUsages()
    {
        $connection = new QueryConnection($this->connection);
        $connection->setDatabase("information_schema");

        $model = new ActiveQuery($connection,
        "KEY_COLUMN_USAGE",
        false,
        false
        );

        $usages = $model->find(
            [
                'COLUMN_NAME column',
                "REFERENCED_TABLE_SCHEMA db",
                "REFERENCED_TABLE_NAME table",
                "REFERENCED_COLUMN_NAME ref_column",
                "CONSTRAINT_NAME name"
            ])->where(
                [
                    'TABLE_SCHEMA' => ['=', $this->connection->getDatabase()],
                    'TABLE_NAME' => ['=', $this->table],
                    'CONSTRAINT_NAME' => ['!=', 'PRIMARY']
                ])->all();

        foreach ($usages as $usage)
        {
            $model = new ActiveQuery($this->connection, $usage->get('table')->value());
            $model->connection->setDatabase($usage->get('db')->value());
            $this->usages[$usage->get('table')->value()][] = [
                'db' => $usage->get('db')->value(),
                'table' => $usage->get('table')->value(),
                'usage_column' => $usage->get('column')->value(),
                'ref_column' => $usage->get('ref_column')->value(),
                'name' => $usage->get('name')->value(),
                'model' => $model
            ];
        }
    }

    private function getColumns($table = null)
    {
        $table = $table ?? $this->table;
        $new_connection = clone $this->connection;
        $new_connection->setDatabase("information_schema");
        $model = new ActiveQuery($new_connection,
        "COLUMNS",
        false,
        false
        );

        return ['table' => $table, 'columns' => $model->query("SELECT * FROM `COLUMNS` WHERE `TABLE_SCHEMA` = '{$this->connection->getDatabase()}' AND `TABLE_NAME` = '$table'")->fetch()];
    }

    private function setAttributes($data)
    {
        $columns = $data['columns'];
        foreach ($columns as $column)
        {
            $attribute = new ActiveAttribute($column['static_attributes']['COLUMN_NAME'], $data['table'], $column['static_attributes']['COLUMN_DEFAULT']);
            $this->attributes[$column['static_attributes']['COLUMN_NAME']] = $attribute;
        }
    }

    public function setTable($table_name)
    {
        $this->table = $table_name;
    }

    private function clear()
    {
        if ($this->use_temp)
        {
            $this->table = $this->temp_connection['table'];
            $this->connection->setDatabase( $this->temp_connection['database'] ?? $this->connection->getDatabase());
        }
        $this->use_temp = false;
        $this->temp_connection = [
            'table' => null,
            'database' => null
        ];
        $this->where_conditions = [];
        $this->join_conditions = [];
        $this->query = "";
        $this->params = [];
        $this->selected_columns = [];
        $this->columns_on_update = [];
        $this->aliases = [];
        $this->query_type = null;
    }

    public function from($table_name, $database = null)
    {
        if (is_null($table_name))
        {
            throw new \DatabaseException("table_name mustn`t be null");
        }
        $this->use_temp = true;
        $this->temp_connection = [
            'table' => $this->table,
            'database' => $this->connection->getDatabase()
        ];
        $this->attributes = [];
        $this->table = $table_name;
        $this->connection->setDatabase($database ?? $this->connection->getDatabase());
        $this->setAttributes($this->getColumns());
        return $this;
    }

    public function fetch()
    {
        $result = [];

        if (!is_null($this->result))
        {
            $i = 0;
            while ($row = $this->result->getResult()->fetch(\PDO::FETCH_ASSOC))
            {
                $result[$i] = [];
                foreach ($row as $item => $val)
                {
                    foreach ($this->joined_columns as $table => $columns) {
                        if (array_key_exists($item, $columns)) {
                            $attribute = new ActiveAttribute($columns[$item]->name(), $table, $val);
                            $result[$i]['related'][$table][$columns[$item]->name()] = $attribute;
                            unset($row[$item]);
                        }
                    }
                }
                $result[$i]['static_attributes'] = $row;
                if (!array_key_exists('related',$result[$i]))
                    $result[$i]['related'] = [];
                $i++;
            }
        }
        return $result;
    }

    public function get($name)
    {
        if (isset($this->attributes[$name]))
        {
            return $this->attributes[$name];
        }
        else
        {
            if (isset($this->extra_attributes[$name]))
                return $this->extra_attributes[$name];
            throw new \Exception("Undefined attribute '$name'");
        }
    }

    public function getRelatedData($table)
    {
        if (!isset($this->usages[$table]))
            throw new \DatabaseException("Unknown relation `$table`");
        $model = new ActiveQuery($this->connection, $table);
        $model->load($this->related_attributes[$table]);
        return $model;
    }

    public function set($name, $value)
    {
        if (array_key_exists($name, $this->attributes))
        {
            $this->attributes[$name]->setValue($value);
        }
        else
        {
            throw new \Exception("Undefined attribute '$name'");
        }
    }

    public function load($data)
    {
        foreach ($data as $name => $value)
        {
            if ($value instanceof  ActiveAttribute)
                $value = $value->value();
            if (isset($this->attributes[$name]))
            {
                $attribute = new ActiveAttribute($name, $this->attributes[$name]->table(), $value);
                $this->attributes[$name] = $attribute;
            }
            else
            {
                $attribute = new ActiveAttribute($name, null, $value);
                $this->extra_attributes[$name] = $attribute;
            }
        }
    }

    public function buildBaseQuery()
    {
        if (isset($_SESSION['test']))
        $query = "";
        if (is_null($this->query_type)) throw new \DatabaseException("Attempt to create an empty request");
        switch ($this->query_type)
        {
            case "SELECT":
                $query = "SELECT ";
                $i = 0;
                foreach ($this->selected_columns as $column)
                {
                    $query .= "`".$column->table()."`.`".$column->name()."`".((is_null($column->alias())) ? "" : " as `".$column->alias()."`");
                    if ($i + 1 < count($this->selected_columns))
                    {
                        $query .= ", ";
                    }
                    $i++;
                }
                $query .= " FROM `{$this->table}` ";
                break;
            case "DELETE":
                $query = "DELETE FROM `{$this->table}`";
                break;
            case "UPDATE":
                $query = "UPDATE `$this->table` SET ";
                $i = 0;
                foreach ($this->columns_on_update as $column)
                {
                    if ($column == "id")
                    {
                        continue;
                    }
                    $i++;
                    $query .= "`$column` = ?";
                    $query .= ($i + 1 < count($this->columns_on_update)) ? ", " : " ";
                }
                break;
            case "INSERT":
                $query = "INSERT INTO `$this->table` ";
                $i = 0;
                $cols = "(";
                $values = " VALUES (";
                foreach (array_keys($this->insert_cols) as $col)
                {
                    $i++;
                    $cols .= "`$col`";
                    $values .= "?";
                    if ($i + 1 <= count($this->insert_cols))
                    {
                        $cols .= ", ";
                        $values .= ", ";
                    }
                    else
                    {
                        $cols .= ")";
                        $values .= ")";
                    }
                }
                $query .= $cols . $values;
                break;
        }

        if ($query == "")
        {
            throw new \DatabaseException("Failed while creating a base query");
        }

        return $query;
    }

    private function buildWhereQuery()
    {
        $query = " WHERE ";
        for ($i = 0; $i < count($this->where_conditions); $i++)
        {
            $condition = $this->where_conditions[$i];
            foreach ($condition[0] as $col => $cond)
            {
                $operator = trim($cond[0], " ");
                $operator_ = explode(" ", $cond[0]);
                $not_operator = "";
                if (count($operator_) > 1)
                {
                    if ($operator_[0] == "NOT")
                    {
                        $operator = "";
                        for ($j = 1; $j < count($operator_); $j++)
                        {
                            $operator .= ($j + 1  == count($operator_)) ? $operator_[$j] : $operator_[$j] . " ";
                        }
                        $not_operator = "NOT ";
                    }
                }
                $query .= "$not_operator(";
                switch ($operator)
                {
                    case "BETWEEN":
                        if (!is_array($cond[1]))
                        {
                            throw new \DatabaseException("Invalid value for operator, value must be array");
                        }
                        $params = [];
                        foreach ($cond[1] as $item)
                        {
                            $params[] = "?";
                            $this->params[] = $item;
                        }
                        $query .= "`$col` BETWEEN ".implode(" AND ", $cond[1]);
                        break;
                    case "IN":
                        if (!is_array($cond[1]))
                        {
                            throw new \DatabaseException("Invalid value for operator, value must be array");
                        }
                        $params = [];
                        foreach ($cond[1] as $item)
                        {
                            $params[] = "?";
                            $this->params[] = $item;
                        }
                        $query .= "`$col` IN (".implode(",", $params).")";
                        break;
                    case "IS NULL":
                        $query .= "`$col` IS NULL";
                        break;
                    default:
                        $this->params[] = $cond[1];
                        $query .= "`$col` ".$operator." ?";
                        break;
                }
                $query .= ($i + 1 < count($this->where_conditions)) ? ") $condition[1] " : ") ";
            }
        }
        if ($query == " WHERE ")
        {
            $query = "";
        }
        return $query;
    }

    private function buildJoinQuery()
    {
        $query = "";
        foreach ($this->join_conditions as $table_usages)
        {
            foreach ($table_usages as $usage)
            {
                $query .= "$usage[type] $usage[table] ON `$this->table`.`$usage[usage_column]` = `$usage[table]`.`$usage[ref_column]` ";
            }
        }
        return $query;
    }

    private function buildSortQuery()
    {
        $query = "";
        if (!is_null($this->limit))
        {
            $query .= " LIMIT $this->limit";
        }
        return $query;
    }

    private function buildQuery()
    {
        if (empty($this->where_conditions) && $this->query_type != "SELECT")
            $this->where(['id' => ['=', $this->get('id')]]);
        $this->query = $this->buildBaseQuery() . $this->buildJoinQuery() . $this->buildWhereQuery() . $this->buildSortQuery();
        return $this->query;
    }

    private function startNewQuery($type)
    {
        if (!is_null($this->query_type)) throw new \DatabaseException("You already start another query");
        $this->query_type = $type;
    }

    public function find($columns = [])
    {
        $this->startNewQuery("SELECT");

        if (!is_array($columns))
        {
            throw new \DatabaseException("Invalid argument in `find` function, argument must be array");
        }
        if (empty($columns))
        {
            if (empty($this->attributes))
            {
                $this->setAttributes($this->getColumns());
            }
            $columns = $this->attributes;
        }
        else
        {
            $attributes = [];
            foreach ($columns as $column => $value)
            {
                $attributes[$value] = $value;
            }
            $columns = $attributes;
        }
        $this->selected_columns = [];
        $i = 0;
        foreach ($columns as $column => $value)
        {
            $i++;
            $column = trim($column);
            $exploded_column = explode(" ", $column);
            $attribute = new ActiveAttribute($exploded_column[0], $this->table, null);

            if (count($exploded_column) > 1)
            {
                $attribute->setAlias($exploded_column[1]);
            }

            $this->selected_columns[] = $attribute;
        }
        return $this;
    }

    public function findOne($id)
    {
        return $this->where(['id' => ['=', $id]])->one();
    }

    public function delete()
    {
        $this->startNewQuery("DELETE");
    }

    public function update($update = [])
    {
        $this->startNewQuery("UPDATE");
        $this->columns_on_update = array_keys($this->attributes);
        unset($this->columns_on_update['id']);
        $cols = $this->attributes;
        unset($cols['id']);
        $this->params = $cols;
        if (!empty($update))
        {
            $this->columns_on_update = [];
            $this->params = [];
            foreach ($update as $attr => $value)
            {
                if (!array_key_exists($attr, $this->attributes))
                    throw new \DatabaseException("Unknown attribute `$attr` in table `$this->table`");
                $this->columns_on_update[] = $attr;
                $this->params[] = $value;
            }
        }
        return $this;
    }

    public function insert()
    {
        $this->startNewQuery("INSERT");
        foreach ($this->attributes as $attribute => $value)
        {
            if ($attribute == "id")
                continue;
            $this->insert_cols[$attribute] = null;
            $this->params[] = $value;
        }
        return $this;
    }

    public function exec()
    {
        if ($this->query_type == "SELECT")
            throw new \DatabaseException("Invalid function, to execute select query use ActiveQuery::all() / ActiveQuery::one() / ActiveQuery::as_array() functions");
        $this->query()->fetch();
        return $this->result;
    }

    public function addWhereCondition($conditions, $delimeter = "AND")
    {
        if (empty($conditions)) return;
        if (!is_array($conditions)) throw new \DatabaseException("Invalid condition");
        foreach ($conditions as $attribute => $condition)
        {
            $this->where_conditions[] = [[$attribute => $condition], $delimeter];
        }
    }

    public function where($conditions, $delimeter = "AND")
    {
        $this->addWhereCondition($conditions, $delimeter);
        return $this;
    }

    public function andWhere($conditions, $delimeter = "AND")
    {
        if (empty($this->where_conditions)) throw new \DatabaseException("Failed while adding new where condition");
        $this->addWhereCondition($conditions, $delimeter);
        return $this;
    }

    public function orWhere($conditions, $delimeter = "AND")
    {
        if (empty($this->where_conditions)) throw new \DatabaseException("Failed while adding new where condition");
        $this->addWhereCondition($conditions, $delimeter);
        return $this;
    }

    public function limit($num)
    {
        $this->limit = $num;
        return $this;
    }

    public function sort($sort)
    {
        if (!is_array($sort))
            throw new \DatabaseException("Invalid syntax in sort function, it must be array");
        $this->sort = $sort;
        foreach ($sort as $item => $value)
        {

        }
    }

    public function count()
    {
        return count($this->query()->fetch());
    }

    public function bind($table, $attributes)
    {
        $model = new ActiveQuery($table);
        foreach ($attributes as $table_one_attribute => $table_two_attribute)
        {
            if (!isset($this->attributes[$table_two_attribute]))
                throw new \DatabaseException("Unknown attribute `$table_two_attribute` in `$this->table` table");
            if (!isset($model->attributes[$table_one_attribute]))
                throw new \DatabaseException("Unknown attribute `$table_one_attribute` in `$table` table");
            $model->addWhereCondition([$table_one_attribute, $this->attributes[$table_two_attribute]]);
        }
        return $model;
    }

    public function joinWith($table, $usages = [], $type = "LEFT JOIN")
    {
        if (!array_key_exists($table, $this->usages))
        {
            throw new \DatabaseException("Unknown table `$table` in join query");
        }

        if (empty($usages))
        {
            foreach ($this->usages[$table] as $usage)
            {
                $usages[] = $usage['name'];
            }
        }
        foreach ($this->usages as $usages_table => $_usages)
        {
            if ($table != $usages_table) continue;
            foreach ($_usages as $usage)
            {
                if (!in_array($usage['name'], $usages)) continue;
                $usage['type'] = $type;
                $attributes = $usage['model']->attributes;

                foreach ($attributes as $attribute)
                {
                    $attribute = new ActiveAttribute($attribute->name(), $usages_table, null, md5($attribute->name())."_".$attribute->name());
                    $this->selected_columns[] = $attribute;
                    $this->joined_columns[$attribute->table()][$attribute->alias()] = $attribute;
                }
                $this->join_conditions[$table][] = $usage;
            }
        }

        return $this;
    }

    public function one()
    {
        $data = new ActiveQuery($this->connection, $this->table);
        $this->limit(1);
        $db_res = $this->as_array();

        if (!empty($db_res))
        {
            $data->related_attributes = $db_res[0]['usage'];
            unset($db_res[0]['usage']);
            $data->load($db_res[0]);
        }

        return $data;
    }

    public function all()
    {
        $data = [];
        $db_res = $this->as_array();

        foreach ($db_res as $item)
        {
            $model = new ActiveQuery($this->connection, $this->table);
            $model->related_attributes = $item['usage'] ?? [];
            unset($item['usage']);
            $model->load($item);
            $data[] = $model;
        }

        return $data;
    }

    public function as_array()
    {
        $array = [];

        $data = $this->query()->fetch();

        foreach ($data as $value)
        {
            $item = $value['static_attributes'];
            $item['usage'] = $value['related'];
            $array[] = $item;
        }

        return $array;
    }
}