<?php 

namespace Siktec\App\Api\Endpoints;

use \Siktec\Frigate\Base;
use \Siktec\Frigate\Routing\Files\FileServer;
use \Siktec\Frigate\Routing\Http;

/**
 * @OA\Get(
 *   tags={"Files"},
 *   path="/file/{file}",
 *   summary="File Server access point",
 *   @OA\Parameter(
 *     name="file",
 *     in="path",
 *     required=true,
 *     description="File key to download / load"
 *   ),
 *   
 *   @OA\Response(response=200, description="OK"),
 *   @OA\Response(response=401, description="Unauthorized"),
 *   @OA\Response(response=404, description="File not found"),
 *   @OA\Response(response=500, description="Internal Server Error")
 * )
 */
class FileHandle extends FileServer {

    public function __construct(
        bool $debug = false, 
        bool $auth = false, 
        string $auth_method = "basic"
    ) {
        parent::__construct($debug, $auth, $auth_method);
    }
    
    /**
     * serve_file
     * expected method by the FileServer class which allows to
     * implement you own file serving logic return path of file given a key.
     * @param  mixed $key
     * @return string
     */
    public function serve_file(string $key) : ?array {
		
		//Define your own serving file logic
        $file = Base::$db->where("file_key", $key)->getOne("media");
        return [
            !empty($file) ? $file["path"] : null,
            !empty($file) ? $file["file_name"] : null,
        ];

    }

}