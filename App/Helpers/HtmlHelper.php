<?php

namespace App\Helpers;

class HtmlHelper {
    public function esc($string)
    {
        return htmlspecialchars($string, ENT_QUOTES);
    }
}
