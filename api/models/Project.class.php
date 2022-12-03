<?php 

namespace Siktec\Homevr\Api\Models;

use \Siktec\Frigate\Models\DataModel;
use \Siktec\Frigate\Tools\Input\Sanitize;

/**
 * @OA\Schema(
 *   schema="minMax",
 *   description="Project min max data annotation",
 *   type="object",
 *   @OA\Property(property="status", type="string", description="Status group", example="available"),
 *   @OA\Property(property="total", type="integer", description="Total number of buildings", example="2"),
 *   @OA\Property(property="min_price", type="number", format="float", description="Minimum price", example="42.3"),
 *   @OA\Property(property="max_price", type="number", format="float", description="Maximum price", example="61.98"),
 *   @OA\Property(property="min_sqm", type="number", format="float", description="Minimum square meters", example="81.8"),
 *   @OA\Property(property="max_sqm", type="number", format="float", description="Maximum square meters", example="104"),
 *   @OA\Property(property="min_bedrooms", type="integer", description="Minimum bedrooms", example="3"),
 *   @OA\Property(property="max_bedrooms", type="integer", description="Maximum bedrooms", example="5"),
 *   @OA\Property(property="min_bathrooms", type="integer", description="Minimum bathrooms", example="1"),
 *   @OA\Property(property="max_bathrooms", type="integer", description="Maximum bathrooms", example="2"),
 *   @OA\Property(property="min_wc", type="integer", description="Minimum wc", example="1"),
 *   @OA\Property(property="max_wc", type="integer", description="Maximum wc", example="2"),
 *   @OA\Property(property="min_garden_sqm", type="number", format="float", description="Minimum garden square meters", example="21.8"),
 *   @OA\Property(property="max_garden_sqm", type="number", format="float", description="Maximum garden square meters", example="240"),
 *   example={
 *   "status": "available",
 *   "total": 2,
 *   "min_price": 42.3,
 *   "max_price": 61.98,
 *   "min_sqm": 81.8,
 *   "max_sqm": 104,
 *   "min_bedrooms": 3,
 *   "max_bedrooms": 5,
 *   "min_bathrooms": 1,
 *   "max_bathrooms": 2,
 *   "min_wc": 1,
 *   "max_wc": 2,
 *   "min_garden_sqm": 21.8,
 *   "max_garden_sqm": 240
 *   }
 * )
 * 
 * 
 * @OA\Schema(
 *   schema="Project",
 *   description="Project model contains all the information about a registered project",
 *   type="object",
 *   @OA\Property(property="id",                    type="integer", description="Project ID", example="1"),
 *   @OA\Property(property="projectName",           type="string", description="Project name"),
 *   @OA\Property(property="company",               type="string", description="Project company"),
 *   @OA\Property(property="contact",               type="integer", description="Project contact ID"),
 *   @OA\Property(property="contactPhone",          type="string", description="Project contact phone"),
 *   @OA\Property(property="contactEmail",          type="string", description="Project contact email"),
 *   @OA\Property(property="projectLots",           type="integer", description="Project lots"),
 *   @OA\Property(property="location",              type="integer", description="Project location ID"),
 *   @OA\Property(property="locationName",          type="string", description="Project location name"),
 *   @OA\Property(property="picture",               type="string", description="Project picture id"),
 *   @OA\Property(property="pictureUrl",            type="string", description="Project picture url"),
 *   @OA\Property(property="buildingsTot",          type="integer", description="Project buildings total"),
 *   @OA\Property(property="added",                 type="string", description="Project added date", example="2019-01-01 00:00:00"),
 *   @OA\Property(property="addedBy",               type="string", description="Project added by user name"),
 *   @OA\Property(property="updated",               type="string", description="Project updated date", example="2019-01-01 00:00:00"),
 *   @OA\Property(property="updatedBy",             type="string", description="Project updated by user name"),
 *   @OA\Property(property="status",                type="integer", description="Project status - 0 deleted, 1 active", example="1"),
 *   @OA\Property(
 *     property="buildingsData",         
 *     type="array", 
 *     description="Project buildings data",
 *     @OA\Items(ref="#/components/schemas/Building")
 *   ),
 *   @OA\Property(
 *     property="minMax",
 *     type="object",
 *     @OA\Property(property="available", ref="#/components/schemas/minMax"),
 *     @OA\Property(property="sold", ref="#/components/schemas/minMax"),
 *     @OA\Property(property="reserved", ref="#/components/schemas/minMax"),
 *     @OA\Property(property="under construction", ref="#/components/schemas/minMax"),
 *     @OA\Property(property="unknown", ref="#/components/schemas/minMax")
 *   ),
 *   example={
 *      "id": "4",
 *      "company": "Test Company",
 *      "contact": "8",
 *      "contactPhone": "0509851408",
 *      "contactEmail": "shlomohassid@gmail.com",
 *      "projectName": "Massive Project3",
 *      "projectLots": "5",
 *      "location": "3",
 *      "locationName": "Haifa, Israel",
 *      "picture": "0",
 *      "pictureUrl": "",
 *      "companyLogo": "4",
 *      "companyLogoUrl": "http://localhost/api/v1/files/c3bce67c-9002-43d7-b7b9-3b401def4063",
 *      "buildingsTot": "2",
 *      "added": "2022-09-03 12:58:22",
 *      "addedBy": "shlomi",
 *      "updated": "2022-09-03 12:58:22",
 *      "updatedBy": "shlomi",
 *      "status": "1",
 *      "buildingsData": {
 *        {
 *          "id": "18",
 *          "ofProject": "4",
 *          "buildingName": "TRE-145254",
 *          "price": "61.98",
 *          "currencyName": "EUR",
 *          "currencySym": "\u20ac",
 *          "status": "available",
 *          "picture": "0",
 *          "squareMeters": "104",
 *          "bedrooms": "5",
 *          "bathrooms": "2",
 *          "wc": "2",
 *          "gardenSquareMeters": "240",
 *          "mapName": "map-18",
 *          "downloadIOS": "0",
 *          "downloadAndroid": "0",
 *          "pictureUrl": "",
 *          "downloadUrliOS": "",
 *          "downloadUrlAndroid": ""
 *        }
 *      },
 *      "minMax": {
 *        "available": {
 *          "status": "available",
 *          "total": 2,
 *          "min_price": 42.3,
 *          "max_price": 61.98,
 *          "min_sqm": 81.8,
 *          "max_sqm": 104,
 *          "min_bedrooms": 3,
 *          "max_bedrooms": 5,
 *          "min_bathrooms": 1,
 *          "max_bathrooms": 2,
 *          "min_wc": 1,
 *          "max_wc": 2,
 *          "min_garden_sqm": 21.8,
 *          "max_garden_sqm": 240
 *        }
 *      }
 *    }   
 * )
 */
