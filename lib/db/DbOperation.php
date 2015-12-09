<?php

class DbOperation {
    
    private $HOST_NAME;
    private $PORT;
    private $USER;
    private $PASSWORD;
    private $DATABASE;
    private $DRIVER;

    /**
     *
     */
    public function __construct() {
        include_once("config/database.php");
        /** @var array $config */
        $conf = $config["db"];

		$this->DRIVER = strtolower($conf["driver"]);
        $this->HOST_NAME = $conf["host"];
        $this->PORT = $conf["port"];
        $this->USER = $conf["user"];
        $this->PASSWORD = $conf["pwd"];
        $this->DATABASE = $conf["dbn"];

    }

    /**
     * @return false|mysqli|resource
     */
    public function connect()
    {
        try{
            if($this->DRIVER == "sqlsrv")
            {
                $server_name = $this->HOST_NAME .",". $this->PORT;

                $dbection_info = array(
                    "Database"  =>  $this->DATABASE,
                    "UID"       =>  $this->USER,
                    "PWD"       =>  $this->PASSWORD
                );
                $db = sqlsrv_connect($server_name, $dbection_info);

                if( $db === false ) die( print_r( sqlsrv_errors(), true));
                else return $db;
            }else if($this->DRIVER == "mysqli")
            {
                $db = new mysqli($this->HOST_NAME, $this->USER,$this->PASSWORD,$this->DATABASE,$this->PORT);
                if($db->connect_error)
                {
                    die('Connect Error (' . $db->connect_errno . ') '
                        . $db->connect_error);
                }
                return $db;
            }
            else{
                die("Driver not found!");
            }
        }catch (Exception $e){
            var_dump("Error: {$e->getMessage()}");
            return;
        }
    }


    /**
     * @param array $arg
     * @return array
     * @internal param string $table
     */
    public function getDb($arg= [])
    {
        $result = array();
        $db = $this->connect();
        $conditon = "";
        $sql_join = "";

        if(empty($arg["field"])) $arg["field"] = "*";

        if(! empty($arg["where"]))
        {
            foreach($arg["where"] as $k => $v)
            {
                $conditon .= "AND {$k} = '{$v}' ";
            }
        }

        if(! empty($arg["join"]))
        {
            $join = $arg["join"];

            if(! empty($join["left"]))
            {
                $left_join = $join["left"];

                foreach($left_join as $left)
                {
                    if(! empty($left['table'])) $sql_join .= "left join {$left['table']} ";
                    if(! empty($left['on']))
                    {
                        $where = empty($left['where']) ? "" : "AND {$left['where']}";
                        $on = $left["on"];
                        foreach($on as $onk=>$onv)
                        {
                            $sql_join .= "on {$arg['table']}.{$onk} = {$left['table']}.{$onv} $where ";
                        }
                    }
                }
            }
            if(! empty($join["right"]))
            {
                $right_join = $join["right"];
                foreach($right_join as $right)
                {
                    if(! empty($right['table'])) $sql_join .= "right join {$right['table']} ";
                    if(! empty($right['on']))
                    {
                        $where = empty($right['where']) ? "" : "AND {$right['where']}";
                        $on = $right["on"];
                        foreach($on as $onk=>$onv)
                        {
                            $sql_join .= "on {$arg['table']}.{$onk} = {$right['table']}.{$onv} $where ";
                        }
                    }
                }
            }
            if(! empty($join["full"]))
            {
                $full_join = $join["full"];
                foreach($full_join as $full)
                {
                    if(! empty($full['table'])) $sql_join .= "full join {$full['table']} ";
                    if(! empty($full['on']))
                    {
                        $where = empty($full['where']) ? "" : "AND {$full['where']}";
                        $on = $full["on"];
                        foreach($on as $onk=>$onv)
                        {
                            $sql_join .= "on {$arg['table']}.{$onk} = {$full['table']}.{$onv} $where ";
                        }
                    }
                }
            }
            if(! empty($join["inner"]))
            {
                $inner_join = $join["inner"];
                foreach($inner_join as $inner)
                {
                    if(! empty($inner['table'])) $sql_join .= "inner join {$inner['table']} ";
                    if(! empty($inner['on']))
                    {
                        $where = empty($inner['where']) ? "" : "AND {$inner['where']}";
                        $on = $inner["on"];
                        foreach($on as $onk=>$onv)
                        {
                            $sql_join .= "on {$arg['table']}.{$onk} = {$inner['table']}.{$onv} $where ";
                        }
                    }
                }
            }
        }


        if(! empty($arg["other_where"])) $conditon .= "AND {$arg["other_where"]} ";


        if(! empty($arg["group_by"]))
        {
            $group_by = "";
            foreach($arg["group_by"] as $gby)
            {
                $group_by .= empty($group_by) ? "group by {$gby}" : ", {$gby}";
            }
            $conditon .= $group_by;
        }

        $conditon .= empty($arg["having"])? "" : " having {$arg['having']}";

        if(! empty($arg["order_by"]))
        {
            $order_by = $arg['order_by'];
            $short="";
            foreach($order_by as $k => $v){
                $short .= empty($short) ? " order by {$k} {$v}" : ", {$k}  {$v}";
            }
            $conditon .= $short;
        }

        try{
            $sql = "select {$arg['field']} from {$arg['table']} {$sql_join} where 1=1 {$conditon}";

            if($this->DRIVER == "mysqli")
            {
                $query = $db->query($sql);
                if($query)
                {
                    while ($row = $query->fetch_object()){
                        $result[] = $row;
                    }
                }

                mysqli_close($db);

            }else if($this->DRIVER == "sqlsrv")
            {
                $stmt = sqlsrv_query($db,$sql);
                if( $stmt === false ) die( print_r( sqlsrv_errors(), true));
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $result[] = $row;
                }
                sqlsrv_close($db);
            }
            return $result;
        }catch (mysqli_sql_exception $e){
            var_dump("Error: {$e->getMessage()}");
            return;
        }
    }

    /**
     * @param array $arg
     */
    public function insertDb($arg=[])
    {
        if(empty($arg)) return;

        $db = $this->connect();
        $field = "";
        $value = "";

        try{
            if($this->DRIVER == 'mysqli')
            {
                if (!empty($arg["data"]))
                {
                    $data = $arg["data"];
                    foreach ($data as $k => $v) {
                        $field .= empty($field) ? "`{$k}`" : ",`{$k}`";
                        $value .= empty($value) ? "'{$v}'" : ",'{$v}'";
                    }
                }
                $sql = "insert into {$arg['table']}({$field}) values({$value})";
                return $db->query($sql)? $db->insert_id : 0;

            }elseif($this->DRIVER == 'sqlsrv'){
                $var = [];
                if(! empty($arg["data"]))
                {
                    $data = $arg["data"];
                    foreach($data as $k => $v)
                    {
                        $field .= empty($field) ? $k : ",{$k}";
                        $value .= empty($value) ? "?" : ",?";
                        array_push($var, $v);
                    }
                }

                $sql = "insert into {$arg['table']}({$field}) values({$value}); SELECT SCOPE_IDENTITY() as lastId;";
                $stmt = sqlsrv_query($db, $sql, $var);
                if(! $stmt) die(print_r(sqlsrv_errors(),true));
                sqlsrv_next_result($stmt);
                $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC);

                return $result[0];
            }else return "Database driver not found!";
        }catch (mysqli_sql_exception $e){
            die(var_dump($e->getMessage()));
        }finally{
            if($this->DRIVER == "mysqli"){
                mysqli_close($db);
            }else if($this->DRIVER == "sqlsrv"){
                sqlsrv_close($db);
            }
        }

    }
}
