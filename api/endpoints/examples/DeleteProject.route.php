<?php 

namespace Siktec\Homevr\Api\Endpoints;

use \Siktec\Frigate\Base;
use \Siktec\Frigate\Api\EndPoint;
use \Siktec\Frigate\Api\EndPointContext;
use \Siktec\Frigate\Routing\Http;
use \Siktec\Frigate\Tools\Input\Sanitize;
use \Siktec\Frigate\Tools\FileSystem\FilesHelper;
use \Siktec\Homevr\Api\ApiMethods;

/**
 * @OA\Delete(
 *   tags={"Projects"},
 *   path="/project/{id}",
 *   summary="Delete a project",
 *   description="Delete a project given its ID",
 *   security={{"apiAuth":{}}},
 *   @OA\Parameter(
 *     name="id",
 *     in="path",
 *     description="ID of the project to delete",
 *     required=true,
 *     @OA\Schema(type="integer")
 *   ),
 *   
 *  @OA\Response(
 *    response=200, 
 *    description="OK",
 *    @OA\JsonContent(
 *      type="object",
 *      @OA\Property(property="message", type="string", description="Success message", example="success"),
 *      @OA\Property(property="project", type="integer", description="id of the deleted project")
 *    )
 *  ),
 *  @OA\Response(
 *    response=404, 
 *    description="Project not found"
 *  ),
 *  @OA\Response(response=401, ref="#/components/responses/401"),
 *  @OA\Response(response=500, ref="#/components/responses/500")
 * )
 */
class DeleteProjectEndPoint extends EndPoint { 

    use EndPointContext;

    public function __construct(bool $debug = false, bool $auth = false, string $auth_method = "basic") {
        
        parent::__construct($debug, $auth, $auth_method);

    }

    public function call(array $context, Http\RouteRequest $request) : Http\Response {

        Base::debug($this, "Execute endpoint - DeleteProject\n".$request);
        
        //Authorize:
        if ($this->authorize) {
            $user = $request->requireAuthorization($this->authorize_method);
        }

        //A response object
        $response = new Http\Response(
            status: 200, 
            headers: [
                "content-type" => "application/json",
                "X-Perform"    => $request->isTest() ? "Test" : "Live"
            ]
        );

        //Get data:
        $id = Sanitize::integer($this->get_context("id", $context, 0), 0);
        $project = ApiMethods::getProject($id);
        if (is_null($project)) {
            throw new \Exception("Project not found", 404);
        }

        //update:
        Base::$db->startTransaction();
        $success = Base::$db->where("id", $id)->update("projects", [
            "status" => 0,
            "updated" => Base::$db->now(),
            "updated_by" => Sanitize::email($user)
        ], 1);

        //Save:
        try {
            if ($request->isTest()) {
                Base::$db->rollback();
            } else if ($success) {
                Base::$db->commit();
            } else {
                throw new \Exception("Internal database error", 500);
            }
        } catch (\Exception $e) {
            throw new \Exception("Internal database error", 500);
        }
 
        $response->setBodyJson([
            "message"       => "success",
            "building"      => $project->id
        ]);

        return $response;
    }

}