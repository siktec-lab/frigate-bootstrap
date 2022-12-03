<?php 

namespace Siktec\Homevr\Api\Models;

use \Siktec\Frigate\Models\DataModel;
use \Siktec\Frigate\Tools\Input\Sanitize;
use \Siktec\Frigate\Tools\Intl\Currency;

 /**
 * @OA\Schema(
 *   schema="BuildingStatus",
 *   description="Building status annotation",
 *   type="array",
 *   @OA\Items(
 *     type="string",
 *     enum={"available", "sold", "under construction", "reserved", "unknown"}
 *   )
 * )
 * 
 * 
 * @OA\Schema(
 *   schema="Building",
 *   description="Building model contains all the information about a registered building",
 *   type="object",
 *   @OA\Property(property="id",                    type="integer", description="Building ID", example="1"),
 *   @OA\Property(property="ofProject",             type="integer", description="Project ID"),
 *   @OA\Property(property="buildingName",          type="string", description="Building name"),
 *   @OA\Property(property="price",                 type="number", format="float", description="Building price"),
 *   @OA\Property(property="currencyName",          type="string", description="Currency ISO name e.g. EUR"),
 *   @OA\Property(property="currencySym",           type="string", description="Currency symbol e.g. €"),
 *   @OA\Property(property="status",                ref="#/components/schemas/BuildingStatus"),
 *   @OA\Property(property="squareMeters",          type="number", format="float", description="Building square meters"),
 *   @OA\Property(property="gardenSquareMeters",    type="number", format="float", description="Building garden square meters"),
 *   @OA\Property(property="bedrooms",              type="integer", description="Building bedrooms"),
 *   @OA\Property(property="bathrooms",             type="integer", description="Building bathrooms"),
 *   @OA\Property(property="wc",                    type="integer", description="Building wc"),
 *   @OA\Property(property="mapName",               type="string",  description="Building map name"),
 *   @OA\Property(property="picture",               type="integer", description="Building picture ID"),
 *   @OA\Property(property="pictureUrl",            type="string", description="Building picture URL"),
 *   @OA\Property(property="downloadIOS",           type="string", description="Building iOS pak file ID"),
 *   @OA\Property(property="downloadUrliOS",        type="string", description="Building download iOS pak URL"),
 *   @OA\Property(property="downloadAndroid",       type="string", description="Building Android pak file ID"),
 *   @OA\Property(property="downloadUrlAndroid",    type="string", description="Building download Android pak URL"),
 *   example={
 *      "id": 54,
 *      "ofProject": 12,
 *      "buildingName": "Building 1",
 *      "price": 120000,
 *      "currencyName": "EUR",
 *      "currencySym": "€",
 *      "status": "available",
 *      "squareMeters": 100,
 *      "gardenSquareMeters": 100,
 *      "bedrooms": 3,
 *      "bathrooms": 2,
 *      "wc": 1,
 *      "mapName": "map-18",
 *      "picture": 18,
 *      "pictureUrl": "https://localhost/api/file/9b535287-1b05-46e2-9a33-c6b404294235",
 *      "downloadIOS": 21,
 *      "downloadUrliOS": "https://localhost/api/file/jd323jkj3-1b05-46e2-9a33-c6b404294235",
 *      "downloadAndroid": 22,
 *      "downloadUrlAndroid": "https://localhost/api/file/54sdd6fa-1b05-46e2-9a33-c6b404294235"
 *   }
 * )
 */
class Building extends DataModel {

