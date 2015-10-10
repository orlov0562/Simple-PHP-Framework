<?php

namespace App\Routes\Middleware;

class TestMiddleware {
    public function test($param1, $param2)
    {
        echo 'Test middleware, p1='.$param1.', p2='.$param2.'<br>';
    }
}

