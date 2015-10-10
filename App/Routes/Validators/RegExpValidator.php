<?php

namespace App\Routes\Validators;

class RegExpValidator
{
    public function isMatched($var, $regexp)
    {
        return preg_match($regexp, $var);
    }
}