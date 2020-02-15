<?php


namespace app\core\database;


class Query
{
    private $connection;

    private $query;

    private $params;

    public $result;

    public $errors;

    public function __construct($connection)
    {
//        var_dump($connection);die;
        $this->connection = $connection->connect();
    }

    public function exec($query, $params)
    {
        $this->query = $query;
        $this->params = $params;

        if ($this->query == "")
        {
            throw new \DatabaseException("Empty query");
        }

        $query = $this->connection->prepare($this->query);
        $query->execute($this->params);

//        if (is_array($query))
//            var_dump($query);die;

        $this->errors = $query->errorInfo();
        $this->result = $query;

        if (!is_null($this->errors[1]))
        {
            throw new \DatabaseException("Query error {$this->errors[1]} `{$this->errors[2]}` (query str => '$this->query')");
        }

        return $this;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getResult()
    {
        return $this->result;
    }
}