<?php

namespace app\core;

use app\configs\AppConfig;

class FileManager
{
    public $file;

    public function get_file_type()
    {
        return explode(".", $this->file['name'])[1];
    }

    public function get_file_name()
    {
        return explode(".", $this->file['name'])[0];
    }

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function save($name = "")
    {
        $filename = microtime(true);
        $filename = $name . "_" . explode(".", $filename)[0];
        $dirname = "";

        $valid_file_types = AppConfig::get_valid_types();
        $filetype = $this->get_file_type();

        if (!in_array($filetype, $valid_file_types))
        {
            return false;
        }

        if (in_array($filetype, AppConfig::$valid_file_types))
        {
            $dirname = "documents";
        }
        else
        {
            $dirname = "images";
        }

        $year = date("Y");

        if (!file_exists("../web/user_files/$dirname/$year"))
        {
            mkdir("../web/user_files/$dirname/$year");
        }

        $path = "$dirname/$year/$filename.$filetype";

        if (move_uploaded_file($this->file['tmp_name'], "../web/user_files/$path"))
        {
            return $path;
        }
        else
        {
            return false;
        }
    }
}