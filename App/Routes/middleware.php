<?php

    return [
        'role' => ['App\Routes\Middleware\AuthMiddleware', 'validateRole'],
        'autologin' => ['App\Routes\Middleware\AuthMiddleware', 'autoLoginByIp'],
        'ip' => ['App\Routes\Middleware\IpMiddleware', 'isClientIp'],
        'test' => ['App\Routes\Middleware\TestMiddleware', 'test'],

    ];
