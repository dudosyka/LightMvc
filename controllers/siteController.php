<?php


namespace app\controllers;


use app\assets\AppAssets;
use app\configs\AppConfig;
use app\core\Controller;

class siteController extends Controller
{
    public function action_index()
    {
        AppAssets::get_auto_load_css();
        $this->render("index");
    }
}