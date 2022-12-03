<?php

namespace Siktec\App\Admin\Pages;

use \Siktec\Frigate\Pages\Page as PageBuilder;
use \Siktec\App\Admin\Pages\SideNavigationTrait;

class Dashboard extends PageBuilder {

    use SideNavigationTrait;

    public string $base;
    public string $lib;
    public string $vendor;

    /**
     * "default"            -> main tab render:
     */
    public string $state = "";

    public function __construct(string $state = "default")
    {
        parent::__construct(APP_BASE_URL);

        //Tokenize:
        $this->tokenize(reuse : false);

        //Templates and paths:
        $this->use_templates(__DIR__.DS."templates");
        $this->base         = APP_BASE_URL;
        $this->lib          = APP_BASE_URL."pages/lib/";
        $this->vendor       = APP_BASE_URL."vendor/";
        $this->components   = APP_BASE_URL."vendor/components/";

        //Set meta:
        $this->meta->title          = "Admin Dashboard";
        $this->meta->description    = "App Admin Dashboard";
        $this->meta->viewport       = "width=device-width, initial-scale=1.0,  shrink-to-fit=no";

        //Favicon:
        $this->meta->favicon_png32 = $this->lib."img/favicon.png";

        //Sources:
        $this->sources->head->include_links("https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700");
        $this->sources->head->include_links("https://fonts.googleapis.com/icon?family=Material+Icons", crossorigin : "anonymous");
        $this->sources->head->include_links($this->vendor."twbs/bootstrap/dist/css/bootstrap.min.css");
        $this->sources->head->include_links("https://kit.fontawesome.com/42d5adcbca.js", crossorigin : "anonymous");
        $this->sources->head->include_links($this->lib."css/material-dashboard.css?ver=".APP_VERSION, id : "pagestyle");
        $this->sources->head->include_links($this->lib."css/main.css?ver=".APP_VERSION, id : "pagestyle");
        $this->sources->head->include_links($this->lib."css/dashboard-style.css?ver=".APP_VERSION, id : "pagestyle");
        
        $this->sources->body->include_script($this->vendor."components/jquery/jquery.min.js");
        $this->sources->body->include_script($this->vendor."twbs/bootstrap/dist/js/bootstrap.bundle.js");

        $this->sources->body->include_script($this->lib."js/plugins/perfect-scrollbar.min.js");
        // $this->sources->body->include_script($this->lib."js/plugins/smooth-scrollbar.min.js");
        // $this->sources->body->include_script($this->lib."js/plugins/chartjs.min.js");
                
        $this->sources->body->include_script($this->lib."js/material-dashboard.js?ver=".APP_VERSION);
        $this->sources->body->include_script($this->lib."js/core/app.js?ver=".APP_VERSION);
        $this->sources->body->include_script($this->lib."js/dashboard.js?ver=".APP_VERSION);

        //State : 
        $this->state = $state;

    }

    

    private function stats_cards() : array {

        return [
            "activeProjects"  => [ "color" => "dark", "icon" => "apartment", "value" => "0", "title"          => "Active Projects"  ],
            "deletedProjects" => [ "color" => "danger", "icon" => "domain_disabled", "value" => "0", "title"   => "Deleted Projects" ],
            "buildings"       => [ "color" => "success", "icon" => "location_city", "value" => "0", "title"   => "Registered Buildings" ],
            "companies"       => [ "color" => "primary", "icon" => "business_center", "value" => "0", "title" => "Registered Companies" ],
            "contacts"        => [ "color" => "secondary", "icon" => "contacts", "value" => "0", "title"     => "Registered Contacts"   ],
            "locations"       => [ "color" => "info", "icon" => "map", "value" => "0", "title"      => "Registered Locations"    ],
            "media"           => [ "color" => "warning", "icon" => "perm_media", "value" => "0", "title"    => "Stored Media Files"     ]
        ];
    }
    public function compile(): string
    {

        $context = [
            "meta"      => $this->meta->to_array(),
            "opengraph" => $this->opengraph->to_array(),
            "sources"   => $this->sources->to_array(),
            "state"     => $this->state,
            "sidenav"   => $this->side_navigation($this->base, "dashboard"),
            "topnav"    => [
                "breadcrumb" => [ "path" => "Pages", "active" => "Dashboard" ],
                "title"      => "Dashboard"
            ],
            "stat_cards" => $this->stats_cards(),
        ];
        return $this->templating->render("dashboard.twig", $context);
    }
}


