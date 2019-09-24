<?php
namespace app\configs;

class AppConfig {

    // status_code для name
    const DATABASE_ERROR = 1000;
    const REQUEST_ERROR = 1001;
    const VALIDATE_ERROR = 1002;
    const FORBIDDEN = 1003;
    const RECORD_NOT_FOUND = 1004;
    const DUPLICATE_RECORD = 1005;
    const FAILED_UPLOADED = 1006;
    const FAILED_PASSWORD = 107;
    const FAILED_LOGIN = 1008;
    const SUCCESS = 200;

    public static $layout = "template_view.php";

    public static $default_view = "index";

    public static $default_view_dir = "";

    public static $route = [
        'default_controller' => 'site',
        'default_action' => 'index'
        ];

    public static $db_config = [
        'host' => 'localhost',
        'user' => '',
        'password' => '',
        'database' => ''
        ];

    public static $valid_file_types = ['docs', 'docx', 'doc', 'odf', 'pdf', 'Djvu', 'txt'];

    public static $valid_image_types = ['png', 'jpg', 'jpeg', 'bmp', 'gif'];

    public static function get_valid_types()
    {
        return array_merge(self::$valid_file_types, self::$valid_image_types);
    }

    public static function assets()
    {
        return "http://".$_SERVER['HTTP_HOST']."/web/assets";
    }
}
