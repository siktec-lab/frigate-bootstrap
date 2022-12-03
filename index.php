<?php
define("APP_VERSION","1.0.0");
define("SHOW_ERRORS", true);
ini_set('error_log', __DIR__.'/php_errors.log');
error_reporting(SHOW_ERRORS ? -1 : 0);
ini_set('display_errors', SHOW_ERRORS ? 'on' : 'off');

require_once "vendor/autoload.php";

use \Siktec\Frigate\Base;
use \Siktec\Frigate\Routing\Http\Methods;
use \Siktec\Frigate\Routing\Router;
use \Siktec\Frigate\Routing\Route;
use \Siktec\Frigate\Routing\Http;
use \Siktec\Frigate\Swagger\Page\SwaggerUI;
use \Siktec\Frigate\Swagger\Parser as SwaggerParser;
use \Siktec\App\Api\Endpoints;
use \Siktec\App\Routing\Requests;
use \Siktec\App\Api\Endpoints\FileHandle;

/*****************************************************
 * Init Frigate Application:
 *****************************************************/

Base::init(
    config      : __DIR__,  //path to .env file - required
    connect     : false,     // Connect to database - will die if connection fails 
    session     : true,     // Start a session if not already started
    page_buffer : true      // Buffer output will capture all unexpected output until the end of the script
);

Base::set_paths(
    root       : __DIR__, //Application root path
    base_path  : Base::ENV_STR("ROOT_FOLDER", "/"),
    app_url    : Base::ENV_STR("APP_DOMAIN",  "http://localhost/")
);

/*****************************************************
 * Init Frigate Route:
 *****************************************************/
Router::init(debug : Base::ENV_BOOL("DEBUG_ROUTER"));
Router::parse_request(APP_BASE_URL_PATH);

/*****************************************************
 * General Errors Handler:
 *****************************************************/
Router::define_error("any", new Route("",
    context : [], //Error context always has the same structure code, message, trace.
    returns : [ "text/html", "application/json" ],
    func : new Endpoints\ErrorEndPoint( Base::ENV_BOOL("EXPOSE_ERRORS") )
));


/*****************************************************
 * File Server:
 *****************************************************/

// $FileServer = new FileHandle(
    // debug       : Base::ENV_BOOL("DEBUG_ENDPOINTS"), 
    // auth        : true, 
    // auth_method : "session"
// );
// $FileServer->set_folders(
    // temp        : APP_ROOT.'/'.Base::ENV_STR("UPLOAD_TEMP_FOLDER"),
    // storage     : APP_ROOT.'/'.Base::ENV_STR("UPLOAD_STORAGE_FOLDER"),
    // create      : true
// );
// $FileServer->allowed_files = [
    // "image/*",
    // "application/octet-stream",
    // "application/x-zip-compressed"
// ];

// Router::define([Methods::GET, Methods::POST, Methods::PATCH, Methods::DELETE, Methods::HEAD],
    // route   : new Route("file/{file}", 
    // context : [ "file" => "string" ],
    // returns : [ "*/*" ],
    // func : $FileServer
// ));

/*****************************************************
 * Swagger API Documentation:
 *****************************************************/
Router::define([Methods::GET],
    route   : new Route("docs", 
    context : [],
    returns : [ "text/html" ],
    func    : function(array $context, Http\RouteRequest $request) : Http\Response {
        $swagger = new SwaggerUI();
        $parser = new SwaggerParser(["./api"], SwaggerParser::OUTPUT_JSON);
        $swagger->json_source(
            $parser->generate()
        );
        $response = new Http\Response(
            status    : 200,
            headers : [ "Content-Type" => "text/html" ],
            body    : $swagger->compile()
        );
        return $response;
    }
));

/*****************************************************
 * Endpoints Routing:
 *****************************************************/
Router::define(Methods::GET, new Route("/",
    returns : [ "text/html", "application/json" ],
    func    : new Endpoints\BaseEndPoint(
        Base::ENV_BOOL("DEBUG_ENDPOINTS")
    ),
    request_mutator: new ReflectionClass(Requests\MyRequest::class)
));

// Router::define(Methods::POST, new Route("actions/{action}",
    // context : [
        // "files_temp"     => APP_ROOT.'/'.Base::ENV_STR("UPLOAD_TEMP_FOLDER"),
        // "files_storage"  => APP_ROOT.'/'.Base::ENV_STR("UPLOAD_STORAGE_FOLDER"),
        // "action"         => "string" 
    // ],
    // returns : [ "application/json" ],
    // func    : new Endpoints\ActionsEndPoint(
        // debug       : Base::ENV_BOOL("DEBUG_ENDPOINTS"), 
        // auth        : true, 
        // auth_method : "session|basic"
    // )
// ));


// Router::dump_routes();
$Response = Router::load();
$unexpected = Base::end_page_buffer();  
Router::send_response($Response);
echo PHP_EOL.$unexpected;