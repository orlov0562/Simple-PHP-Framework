<?php

    require_once '../App/bootstrap.php';

    App\S::run([
        'env' => 'dev',
        'baseUrl' => 'auto', // http://test.sv/framework
        'webDir' => dirname(__FILE__),
        'appDir' => dirname(dirname(__FILE__)).'/App/',
    ]);
