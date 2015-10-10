<?php

namespace App\Routes\Middleware;

class RoleMiddleware {
    public function isCurrentUserRole($role) {
        return false;
    }
}
