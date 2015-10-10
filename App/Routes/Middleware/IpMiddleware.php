<?php

namespace App\Routes\Middleware;

class IpMiddleware {
    public function isClientIp(array $ipList) {
        if (!in_array($_SERVER['REMOTE_ADDR'], $ipList))
        {
            die('Your IP not allowed in middleware');
        }
    }
}