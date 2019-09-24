<?php


namespace app\controllers;


use app\core\Controller;

class siteController extends Controller
{
    public function action_index()
    {
        $this->render("index");
    }
}