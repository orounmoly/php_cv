<?php

class library {
    
    public $route;

    public function __construct() {
        require "config/route.php";
        $this->route = $route["default_controller"];
    }
    
    public function get_segment($seg)
    {
        $uri_segment = explode("/", parse_url(trim($_SERVER["REQUEST_URI"],"/"), PHP_URL_PATH));
        return $uri_segment[$seg];
    }
    public function get_all_segment()
    {
        return explode("/",parse_url(trim($_SERVER["REQUEST_URI"],"/"), PHP_URL_PATH));
    }

    public function load_page($page, $data=array())
    {
        /*
        $title = $data["title"];
        $menu = $data["menu"]; */

        foreach($data as $k=>$v){
            ${$k} = $v;
        }
        include_once "app/page/".$page.".php";
        return;
    }
    
    public function base_url()
    {
        $project_root = explode("/", trim($_SERVER['PHP_SELF'],'/'));
        return trim(strtolower($_SERVER['REQUEST_SCHEME']). "://" . $_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/".$project_root[0],"/")."/";
    }

    public function site_url($part="")
    {
        $project_root = explode("/", trim($_SERVER['PHP_SELF'],'/'));
        return trim(strtolower($_SERVER['REQUEST_SCHEME'])."://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/".$project_root[0]."/".$project_root[1],"/")."/".$part;
    }

}
