<?php 

namespace Siktec\Homevr\Api;

use \Siktec\Frigate\Base;
use \Siktec\Homevr\Api\Models;
use \Siktec\Frigate\Tools\Input\Sanitize;

class ApiMethods {

    public static function getProject(int $id, bool $buildings = false) : ?Models\Project {
        try {
            $get = Base::$db->where("projects.id", $id)
                     ->join("contacts",  "projects.contact = contacts.id","LEFT")
                     ->join("locations", "locations.id = projects.location", "LEFT")
                     ->join("media as proj_picture",     "projects.picture = proj_picture.id", "LEFT")
                     ->join("companies", "projects.company = companies.name", "LEFT")
                     ->join("media as company_picture",     "companies.logo = company_picture.id", "LEFT")
                     ->getOne("projects", implode(", ", [
                        "projects.*",
                        "contacts.phone as contact_phone",
                        "contacts.email as contact_email",
                        "locations.name as location_name",
                        "proj_picture.url as picture_url",
                        "companies.logo as company_logo",
                        "company_picture.url as company_logo_url",
                     ]));
            $project = new Models\Project();
            $project->set(!empty($get) ? $get : []);
            // var_dump(Base::$db->getLastQuery());
            if ($project->id > 0 && $buildings) {
                $buildings = self::getProjectBuildings($project->id);
                $project->buildings_data = array_map(function($building) {
                    return $building->to_array();
                }, $buildings);
            }
            return $project;
        } catch(\Exception $e) {
            return null;
        }
    }

    public static function getProjects(
        string       $f_term        = "", 
        string|array $f_list        = [], 
        string       $f_name        = "", 
        string       $f_company     = "", 
        string|bool  $f_buildings   = false, 
        string|bool  $f_deleted     = false,
        string|int   $f_max         = 0,
        string|int   $f_page        = 1 
    ) : ?array {
        
        //Prepare arguments:
        $f_list         = Sanitize::numbers_list($f_list);
        $f_name         = Sanitize::text($f_name);
        $f_company      = Sanitize::text($f_company);
        $f_term         = Sanitize::text($f_term);
        $f_buildings    = Sanitize::boolean($f_buildings, false);
        $f_deleted      = Sanitize::boolean($f_deleted, false); 
        $f_max          = Sanitize::integer($f_max, 0);
        $f_page         = Sanitize::integer($f_page, 1);
        
        //Limit if none:
        $f_page = $f_page > 0 ? $f_page : 1;
        $f_max = $f_max > 0 ? $f_max : 20;
        
        //Apply filters:
        Base::$db->where("projects.status", $f_deleted ? 0 : 1);
        if (!empty($f_term)) {
            Base::$db->where(
                "(projects.name LIKE ? OR projects.company LIKE ? OR projects.added LIKE ? OR locations.name LIKE ?)",
                ["%$f_term%", "%$f_term%", "%$f_term%", "%$f_term%"]
            );
        } else {
            if (!empty($f_list)) {
                Base::$db->where("projects.id", $f_list, "IN");
            }
            if (!empty($f_name)) {
                Base::$db->where("projects.name", "%".$f_name."%", "LIKE");
            }
            if (!empty($f_company)) {
                Base::$db->where("projects.company", "%".$f_company."%", "LIKE");
            }
        }
        
        //Query:
        try {
            Base::$db->pageLimit = $f_max;
            $get = Base::$db->join("contacts", "projects.contact = contacts.id","LEFT")
                            ->join("locations", "locations.id = projects.location", "LEFT")
                            ->join("media as proj_picture",     "projects.picture = proj_picture.id", "LEFT")
                            ->join("companies", "projects.company = companies.name", "LEFT")
                            ->join("media as company_picture",     "companies.logo = company_picture.id", "LEFT")
                            ->orderBy("projects.id", "DESC")
                            ->paginate("projects", $f_page, implode(", ", [
                                "projects.*",
                                "contacts.phone as contact_phone",
                                "contacts.email as contact_email",
                                "locations.name as location_name",
                                "proj_picture.url as picture_url",
                                "companies.logo as company_logo",
                                "company_picture.url as company_logo_url",
                            ]));

            // var_dump(Base::$db->getLastQuery());
            $projects = [];
            foreach ($get as $key => $prop) {
                $projects[$key] = new Models\Project();
                $projects[$key]->set($prop);
                if ($projects[$key]->id > 0 && $f_buildings) {
                    $buildings = self::getProjectBuildings($projects[$key]->id);
                    $projects[$key]->buildings_data = array_map(function($building) {
                        return $building->to_array();
                    }, $buildings);
                }
            }
            return [$projects, $f_page, Base::$db->totalPages];
        } catch(\Exception $e) {
            return [null, $f_page, 0];
        }
    }

