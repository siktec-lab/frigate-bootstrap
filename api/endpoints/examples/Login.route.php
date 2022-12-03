<?php 

namespace Siktec\Homevr\Api\Endpoints;

use \Siktec\Frigate\Base;
use \Siktec\Frigate\Api\EndPoint;
use \Siktec\Frigate\Api\EndPointContext;
use \Siktec\Frigate\Routing\Http;
use \Siktec\Frigate\Tools\Input\Sanitize;
use \Siktec\Homevr\Admin\Pages;

class LoginEndPoint extends EndPoint { 

    use EndPointContext;

    private function new_token() : string {
        return random_bytes(32);
    }

    public function __construct(bool $debug = false, bool $auth = false, string $auth_method = "basic")
    {
        parent::__construct($debug, $auth, $auth_method);
    }

    private function login(Http\RouteRequest $request) : Http\Response {

        //Get credentials:
        $credentials = Sanitize::filter_empty(
            Sanitize::filter_keys($request->getPostData(), ["user", "key"])
        );

        //Validate:
        if (count($credentials) === 2 && $credentials["key"] === $_ENV["ADMIN_KEY"]) {
            // create session:
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $token = $this->new_token();
            $cookie = base64_encode($credentials["user"].":".$token);
            $_SESSION["AUTHTOKEN"] = $token;
            setcookie("AUTHTOKEN", $cookie, time() + 3600, "/");
            $page = new Pages\Dashboard(state : "default");

        } else {
            // Login error:
            $page = new Pages\Login(state : "show-error-login");
        }

        return new Http\Response(
            status    : 200,
            headers : ["Content-Type" => "text/html"],
            body    : $page->compile()
        );
    }

    private function logout(Http\RouteRequest $request) : Http\Response {
        //Get action value from legit post data:
        //NOTE: we do this to avoid indirect logout hacks: 
        $action = Sanitize::filter_empty(
            Sanitize::filter_keys($request->getPostData(), ["perform"])
        );
        $action = Sanitize::array_value("perform", $action);

        //Guard:
        if ($action === "logout") {
            // delete session:
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (isset($_SESSION["AUTHTOKEN"])) 
                unset($_SESSION["AUTHTOKEN"]);
            setcookie("AUTHTOKEN", null, time() - 3600*30, "/");
        }
        $page_response = new AdminEndPoint();
        return $page_response->call([], $request);
    }

    public function call(array $context, Http\RouteRequest $request) : Http\Response {

        Base::debug($this, "Execute endpoint - CreateProject\n".$request);

        $perform = $this->get_context("perform", $context, "logout");

        switch ($perform) {
            case "login": {
                $response = $this->login($request);
            } break;
            default: {
                $response = $this->logout($request);
            } break;
        }

        return $response;
    }

}
