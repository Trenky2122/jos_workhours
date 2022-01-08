<?php
include "models.php";
include "config.php";
class Service{
    private $mysqli = null;
    public function __construct(){
        $this->mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS,
            DB_NAME, DB_PORT);
        if($this->mysqli->connect_errno)
            throw new Exception($this->mysqli->error);
    }

    public function GetAllWorkers(): array{
        $result = $this->mysqli->query("SELECT name, surname, id, password_hash, member_since, is_admin FROM
                                                                     workers");
        $retval = array();
        while ($row = $result->fetch_object("Worker")){
            array_push($retval, $row);
        }
        return $retval;
    }
}
