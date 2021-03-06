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
echo "<pre>";
loader::load(__DIR__);
loader::load(__DIR__."/Exception");
loader::load("../config");
loader::load(__DIR__."/Database", true);
loader::load("../assets");
loader::load("../models");
loader::load("../widgets");

set_exception_handler(array("ExceptionHandler", "handler"));
set_error_handler(array("ErrorHandler", "handler"));

app\core\Route::start();