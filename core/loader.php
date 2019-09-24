<?php

namespace app\core;

class loader
{
    public static function load($dir)
    {
        $list  = scandir($dir);
        unset($list[0],$list[1]);
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
}