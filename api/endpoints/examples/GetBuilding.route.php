<?php 

namespace Siktec\Homevr\Api\Endpoints;

use \Siktec\Frigate\Base;
use \Siktec\Frigate\Api\EndPoint;
use \Siktec\Frigate\Api\EndPointContext;
use \Siktec\Frigate\Routing\Http;
use \Siktec\Homevr\Api\ApiMethods;

/**
 * 
 * @OA\Get(
 *   tags={"Buildings"},
 *   path="/building/{id}",
 *   summary="Get a specific building",
 *   description="Get a specific building given a building id",
 *   
 *   @OA\Parameter(
 *     name="id",
 *     in="path",
 *     required=true,
 *     description="Building id to get"
 *   ),
 *   
 *  @OA\Response(
 *    response=200, 
 *    description="OK",
 *    @OA\JsonContent(
 *      type="object",
 *      @OA\Property(property="message", type="string", enum={"notset", "success"}),
 *      @OA\Property(property="building", ref="#/components/schemas/Building")
 *    )
 *  ),
 *  @OA\Response(
 *    response=400, 
 *    description="Required positive numeric query parameter 'id'"
 *  ),
 *  @OA\Response(response=401, ref="#/components/responses/401"),
 *  @OA\Response(response=500, ref="#/components/responses/500")
 * )
 */
class GetBuildingEndPoint extends EndPoint { 

    use EndPointContext;

    public function __construct(bool $debug = false, $auth = false, $auth_method = "basic")
    {
        parent::__construct($debug, $auth, $auth_method);
    }

    public function call(array $context, Http\RouteRequest $request) : Http\Response {

        Base::debug($this, "Execute endpoint - GetBuilding\n".$request);

        //Authorize:    
        if ($this->authorize) {
            $request->requireAuthorization($this->authorize_method);
        }

        //A response object
        $response = new Http\Response(
            status: 200, 
            headers: [
                "content-type" => "application/json",
                "X-Perform"    => $request->isTest() ? "Test" : "Live"
            ]
        );

        //Validate:
        $id = $this->get_context("id", $context, 0);
        if ($id <= 0) {
            throw new \Exception("Required positive numeric query parameter 'id'", 400);
        }

        //Get project:
        $building = ApiMethods::getBuilding($id);
        if (is_null($building)) {
            throw new \Exception("Internal database error", 500);
        }
        $response->setBodyJson([
            "message" => $building->id === 0 ? "notset" : "success",
            "building" => $building->id === 0 ? false : $building->to_array(),
        ]);
        return $response;
    }
}