    public static function getProjectMinMax(int $id, string $building_status = "all", bool $update_cache = false) : array {
        try {
            Base::$db->where("of_project", $id);
            Base::$db->groupBy("status");
            if ($building_status !== "all") {
                Base::$db->where("status", $building_status); 
            }
            $get = Base::$db->map("status")->get("buildings", null, implode(", ", [
                "status",
                "COUNT(id) as total",
                "MIN(price) as min_price",
                "MAX(price) as max_price",
                "MIN(sqm) as min_sqm", 
                "MAX(sqm) as max_sqm",
                "MIN(bedrooms) as min_bedrooms",
                "MAX(bedrooms) as max_bedrooms",
                "MIN(bathrooms) as min_bathrooms",
                "MAX(bathrooms) as max_bathrooms",
                "MIN(wc) as min_wc",
                "MAX(wc) as max_wc",
                "MIN(garden_sqm) as min_garden_sqm",
                "MAX(garden_sqm) as max_garden_sqm"
            ]));

            //For total registered buildings 
            $buildings = 0;
            foreach ($get as $key => $prop) {
                $buildings += $prop["total"];
            }

            //Update cache:
            if ($update_cache && $building_status = "all" && !empty($get)) {
                Base::$db->where("id", $id)->update("projects", [
                    "buildings" => $buildings,
                    "minmax"    => json_encode($get)
                ], 1);
            }

            return $get;
        } catch(\Exception $e) {
            return [];
        }
    }


    public static function getBuildings(
        string       $f_term        = "", 
        string|array $f_list        = [], 
        string       $f_project     = "",
        string|int   $f_max         = 0,
        string|int   $f_page        = 1 
    ) : ?array {
        
        //Prepare arguments:
        $f_list         = Sanitize::numbers_list($f_list);
        $f_project      = Sanitize::integer($f_project);
        $f_term         = Sanitize::text($f_term);
        $f_max          = Sanitize::integer($f_max, 0);
        $f_page         = Sanitize::integer($f_page, 1);
        
        //Limit if none:
        $f_page = $f_page > 0 ? $f_page : 1;
        $f_max = $f_max > 0 ? $f_max : 20;
        
        //Apply filters:
        if (!empty($f_project)) {
            Base::$db->where("buildings.of_project", $f_project);
        }
        if (!empty($f_term)) {
            Base::$db->where(
                "(buildings.name LIKE ? OR buildings.status LIKE ?)",
                ["%$f_term%", "%$f_term%"]
            );
        }
        if (!empty($f_list)) {
            Base::$db->where("buildings.id", $f_list, "IN");
        }

        
        //Query:
        try {
            Base::$db->pageLimit = $f_max;
            $get = Base::$db->join("media as pictures", "buildings.picture = pictures.id", "LEFT")
                            ->join("media as ios", "buildings.ios_download = ios.id", "LEFT")
                            ->join("media as android", "buildings.android_download = android.id", "LEFT")
                            ->orderBy("buildings.id", "DESC")
                            ->paginate("buildings", $f_page, implode(", ", [
                                "buildings.*",
                                "pictures.url as picture_url",
                                "pictures.file_name as picture_name",
                                "ios.url as ios_url",
                                "ios.file_name as ios_download_name",
                                "android.url as android_url",
                                "android.file_name as android_download_name"
                            ]));
            // var_dump(Base::$db->getLastQuery());
            $buildings = [];
            foreach ($get as $row) {
                $building = new Models\Building();
                $building->set(!empty($row) ? $row : []);
                $building->parse_currency();
                $buildings[] = $building;
            }

            return [$buildings, $f_page, Base::$db->totalPages];
        } catch(\Exception $e) {
            return [null, $f_page, 0];
        }
    }

