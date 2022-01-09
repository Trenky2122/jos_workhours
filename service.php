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

    public function GetAllWeeks(){
        $result = $this->mysqli->query("SELECT DISTINCT week FROM work_days ORDER BY week");
        return $result->fetch_array(MYSQLI_NUM);
    }

    public function GetDaysInWeek($year, $week){
        $retval = array();
        $string_date = $week." monday january ".$year;
        $days_of_week = array("Pondelok", "Utorok", "Streda", "Štvrtok", "Piatok", "Sobota", "Nedeľa");
        for($i=0; $i<6; $i+=1){
            $day = new WorkDay();
            $day->day = date("Y-m-d",strtotime($string_date." +".$i." day"));
            $day->week = $week."/".substr($year, 2);
            $day->month = date("m", strtotime($string_date." +".$i." day"))."/".date("y", strtotime($string_date." +".$i." day"));
            $day->day_of_week = $days_of_week[$i];
            array_push($retval, $this->CreateOrGetWorkday($day));
        }
        return $retval;
    }

    public function CreateOrGetWorkday($day){
        $stmt = $this->mysqli->prepare("SELECT day, week, month, day_of_week, id FROM work_days WHERE 
                                                day=?");
        $stmt->bind_param("s", $day->day);
        $stmt->execute();
        $result = $stmt->get_result();
        $dayRes = $result->fetch_object("WorkDay");
        if($dayRes != null){
            return $dayRes;
        }
        else{
            $stmt = $this->mysqli->prepare("INSERT INTO work_days (day, week, month, day_of_week) VALUES (?,?,?,?)");
            $stmt->bind_param("ssss", $day->day, $day->week, $day->month, $day->day_of_week);
            $stmt->execute();
            return $this->CreateOrGetWorkday($day);
        }
    }
}
