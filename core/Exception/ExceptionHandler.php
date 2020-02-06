<?php


use app\core\View;

class ExceptionHandler
{
    public static function handler($exp)
    {
        if (!\app\configs\AppConfig::$debug_tools)
        {
            header("HTTP/1.0 500 Server error"); die;
        }
        $exception_name = get_class($exp);
        if (isset($exp->getTrace()[0]['type']))
        {
            $call_by = $exp->getTrace()[0]['class'].$exp->getTrace()[0]['type'].$exp->getTrace()[0]['function']."()";
        }
        else
        {
            $call_by = "";
        }
        $header_information = ["class_name" => $exception_name, "error_message" => $exp->getMessage(), "error_code" => $exp->getCode(), "file" => $exp->getFile(), "line" => $exp->getLine(), 'call_by' => $call_by];
        $trace = $exp->getTrace();
        $view = new View();
        $view->fatal_exception_render(['exp_inf' => $header_information, 'trace' => $trace]);
    }
}