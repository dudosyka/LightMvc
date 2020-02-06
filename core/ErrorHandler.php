<?php

namespace app\core;


class ErrorHandler
{
    public static function handler($err_n, $err_str, $err_file, $err_line)
    {
        if (!\app\configs\AppConfig::$debug_tools)
        {
            var_dump(true);
            header("HTTP/1.0 500 Server error");
        }
//        ob_clean();
//        ob_start();
        var_dump($err_n);
        var_dump($err_str);
        var_dump($err_file);
        var_dump($err_line);
//        ob_flush();
        die;
    }
}