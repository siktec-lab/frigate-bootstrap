<?php

namespace Siktec\App\Routing\Requests;

use \Siktec\Frigate\Routing\Http;

class MyRequest extends Http\RequestDecorator {
    
    public function requireAuthorization(string $method, bool $throw = true) : string|bool {

        return "Hello World";

    }

}