<?php

namespace app\core;


class Controller
{
    public $view;

    public $view_template;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->view = new View();
    }

    public function action_index()
    {
        phpinfo();
    }

    /**
     * @param $view_name
     * @param array $data
     * @param HTMLTag|NULL $head
     */
    public function render ($view_name, $data = [], HTMLTag $head = NULL)
    {
        $this->view->render($view_name, $data, $head);
    }

    /**
     * @param $path
     */
    public function redirect ($path)
    {
        $path = "http://".$_SERVER['HTTP_HOST']."/".$path;
        header("Location: $path", true, 302);
    }

    public function generate_token($length)
    {
        $alphabet = [
            'a',
            'b',
            'c',
            'd',
            'e',
            'f',
            'g',
            'i',
            'j',
            'k',
            'l',
            'm',
            'n',
            'o',
            'p',
            'q',
            'r',
            's',
            't',
            'u',
            'v',
            'w',
            'x',
            'y',
            'z',
            "A",
            "B",
            "C",
            "D",
            "E",
            "F",
            "G",
            "I",
            "J",
            "K",
            "L",
            "M",
            "N",
            "О",
            "P",
            "Q",
            "R",
            "S",
            "T",
            "U",
            "V",
            "W",
            "X",
            "Y",
            "Z",
            "1",
            "2",
            "3",
            "4",
            "5",
            "6",
            "7",
            "8",
            "9",
            "0"
        ];
        $result = "";
        for ($i = 0; $i < $length; $i++)
        {
            $result .= $alphabet[rand(0, count($alphabet) - 1)];
        }
        $_SESSION['api_auth_token'] = $result;
        return $result;
    }
}