    public static function getBuilding(int $id) : ?Models\Building {
        try {
            $get = Base::$db->where("buildings.id", $id)
                     ->join("media as pictures", "buildings.picture = pictures.id", "LEFT")
                     ->join("media as ios", "buildings.ios_download = ios.id", "LEFT")
                     ->join("media as android", "buildings.android_download = android.id", "LEFT")
                     ->getOne("buildings", implode(", ", [
                        "buildings.*",
                        "pictures.url as picture_url",
                        "pictures.file_name as picture_name",
                        "ios.url as ios_url",
                        "ios.file_name as ios_download_name",
                        "android.url as android_url",
                        "android.file_name as android_download_name"
                     ]));
            $building = new Models\Building();
            $building->set(!empty($get) ? $get : []);
            $building->parse_currency();
            // var_dump(Base::$db->getLastQuery());
            return $building;
        } catch(\Exception $e) {
            return null;
        }
    }

    public static function getProjectBuildings(int $id, string $building_status = "all") : ?array {

        Base::$db->where("of_project", $id);
        if ($building_status !== "all") {
            Base::$db->where("status", $building_status); 
        }
        try {
            $get = Base::$db->join("media as pictures", "buildings.picture = pictures.id", "LEFT")
                                    ->join("media as ios", "buildings.ios_download = ios.id", "LEFT")
                                    ->join("media as android", "buildings.android_download = android.id", "LEFT")
                                    ->get("buildings", null, implode(", ", [
                                        "buildings.*",
                                        "pictures.url as picture_url",
                                        "pictures.file_name as picture_name",
                                        "ios.url as ios_url",
                                        "ios.file_name as ios_download_name",
                                        "android.url as android_url",
                                        "android.file_name as android_download_name"
                                    ]));
            $buildings = [];
            foreach ($get as $row) {
                $building = new Models\Building();
                $building->set(!empty($row) ? $row : []);
                $building->parse_currency();
                $buildings[] = $building;
            }
            //var_dump(Base::$db->getLastQuery());
            return $buildings;
        } catch(\Exception $e) {
            return null;
        }
    }

    public static function getLocations(
        string       $f_term        = "", 
        string|array $f_ids         = "",
        string|int   $f_max         = 0,
        string|int   $f_page        = 1 
    ) : ?array {
        
        //Prepare arguments:
        $f_ids          = Sanitize::numbers_list($f_ids);
        $f_term         = Sanitize::text($f_term);
        $f_max          = Sanitize::integer($f_max, 0);
        $f_page         = Sanitize::integer($f_page, 1);
        
        //Limit if none:
        $f_page = $f_page > 0 ? $f_page : 1;
        $f_max = $f_max > 0 ? $f_max : 100;
        //Apply filters:
        if (!empty($f_ids)) {
            Base::$db->where("id", $f_ids, "IN");
        }
        if (!empty($f_term)) {
            Base::$db->where("name", "%".$f_term."%", "LIKE");
        }
        //Query:
        try {
            Base::$db->pageLimit = $f_max;
            $locations = Base::$db->paginate("locations", $f_page);

            // var_dump(Base::$db->getLastQuery());
            return [$locations, $f_page, Base::$db->totalPages];
        } catch(\Exception $e) {
            return [null, $f_page, 0];
        }
    }

