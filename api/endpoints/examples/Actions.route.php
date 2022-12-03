<?php 

namespace Siktec\Homevr\Api\Endpoints;

use \Siktec\Frigate\Base;
use \Siktec\Frigate\Api\EndPoint;
use \Siktec\Frigate\Api\EndPointContext;
use \Siktec\Frigate\Routing\Http;
use \Siktec\Frigate\Tools\FileSystem\DirectoryHelper;
use \Siktec\Homevr\Admin\Pages;

class ActionsEndPoint extends EndPoint { 

    use EndPointContext;

    public function __construct(bool $debug = false, bool $auth = false, string $auth_method = "session")
    {
        parent::__construct($debug, $auth, $auth_method);
    }

    public function clear_temporary_files(string $temp) : Http\Response {
        if (DirectoryHelper::clear_folder($temp, false)) {
            return new Http\Response(
                status    : 200,
                headers : [
                    "Content-Type" => "application/json"
                ],
                body : json_encode([
                    "message" => "cleared temp folder"
                ])
            );
        }
        throw new \Exception("Could not clear temp folder", 500);
    }

    public function call(array $context, Http\RouteRequest $request) : Http\Response {

        Base::debug($this, "Execute endpoint - CreateProject\n".$request);
        
        $user = $request->requireAuthorization($this->authorize_method, throw : true);

        $action = $this->get_context("action", $context, null);

        switch ($action) { 
            case "clear-cache": {
                    $temp = $this->get_context("files_temp", $context, false);
                    return $this->clear_temporary_files($temp);
                } break;
            default:
                return new Http\Response(
                    status    : 404,
                    headers : [
                        "Content-Type" => "application/json"
                    ],
                    body : json_encode([
                        "message" => "Unknown action"
                    ])
                );
        }
    }

}