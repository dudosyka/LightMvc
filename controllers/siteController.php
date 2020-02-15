<?php


namespace app\controllers;


use app\assets\AppAssets;
use app\configs\AppConfig;
use app\core\Controller;
use app\core\database\ActiveQuery;
use http\Env\Request;

class siteController extends Controller
{
    public function action_index()
    {
        $test = new ActiveQuery(AppConfig::$db_config, "user_post");
    }
}