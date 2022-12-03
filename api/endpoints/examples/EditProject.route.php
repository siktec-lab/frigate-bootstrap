<?php 

namespace Siktec\Homevr\Api\Endpoints;

use \Siktec\Frigate\Base;
use \Siktec\Frigate\Api\EndPoint;
use \Siktec\Frigate\Api\EndPointContext;
use \Siktec\Frigate\Routing\Http;
use \Siktec\Frigate\Tools\FileSystem\FilesHelper;
use \Siktec\Frigate\Tools\Input\Sanitize;
use \Siktec\Homevr\Api\ApiMethods;
use \Siktec\Homevr\Api\Models\Project;
use \Siktec\Frigate\Routing\Files;


/**
 * @OA\Patch(
 *   tags={"Projects"},
 *   path="/project",
 *   summary="Edit a project",
 *   description="Edit a project and return the updated project",
 *   security={{"apiAuth":{}}},
 *   @OA\RequestBody(
 *     @OA\JsonContent(
 *       type="object",
 *       required={"id"},
 *       @OA\Property(property="id",                type="integer", description="Project ID to edit - required"),
 *       @OA\Property(property="projectName",       type="string", description="Name of the project"),
 *       @OA\Property(property="company",           type="string",  description="Company Unique Name"),
 *       @OA\Property(property="contact",           type="integer", description="Contact ID or 0 if no saved contact"),
 *       @OA\Property(property="contactPhone",      type="string",  description="Contact Phone Number - If no saved contact"),
 *       @OA\Property(property="contactEmail",      type="string",  description="Contact E-mail - If no saved contact"),
 *       @OA\Property(property="projectLots",       type="integer", description="Number of lots in the project"),
 *       @OA\Property(property="location",          type="integer", description="Location ID or 0 if no saved location"),
 *       @OA\Property(property="locationName",      type="string",  description="Location Name - If no saved location"),
 *       @OA\Property(property="picture",           type="string",  description="Picture Upload key or 0 if no picture")
 *    )
 *  ),
 *   
 *  @OA\Response(
 *    response=200, 
 *    description="OK",
 *    @OA\JsonContent(
 *      type="object",
 *      @OA\Property(property="message", type="string", description="Success message", example="success"),
 *      @OA\Property(property="projects", ref="#/components/schemas/Project")
 *    )
 *  ),
 *  @OA\Response(
 *    response=400, 
 *    description="Invalid Content"
 *  ),
 *  @OA\Response(
 *    response=406, 
 *    description="Validation Errors",
 *    @OA\JsonContent(
 *      type="object",
 *      @OA\Property(property="message", type="string", example="invalid"),
 *      @OA\Property(property="validation", type="array", @OA\Items(type="string"), description="List of validation errors")
 *    )
 *  ),
 *  @OA\Response(response=401, ref="#/components/responses/401"),
 *  @OA\Response(response=500, ref="#/components/responses/500")
 * )
 */

class EditProjectEndPoint extends EndPoint { 

    use EndPointContext;

    public function __construct(bool $debug = false, bool $auth = false, string $auth_method = "basic") {
        
        parent::__construct($debug, $auth, $auth_method);

    }

    public function proccessFiles(array $files, string $temp, string $storage) : int {
        
        //Proccess files:
        [$key, $name] = explode(':', $files[0], 2);
        $saved_file = Files\Upload::save_file_key($key, $name, $temp, $storage);

        //Save image to db:
        if ($saved_file["success"]) {
            $url = defined("APP_BASE_URL") ? APP_BASE_URL : "";
            $url = rtrim($url, " \t\n\r/\\")."/file/".trim($key);
            Base::$db->insert("media", [
                "type"      => $saved_file["mime_type"],
                "url"       => $url,
                "path"      => $saved_file["file_path"],
                "file_key"  => trim($key),
                "file_name" => $saved_file["filename"],
                "file_size" => $saved_file["file_size"]
            ]);
            return Base::$db->getInsertId();
        }
        return 0;

    }

    private function remove_file($id) {
        $file = Base::$db->where("id", $id)->getOne("media");
        if ($file) {
            $file_path = $file["path"];
            $delete = Base::$db->where("id", $id)->delete("media");
            if ($delete) {
                FilesHelper::delete_files($file_path);
            }
        }
    }

