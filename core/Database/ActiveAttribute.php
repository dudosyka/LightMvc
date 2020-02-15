<?php


namespace app\core\database;


class ActiveAttribute
{
    private $name;

    private $alias;

    private $table;

    private $value;

    public function __construct($name, $table, $value = null, $alias = null)
    {
        $this->name = $name;

        $this->table = $table;

        $this->value = $value;

        $this->alias = $alias;
    }

    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function alias()
    {
        return $this->alias;
    }

    public function table()
    {
        return $this->table;
    }

    public function name()
    {
        return $this->name;
    }

    public function value()
    {
        return $this->value;
    }
}