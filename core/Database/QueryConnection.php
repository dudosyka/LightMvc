<?php


namespace app\core\database;


class QueryConnection
{
    private $host;

    private $database;

    private $user;

    private $password;

    public function __construct($db_config = [])
    {
        if ($db_config instanceof QueryConnection)
        {
            $this->user = $db_config->getUser();
            $this->password = $db_config->getPassword();
            $this->host = $db_config->getHost();
            $this->database = $db_config->getDatabase();
        }
        else
        {
            $this->user = $db_config['user'] ?? null;
            $this->password = $db_config['password'] ?? null;
            $this->database = $db_config['database'] ?? null;
            $this->host = $db_config['host'] ?? null;
        }
    }

    public function connect()
    {
        return new \PDO('mysql:host=' . $this->host . ';dbname=' . $this->database . ';charset=utf8', $this->user, $this->password);
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function setDatabase($database)
    {
        $this->database = $database;
    }

    public function setHost($host)
    {
        $this->host = $host;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getDatabase()
    {
        return $this->database;
    }

    public function getHost()
    {
        return $this->host;
    }
}