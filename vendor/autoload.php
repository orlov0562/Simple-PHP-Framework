<?php

    spl_autoload_register(function($class){
        $classPath = dirname(__FILE__).'/'.str_replace('\\', '/', $class).'.php';
        if (file_exists($classPath)) include $classPath;
    });