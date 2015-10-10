<?php

namespace App\Controllers\Frontend;

class ErrorsController {
    public function NotFoundAction()
    {
        echo '404. The page that you are looking for has been moved or deleted';
    }
}
