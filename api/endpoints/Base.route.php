<?php 

namespace Siktec\App\Api\Endpoints;

use RequestInterface;
use \Siktec\Frigate\Base;
use \Siktec\Frigate\Api\EndPoint;
use \Siktec\Frigate\Routing\Http;
/**
 * @OA\Info(
 *   version="1.0.0",
 *   title="App API" 
 * )
 * @OA\Server(
 *   url="http://localhost/",
 * )
 * 
 * @OA\SecurityScheme(
 *   securityScheme="apiAuth",
 *   type="http",
 *   scheme="basic",
 *   in="header",
 *   name="Authorization"
 * )
 * 
 * @OA\Response(
 *   response=401, 
 *   description="Not Authorized check credentials"
 * )
 * 
 * @OA\Response(
 *   response=500,
 *   description="Internal server error"
 * )
 * 
 */
class BaseEndPoint extends EndPoint { 

    public function __construct(bool $debug = false, $auth = false, $auth_method = "basic")
    {
        parent::__construct($debug, $auth, $auth_method);
    }

    public function call(array $context, Http\RequestInterface $request) : Http\Response {

        /** @var MyRequest $request */ // we use this to force type hinting

        Base::debug($this, "Execute endpoint");

        var_dump("11");
        var_dump($request::class);

        print $request->requireAuthorization("basic");

        $response = new Http\Response(
            status    : 200,
            headers : [],
            body    : "App API"
        );

        return $response;
    }

}