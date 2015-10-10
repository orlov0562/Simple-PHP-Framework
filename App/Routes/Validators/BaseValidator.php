<?php

namespace App\Routes\Validators;

class BaseValidator
{
    public function isUnsigned($var)
    {
        return intval($var) > 0;
    }

    public function isNotEmpty($var)
    {
        return trim($var) != '';
    }

}