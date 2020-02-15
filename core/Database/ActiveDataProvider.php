<?php


namespace app\core\database;


class ActiveDataProvider
{
    private $originalData = [];

    private $sortedData = [];

    private $sort = [];

    public function __construct($data, $sort)
    {
        if (!$data instanceof ActiveQuery)
            throw new \DatabaseException("Data must be ActiveQuery object");
        $this->data = $data;
        $this->sort = $sort;
    }

    private function sort()
    {
        foreach ($this->sort as $item => $condition)
        {
            if (!array_key_exists($item, $this->data->attributes))
            {
                if (isset($condition['table']))
                {
                    $i = 0;
                    foreach ($this->data->getUsages() as $usage)
                    {
                        if (!array_key_exists($item, $usage['model']->attributes))
                            $i++;
                    }
                    if ($i == count($this->data->getUsages()))
                        throw new \Exception("Undefined attribute `$item`");
                }
                else
                {
                    throw new \Exception("Undefined attribute `$item`");
                }
            }
        }
    }

    public function getData()
    {

    }
}