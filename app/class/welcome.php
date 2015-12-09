<?php

/**
 * Class welcome
 */
class welcome{
    private $lib;
    private $dbo;

    public function __construct() {
        $this->lib = new library();
        $this->dbo = new DbOperation();
    }
    
    public function index()
    {
        return $this->insert_data();
    }

    /**
     *
     */
    public function get_data()
    {
        $config = [
            "table" =>  "sys_menu",
            "field" =>  "id,count(code),name",
            "where" =>  ["status"=>1],
            "other_where" => "id in (2,101,5)",
            "join"  => [
                "left" => [
                    [
                        "table" => "table_join",
                        "on"    =>  ["fid" => "id"],
                        "where" => "id > 7"
                    ],
                    [
                        "table" => "table_join_2",
                        "on"    => ["fid" => "id"]
                    ]
                ],
                "right" => [
                    [
                        "table" => "table_join_right",
                        "on" => ["fid" => "id"]
                    ],
                    [
                        "table" => "table_join_right_2",
                        "on"    => ["fid" => "id"]
                    ]
                ]
            ],
            "order_by" => [
                "field 1" => "desc",
                "field 3" => "asc"
            ],
            "group_by" => ["name","sex"],
            "having"  =>  "count(code)>10"
        ];
        return $this->dbo->getDb($config);
    }

    public function insert_data()
    {
        $config = [
            "table" => "sys_menu",
            "data"  => [
                "parent_id" => "4",
                "link" => "account/account/chat_account",
                "title" => "Chat Account",
                "status"=> "1",
                "icon" => ""
            ]
        ];
        $result = $this->dbo->insertDb($config);
        if($result > 0)
        {
            return $result;
        }
    }
    
}



