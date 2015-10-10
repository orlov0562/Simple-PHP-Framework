<?php

    return [
        'role' => ['App\Routes\Middleware\RoleMiddleware', 'isCurrentUserRole'],
        'ip' => ['App\Routes\Middleware\IpMiddleware', 'isClientIp'],
    ];