    public static function getContacts(
        string       $f_term        = "", 
        string|array $f_ids         = "",
        string|int   $f_max         = 0,
        string|int   $f_page        = 1 
    ) : ?array {
        
        //Prepare arguments:
        $f_ids          = Sanitize::numbers_list($f_ids);
        $f_term         = Sanitize::text($f_term);
        $f_max          = Sanitize::integer($f_max, 0);
        $f_page         = Sanitize::integer($f_page, 1);
        
        //Limit if none:
        $f_page = $f_page > 0 ? $f_page : 1;
        $f_max = $f_max > 0 ? $f_max : 100;
        
        //Apply filters:
        if (!empty($f_ids)) {
            Base::$db->where("id", $f_ids, "IN");
        }
        if (!empty($f_term)) {
            Base::$db->where(
                "(contacts.email LIKE ? OR contacts.phone LIKE ? OR contacts.company LIKE ?)",
                ["%$f_term%", "%$f_term%", "%$f_term%"]
            );
        }
        //Query:
        try {
            Base::$db->pageLimit = $f_max;
            $contacts = Base::$db->paginate("contacts", $f_page);

            // var_dump(Base::$db->getLastQuery());
            return [$contacts, $f_page, Base::$db->totalPages];
        } catch(\Exception $e) {
            return [null, $f_page, 0];
        }
    }

    public static function getCompanies(
        string       $f_term        = "",
        string|bool  $f_exact       = false,
        string|int   $f_max         = 0,
        string|int   $f_page        = 1 
    ) : ?array {
        
        //Prepare arguments:
        $f_term         = Sanitize::text($f_term);
        $f_exact        = Sanitize::boolean($f_exact);
        $f_max          = Sanitize::integer($f_max, 0);
        $f_page         = Sanitize::integer($f_page, 1);
        
        //Limit if none:
        $f_page = $f_page > 0 ? $f_page : 1;
        $f_max = $f_max > 0 ? $f_max : 100;

        //Apply filters:
        if (!empty($f_term)) {
            Base::$db->where(
                "name", 
                $f_exact ? $f_term : "%".$f_term."%", 
                "LIKE"
            );
        }
        //Query:
        try {
            Base::$db->pageLimit = $f_max;
            Base::$db->join("media", "companies.logo = media.id", "LEFT");
            $companies = Base::$db->paginate("companies", $f_page, [
                "companies.*",
                "media.url as logoUrl"
            ]);

            // var_dump(Base::$db->getLastQuery());
            return [$companies, $f_page, Base::$db->totalPages];
        } catch(\Exception $e) {
            return [null, $f_page, 0];
        }
    }
    
    public static function getTotals(string $parts = "all") : ?array {

        $all = [
            "companies"         => null,
            "locations"         => null,
            "contacts"          => null,
            "media"             => null,
            "projects"          => null,
            "buildings"         => null,
            "deletedProjects"   => null,
            "activeProjects"    => null,
            "deletedBuildings"  => null,
            "activeBuildings"   => null
        ];

        //prepare parts
        $parts = Sanitize::tags_list($parts);
        $parts = array_filter($parts, fn($el) => array_key_exists($el, $all) || $el === "all");
        $parts = empty($parts) || in_array("all", $parts) ? array_keys($all) : $parts;
        // var_dump($parts);
        try {
            foreach ($parts as $part) {
                switch ($part) {
                    case "companies" : {
                        $all[$part] = Base::$db->getValue("companies", "count(*)");
                    } break;
                    case "locations" : {
                        $all[$part] = Base::$db->getValue("locations", "count(*)");
                    } break;
                    case "contacts" : {
                        $all[$part] = Base::$db->getValue("contacts", "count(*)");
                    } break;
                    case "media" : {
                        $all[$part] = Base::$db->getValue("media", "count(*)");
                    } break;
                    case "projects" : {
                        $all[$part] = Base::$db->getValue("projects", "count(*)");
                    } break;
                    case "buildings" : {
                        $all[$part] = Base::$db->getValue("buildings", "count(*)");
                    } break;
                    case "deletedProjects" : {
                        $all[$part] = Base::$db->where("status", 0)->getValue("projects", "count(*)");
                    } break;
                    case "activeProjects" : {
                        $all[$part] = Base::$db->where("status", 1)->getValue("projects", "count(*)");
                    } break;
                    case "soldBuildings" : {
                        $all[$part] = Base::$db->where("status", "sold")->getValue("buildings", "count(*)");
                    } break;
                    case "activeBuildings" : {
                        $all[$part] = Base::$db->where("status", "available")->getValue("buildings", "count(*)");
                    } break;
                }
            }
            return Sanitize::filter_empty($all, [0]);
        } catch(\Exception $e) {
            //var_dump($e->getMessage());
            return null;
        }
    }
}