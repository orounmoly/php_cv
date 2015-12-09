<?php     
    require "vendor/autoload.php";

    function run(){
        $lib = new library();
        $arg = "";
        $function = "index";
        $controller = $lib->route;

        for($i = 0; $i < count($lib->get_all_segment()); $i++)
        {
            if($i == 2) $controller = empty($lib->get_segment($i)) ? $controller : $lib->get_segment($i);
            else if($i == 3) $function = empty($lib->get_segment($i)) ? "index" : $lib->get_segment($i);
            else if($i >= 4) $arg .= empty($arg) ?  $lib->get_segment($i) : ",". $lib->get_segment($i) ;
        }

        include "app/class/".$controller.".php";
        $run = new $controller();
        return empty($arg) ? $run->$function() : $run->$function($arg);
    }
    

