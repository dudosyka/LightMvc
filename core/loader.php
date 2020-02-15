<?php

namespace app\core;

class loader
{
    public static function load($dir, $reverse = false)
    {
        $list  = scandir($dir);
        unset($list[0],$list[1]);
        $list = ($reverse) ? array_reverse($list) : $list;
        foreach ($list as $item)
        {
            if (is_file($dir."/".$item))
            {
                include_once $dir."/".$item;
            }
        }
    }

    public static function load_libs()
    {
        $dir = __DIR__."/libs";
        $list  = scandir($dir);
        unset($list[0],$list[1]);

        foreach ($list as $item)
        {
            if (is_dir($dir."/".$item) && $item == "vendor")
            {
                $vendor_dir = $dir."/".$item;
                $vendor_list = scandir($vendor_dir);
                unset($vendor_list[0], $vendor_list[1]);

                foreach ($vendor_list as $vendor_item)
                {
                    if ($vendor_item == "autoload.php")
                    {
                        include_once $vendor_dir."/".$vendor_item;
                    }
                }
            }
        }
    }

    public static function load_css_libs($lib_dir)
    {
        $dir = __DIR__."/../web/assets/css/".$lib_dir;
        if (file_exists($dir))
        {
            $dir = scandir($dir);
            unset($dir[0], $dir[1]);
            $result = [];
            foreach ($dir as $item)
            {
                $file = explode(".", $item);
                $doctype = $file[count($file) - 1];
                if ($doctype == 'css' && count($file) < 3)
                    $result[] = "$lib_dir/$item";
            }
            return $result;
        }
        return [false];
    }
}