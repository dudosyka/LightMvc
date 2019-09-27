<?php

namespace app\core;

class Utils
{
    static public function to_json($array)
    {
        return (is_array($array)) ? json_encode($array, JSON_UNESCAPED_UNICODE) : false;
    }

    static public function from_json($str)
    {
        return (!is_null($str)) ? json_decode($str) : [];
    }
}