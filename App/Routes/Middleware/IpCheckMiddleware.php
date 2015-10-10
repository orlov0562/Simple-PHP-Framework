<?php

namespace App\Routes\Middleware;

class IpMiddleware {
    public function isClientIpInList(array $ipList) {
        return in_array($_SERVER['REMOTE_ADDR'], $ipList);
    }
}