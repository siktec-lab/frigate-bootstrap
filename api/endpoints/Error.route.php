<?php 

namespace Siktec\App\Api\Endpoints;

use \Siktec\Frigate\Base;
use \Siktec\Frigate\Api\EndPoint;
use \Siktec\Frigate\Routing\Http;

class ErrorEndPoint extends EndPoint { 

    public function __construct(bool $debug = false, $auth = false, $auth_method = "basic")
    {
        parent::__construct($debug, $auth, $auth_method);
    }

    public function call(array $context, Http\RequestInterface $request) : Http\Response {

        //Check what is the expected data to be returned?
        $return_type = $request->expects;
        $json = $return_type === "application/json";

        //The code Status:
        $code = in_array($context["code"], [400, 401, 403, 404, 405, 406, 500]) ? $context["code"] : 500;

        //The message:
        $message = $context["message"] ?? "Unknown Error";

        //The body:
        switch ($code) {
            case 400:
            case 401:
            case 403:
            case 404:
            case 405:
                $body = "Method Not Allowed";
                $body = $json ? json_encode(["code" => $code, "message" => $message]) 
                              : "<h1>{$code}</h1></br><h2>{$message}</h2>";
                break;
            case 406:
                $body = "Not Acceptable";
                $body = $json ? json_encode(["code" => $code, "message" => $message]) 
                              : "<h1>{$code}</h1></br><h2>{$message}</h2>";
                break;
            case 500:
                if (!$this->debug) {
                    $body = $json ? json_encode(["code" => 500, "error" => "Internal Server Error"]) 
                                  : "Internal Server Error";
                } else {
                    $body = $json ? json_encode(["code" => 500, "error" => $message, "trace" => $context["trace"]]) 
                                  : $context["trace"].PHP_EOL.$message;
                }
                break;
            default:
                $body = $json ? json_encode(["error" => "Unknown Error"]) 
                              : "Unknown Error";
                break;
        }

        $response = new Http\Response(
            status      : $json ? $code : 200,
            headers     : [ "Content-Type" => $return_type ],
            body        : $body
        );
        return $response;
    }

}