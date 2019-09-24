<?php
//session_start();
include __DIR__.'/loader.php';

use app\core\loader;

loader::load_libs();
//TODO проверить работает ли автозагрузка библиотек
//include __DIR__.'/libs/mpdf/vendor/autoload.php';


/*
 * Ну тут небольшое изменение совсем
 * Мне пришла "гениальная" идея заюзать loader() для папки ядра
 */
loader::load(__DIR__);
loader::load("../config");
loader::load("../assets");
loader::load("../models");
loader::load("../widgets");

app\core\Route::start();