    public int      $id                     = 0;
    public int      $of_project             = 0;
    public string   $name                   = "";
    public float    $price                  = 0.0;
    public string   $currency_name          = "";
    public string   $currency_sym           = "";
    public string   $status                 = "";
    public int      $picture                = 0;
    public string   $picture_name           = "";
    public float    $sqm                    = 0.0;
    public int      $bedrooms               = 0;
    public int      $bathrooms              = 0;
    public int      $wc                     = 0;
    public float    $garden_sqm             = 0.0;
    public string   $map_name               = "";
    public int      $ios_download           = 0;
    public string   $ios_download_name      = "";
    public int      $android_download       = 0;
    public string   $android_download_name  = "";
    public string   $picture_url            = "";
    public string   $ios_url                = "";
    public string   $android_url            = "";

   
    public const STATUSES = [
        "available",
        "sold",
        "reserved",
        "under construction",
        "unknown"
    ];
    /** 
    * @var array[array] 0=> input type 1=> property name 2=> output type 
    */
    protected array $keys = [
        "id"                    => ["integer",  "id",                   "string"],
        "ofProject"             => ["integer",  "of_project",           "string"],
        "buildingName"          => ["string",   "name",                 "string"],
        "price"                 => ["double",   "price",                "string"],
        "currencyName"          => ["string",   "currency_name",        "string"],
        "currencySym"           => ["string",   "currency_sym",         "string"],
        "status"                => ["string",   "status",               "string"],
        "picture"               => ["integer",  "picture",              "string"],
        "pictureName"           => ["string",   "picture_name",         "string"],
        "squareMeters"          => ["double",   "sqm",                  "string"],
        "bedrooms"              => ["integer",  "bedrooms",             "string"],
        "bathrooms"             => ["integer",  "bathrooms",            "string"], 
        "wc"                    => ["integer",  "wc",                   "string"],
        "gardenSquareMeters"    => ["double",   "garden_sqm",           "string"], 
        "mapName"               => ["string",   "map_name",             "string"], 
        "downloadIOS"           => ["integer",  "ios_download",         "string"],  
        "downloadIOSName"       => ["string",   "ios_download_name",    "string"],
        "downloadAndroid"       => ["integer",  "android_download",     "string"],
        "downloadAndroidName"   => ["string",   "android_download_name","string"],
        "pictureUrl"            => ["string",   "picture_url",          "string"],
        "downloadUrlIOS"        => ["string",   "ios_url",              "string"],
        "downloadUrlAndroid"    => ["string",   "android_url",          "string"]
    ];

    public function __construct() {

    }

    public function parse_currency() : void {
        if (!empty($this->currency_name)) {
            $this->currency_sym = Currency::get_symbol($this->currency_name);
        }
    }

    public function normalize() : void {
        $this->of_project       = empty($this->of_project) ? 0 : Sanitize::integer($this->of_project);
        $this->name             = Sanitize::text($this->name);
        $this->price            = Sanitize::float($this->price);
        $this->currency_name    = strtoupper(Sanitize::chars($this->currency_name));
        $this->status           = strtolower(Sanitize::chars($this->status, "a-zA-Z "));
        $this->picture          = empty($this->picture) ? 0 : Sanitize::integer($this->picture);
        $this->sqm              = Sanitize::float($this->sqm);
        $this->bedrooms         = Sanitize::integer($this->bedrooms);
        $this->bathrooms        = Sanitize::integer($this->bathrooms);
        $this->wc               = Sanitize::integer($this->wc);
        $this->garden_sqm       = Sanitize::float($this->garden_sqm);
        $this->map_name         = Sanitize::text($this->map_name);
        $this->ios_download     = empty($this->ios_download) ? 0 : Sanitize::integer($this->ios_download);
        $this->android_download = empty($this->android_download) ? 0 : Sanitize::integer($this->android_download);
    }

    public function validate(array &$errors = []) : bool {
        
        $start = count($errors);
        
        //Id zero or positive:
        if ($this->id < 0) {
            $errors["id"] = "building 'id' should be a positive number or zero";
        }
        
        //Price zero or positive:
        if ($this->price < 0) {
            $errors["price"] = "building 'price' should be a positive number or zero";
        }

        //validate supported currency:
        if (!Currency::has_symbol($this->currency_name)) {
            $errors["currency_name"] = "building currency name [{$this->currency_name}] is not supported by the system";
        }

        //validate status:
        if (!in_array($this->status, self::STATUSES)) {
            $errors["status"] = "building status is not supported by the system";
        }

        //sqm zero or positive:
        if ($this->sqm < 0) {
            $errors["sqm"] = "building 'sqm' should be a positive number or zero";
        }

        //bedrooms zero or positive:
        if ($this->bedrooms < 0) {
            $errors["bedrooms"] = "building 'bedrooms' should be a positive number or zero";
        }

        //bathrooms zero or positive:
        if ($this->bathrooms < 0) {
            $errors["bathrooms"] = "building 'bathrooms' should be a positive number or zero";
        }

        //wc zero or positive:
        if ($this->wc < 0) {
            $errors["wc"] = "building 'wc' should be a positive number or zero";
        }

        //garden_sqm zero or positive:
        return count($errors) === $start;
    }
}