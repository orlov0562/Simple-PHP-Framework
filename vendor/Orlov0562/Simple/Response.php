<?php

namespace Orlov0562\Simple;

class Response {
    public function redirectToUrl($url, $code=301)
    {
        Header('Location:'.$url, TRUE, $code);
        die(0);
    }
}