    public function call(array $context, Http\RouteRequest $request) : Http\Response {

        Base::debug($this, "Execute endpoint - EditProject\n".$request);
        
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
        $data = $request->getPatchData();
        if (empty($data)) {
            throw new \Exception("Invalid Content", 400);
        }

        //If id is set, get old project:
        $data["id"] = intval($data["id"] ?? 0);
        $oldProject = ApiMethods::getProject($data["id"], false);
        if (is_null($oldProject) || $oldProject->id === 0) {
            $response->setStatus(406);
            $response->setBodyJson([
                "message"       => "invalid",
                "validation"    => [ "id" => "Invalid project id" ]
            ]);
            return $response;
        }

        //Create Project Model
        $new_proj = new Project();
        $new_proj->set($oldProject->get());
        $new_proj->load($data);
        $new_proj->normalize();

        //Validate:
        $validation_messages = [];
        $valid_input = $new_proj->validate($validation_messages);
        if (!$valid_input) {
            $response->setStatus(406);
            $response->setBodyJson([
                "message"       => "invalid",
                "validation"    => $validation_messages
            ]);
            return $response;
        }

        //Validate duplicates and correct selections:
        if ($oldProject->contact !== $new_proj->contact && $new_proj->contact > 0 && !Base::$db->where("id", $new_proj->contact)->has("contacts")) {
            $validation_messages["contact"] = "contact does not exist please create one";
        }
        if ($oldProject->name !== $new_proj->name && Base::$db->where("name", $new_proj->name)->has("projects")) {
            $validation_messages["name"] = "project name allready exists - it should be unique";
        }
        if ($oldProject->location !== $new_proj->location && $new_proj->location > 0 && !Base::$db->where("id", $new_proj->location)->has("locations")) {
            $validation_messages["location"] = "location does not exist please create one";
        }
        if ($oldProject->picture !== $new_proj->picture && $new_proj->picture > 0 && !Base::$db->where("id", $new_proj->picture)->has("media")) {
            $validation_messages["picture"] = "picture does not exist please upload it again";
        }
        if (!empty($validation_messages)) {
            $response->setStatus(406);
            $response->setBodyJson([
                "message"       => "invalid",
                "validation"    => $validation_messages
            ]);
            return $response;
        }

        //Proccess file if needed:
        $temp = $this->get_context("files_temp", $context, false);
        $storage = $this->get_context("files_storage", $context, false);
        if (array_key_exists("projectFiles", $data) && is_array($data["projectFiles"]) && !empty($data["projectFiles"])) {
            $new_proj->picture = $this->proccessFiles($data["projectFiles"], $temp, $storage);
        }

        //Proccess Company logo if needed:
        $set_new_company_logo = 0;    
        if (array_key_exists("companyFiles", $data) && is_array($data["companyFiles"]) && !empty($data["companyFiles"])) {
            $set_new_company_logo = $this->proccessFiles($data["companyFiles"], $temp, $storage);
        }

        //create:
        $success = true;
        $new_company = !Base::$db->where("name", $new_proj->company)->has("companies");
        Base::$db->startTransaction();

        //Company update or create:
        if ($new_company && $set_new_company_logo > 0) {
            $success = Base::$db->insert("companies", [
                "name" => $new_proj->company,
                "logo" => $set_new_company_logo ?: null
            ]);
            //print Base::$db->getLastQuery();
        } elseif ($new_company && $set_new_company_logo === 0) {
            $success = Base::$db->insert("companies", [
                "name" => $new_proj->company
            ]);
            //print Base::$db->getLastQuery();
        } elseif (!$new_company && $set_new_company_logo > 0) {
            $success = Base::$db->where("name", $new_proj->company)->update("companies", [
                "logo" => $set_new_company_logo
            ]);
            //print Base::$db->getLastQuery();
        }

        //Contact create new if needed:
        if ($success && $new_proj->contact === 0) {
            $success = Base::$db->insert("contacts", [
                "email" => $new_proj->contact_email, 
                "phone" => $new_proj->contact_phone,
                "company" => $new_proj->company
            ]);
            // print Base::$db->getLastQuery();
            $new_proj->contact = Base::$db->getInsertId();
        }
        if ($success && $new_proj->location === 0) {
            $success = Base::$db->insert("locations", ["name" => $new_proj->location_name]);
            $new_proj->location = Base::$db->getInsertId();
        }
        if ($success) {
            $success = Base::$db->where("id", $new_proj->id)->update("projects", [
                "company"    => $new_proj->company,
                "contact"    => $new_proj->contact ?: null,
                "name"       => $new_proj->name,
                "lots"       => $new_proj->lots,
                "location"   => $new_proj->location ?: null,
                "picture"    => $new_proj->picture ?: null,
                "updated"    => Base::$db->now(),
                "updated_by" => Sanitize::email($user),
                "status"     => $new_proj->status
            ], 1);
            // print Base::$db->getLastQuery();
        }

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
        
        //remove old files:
        if ($new_proj->picture !== $oldProject->picture && $oldProject->picture > 0) {
            $this->remove_file($oldProject->picture);
        }

        //remove old company logo if needed:
        if ($set_new_company_logo > 0 && $oldProject->company_logo > 0) {
            $this->remove_file($oldProject->company_logo);
        }

        //Final Response:
        $project = ApiMethods::getProject($new_proj->id);
        $response->setBodyJson([
            "message"       => "success",
            "project"       => $project->to_array()
        ]);
        
        return $response;
    }

}