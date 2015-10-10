<?php

namespace App\Routes\Middleware;
use App\S;

class AuthMiddleware {
    public function validateRole($role) {

        $currentUserRole = 'guest'; // получает роль текущего пользователя

        if ($currentUserRole!==$role)
        {
            //$url = S::router()->getUrlByRoute('frontend', 'login', []);
            //$url = S::router()->getUrlByRoute('backend', 'posts', ['page'=>5]);
            //S::response()->redirect($url);
        }
    }

    public function autoLoginByIp() {
        /*
         * тут логиним по ip и редиректим
         *
         */
    }
}
