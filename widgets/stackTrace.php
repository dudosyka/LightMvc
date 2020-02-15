<?php


namespace app\widgets;


use app\core\HTMLTag;

class stackTrace
{
    public $trace_array;

    public function __construct($trace_array = [])
    {
        $this->trace_array = $trace_array;
    }

    private function get_line_from_file($path, $line)
    {
        $file = fopen($path, "r");
        $i = 0;
        $find = "";
        while (($str = fgets($file, 4096)) !== false) {
            $i++;
            if ($i == $line)
            {
                $find = $str;
                break;
            }
        }
        fclose($file);
        return $find;
    }

    public function render()
    {
//        var_dump($this->trace_array);die;
        $trace = new HTMLTag(
            'div',
            ['class' => 'stack_trace'],
            ""
        );
        foreach ($this->trace_array as $item =>$value)
        {
            if ($item == 0)
            {
                continue;
            }
            $trace_item = new HTMLTag(
                'div',
                ['class' => 'stack_item'],
                ""
            );
            $top = new HTMLTag(
                "div",
                ['class' => 'top'],
                ""
                );
            $num = new HTMLTag(
                'h4',
                [],
                "#".$item
            );
            $header = new HTMLTag(
                'div',
                ['class' => 'header'],
                ""
            );
            $error_place = new HTMLTag(
                'h6',
                [],
                "in "
            );
            $file = (!isset($value['file'])) ? "file not found (you can find it use class name) " : $value['file'];
            $file = new HTMLTag(
                'i',
                [],
                $file
            );
            $line = (!isset($value['line'])) ? "file not found, so line of file too (you can find it use class name) " : "line ".$value['line'];
            $line = new HTMLTag(
                'b',
                [],
                $line
            );
            $func_content = "";
            if (isset($value['class']))
            {
                $func_content = $value['class'].$value['type'].$value['function']."()";
            }
            else
            {
                $func_content = $value['function']."()";
            }
            $function = new HTMLTag(
                'i',
                [],
                $func_content
            );
            $error_place->push_back_content($file->render().", on ".$line->render().", in function ".$function->render());
            $header->push_back_content($error_place);
            $top->push_back_content($num);
            $top->push_back_content($header);
            $file = (!isset($value['file'])) ? "file not found (you can find it use class name) " : $value['line']." | ".$this->get_line_from_file($value['file'], $value['line']);
            $code_block = new HTMLTag(
                'div',
                ['class' => 'code-block'],
                $file
            );
            $trace_item->push_back_content($top);
            $trace_item->push_back_content($code_block);
            $trace->push_back_content($trace_item);
        }
        return $trace->render();
    }
}