class Project extends DataModel {

    public int      $id             = 0;
    public string   $company        = "";
    public int      $contact        = 0;
    public string   $contact_phone  = "";
    public string   $contact_email  = "";
    public string   $name           = "";
    public int      $lots           = 0;
    public int      $location       = 0;
    public string   $location_name  = "";
    public int      $picture        = 0;
    public string   $picture_url    = "";

    public int      $company_logo     = 0;
    public string   $company_logo_url = "";

    public int      $buildings      = 0;
    public string   $added          = "";
    public string   $added_by       = "";
    public string   $updated        = "";
    public string   $updated_by     = "";
    public int      $status         = 1;
    public array    $buildings_data = [];
    public array    $minmax         = [];

    /** 
    * @var array[array] 0=> input type 1=> property name 2=> output type 
    */
    protected array $keys = [
        "id"                    => ["integer",  "id",               "string"],
        "company"               => ["string",   "company",          "string"],
        "contact"               => ["integer",  "contact",          "string"],
        "contactPhone"          => ["string",   "contact_phone",    "string"],
        "contactEmail"          => ["string",   "contact_email",    "string"],
        "projectName"           => ["string",   "name",             "string"],
        "projectLots"           => ["integer",  "lots",             "string"],
        "location"              => ["integer",  "location",         "string"],
        "locationName"          => ["string",   "location_name",    "string"],
        "picture"               => ["integer",  "picture",          "string"],
        "pictureUrl"            => ["string",   "picture_url",      "string"],

        "companyLogo"           => ["integer",  "company_logo",     "string"],
        "companyLogoUrl"        => ["string",   "company_logo_url", "string"],

        "buildingsTot"          => ["integer",  "buildings",        "string"],
        "added"                 => ["string",   "added",            "string"], 
        "addedBy"               => ["string",   "added_by",         "string"],
        "updated"               => ["string",   "updated",          "string"], 
        "updatedBy"             => ["string",   "updated_by",       "string"],
        "status"                => ["integer",  "status",           "string"],
        "buildingsData"         => ["array",    "buildings_data",   "array"],
        "minMax"                => ["array",    "minmax",           "array"]
    ];

    public function __construct() {
        
    }

    public function normalize() : void {
        $this->company       = Sanitize::text($this->company);
        $this->name          = Sanitize::text($this->name);
        $this->contact_phone = Sanitize::phone($this->contact_phone);
        $this->contact_email = Sanitize::email($this->contact_email);
        $this->location_name = Sanitize::text($this->location_name);
    }

    public function validate(array &$errors = []) : bool {
        $start = count($errors);
        //Id zero or positive:
        if ($this->id < 0) {
            $errors["id"] = "project 'id' should be a positive number or zero";
        }
        //Company not empty:
        if (empty($this->company)) {
            $errors["company"] = "project 'company' is required";
        }
        //name not empty:
        if (empty($this->name)) {
            $errors["company"] = "project 'company' is required";
        }
        //contact zero or positive:
        if ($this->contact < 0) {
            $errors["contact"] = "project 'contact' should be a positive number or zero";
        }
        //contact zero then phone or email not empty:
        if ($this->contact === 0 && empty($this->contact_phone) && empty($this->contact_email)) {
            $errors["contactPhone"] = "project 'contactPhone' is required if no 'contact' is selected";
            $errors["contactEmail"] = "project 'contactEmail' is required if no 'contact' is selected";
        }
        //lots 0 or positive:
        if ($this->lots < 0) {
            $errors["projectLots"] = "project 'projectLots' should be a positive number or zero";
        }
        //location zero or positive:
        if ($this->location < 0) {
            $errors["location"] = "project 'location' should be a positive number or zero";
        }
        //location zero then locationName not empty:
        if ($this->location === 0 && empty($this->location_name)) {
            $errors["locationName"] = "project 'locationName' is required if no 'location' is selected";
        }
        //picture zero then locationName not empty:
        if ($this->picture < 0) {
            $errors["picture"] = "project 'picture' should be a positive number or zero";
        }

        return count($errors) === $start;
    }
}