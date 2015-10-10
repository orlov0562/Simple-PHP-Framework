<?php

    // Регистрируем автозагрузчик приложения

    spl_autoload_register(function($class)
    {
        if (substr($class, 0, 4)=='App\\') {
            $classFilePath = dirname(__FILE__).'/'.str_replace('\\', '/', substr($class, 4)).'.php';

            if (file_exists($classFilePath))
            {
                include $classFilePath;
            }
        }
    });

    // Подключаем загрузчик вендоров

    require_once '../vendor/autoload.php';