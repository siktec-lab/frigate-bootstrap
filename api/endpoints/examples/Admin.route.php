<?php 

namespace Siktec\Homevr\Api\Endpoints;

use \Siktec\Frigate\Base;
use \Siktec\Frigate\Api\EndPoint;
use \Siktec\Frigate\Api\EndPointContext;
use \Siktec\Frigate\Routing\Http;
use \Siktec\Homevr\Admin\Pages;

class AdminEndPoint extends EndPoint { 

    use EndPointContext;

    public function __construct(bool $debug = false, bool $auth = false, string $auth_method = "session")
    {
        parent::__construct($debug, $auth, $auth_method);
    }

    public function call(array $context, Http\RouteRequest $request) : Http\Response {

        Base::debug($this, "Execute endpoint - CreateProject\n".$request);
        
        $user = $request->requireAuthorization($this->authorize_method, throw : false);

        $page = $this->get_context("page", $context, "dashboard");

        if ($user !== false) {
            switch ($page) { 
                case "projects":
                    $page = new Pages\Projects(state : "default");
                    break;
                case "test":
                    $page = new Pages\Test(state : "default");
                    break;
                case "media":
                        $page = new Pages\Media(state : "default");
                        break;
                default:
                    $page = new Pages\Dashboard(state : "default");
            }
        } else {
            $page = new Pages\Login();
        }
        
        $response = new Http\Response(
            status    : 200,
            headers : [
                "Content-Type" => "text/html"
            ],
            body : $page->compile()
        );

        return $response;
    }

}