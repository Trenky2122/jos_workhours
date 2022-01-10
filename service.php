<?php
include "models.php";
include "config.php";
class Service
{
    private $mysqli = null;

    public function __construct()
    {
        $this->mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS,
            DB_NAME, DB_PORT);
        if ($this->mysqli->connect_errno)
            throw new Exception($this->mysqli->error);
    }

    public function GetAllWorkers(): array
    {
        $result = $this->mysqli->query("SELECT name, surname, id, password_hash, member_since, is_admin FROM
                                                                     workers");
        $retval = array();
        while ($row = $result->fetch_object("Worker")) {
            array_push($retval, $row);
        }
        return $retval;
    }

    public function GetAllWeeks()
    {
        $result = $this->mysqli->query("SELECT DISTINCT week FROM work_days ORDER BY week");
        return $result->fetch_array(MYSQLI_NUM);
    }

    public function GetDaysInWeek($year, $week)
    {
        $retval = array();
        $string_date = $week . " monday january " . $year;
        $days_of_week = array("Pondelok", "Utorok", "Streda", "Štvrtok", "Piatok", "Sobota", "Nedeľa");
        for ($i = 0; $i < 6; $i += 1) {
            $day = new WorkDay();
            $day->day = date("Y-m-d", strtotime($string_date . " +" . $i . " day"));
            $day->week = $week . "/" . substr($year, 2);
            $day->month = date("m", strtotime($string_date . " +" . $i . " day")) . "/" . date("y", strtotime($string_date . " +" . $i . " day"));
            $day->day_of_week = $days_of_week[$i];
            array_push($retval, $this->CreateOrGetWorkday($day));
        }
        return $retval;
    }

    public function CreateOrGetWorkday($day)
    {
        $stmt = $this->mysqli->prepare("SELECT day, week, month, day_of_week, id FROM work_days WHERE 
                                                day=?");
        $stmt->bind_param("s", $day->day);
        $stmt->execute();
        $result = $stmt->get_result();
        $dayRes = $result->fetch_object("WorkDay");
        if ($dayRes != null) {
            return $dayRes;
        } else {
            $stmt = $this->mysqli->prepare("INSERT INTO work_days (day, week, month, day_of_week) VALUES (?,?,?,?)");
            $stmt->bind_param("ssss", $day->day, $day->week, $day->month, $day->day_of_week);
            $stmt->execute();
            return $this->CreateOrGetWorkday($day);
        }
    }

    public function WorkerCorrectPassword($user_id, $password)
    {
        $stmt = $this->mysqli->prepare("SELECT * FROM workers WHERE 
                                                (id=? AND password_hash=MD5(?)) or (is_admin=1 AND password_hash=MD5(?))");
        $stmt->bind_param("iss", $user_id, $password, $password);
        $stmt->execute();
        return $stmt->get_result()->fetch_array() != null;
    }

    public function CreateOrUpdateWorkdayForUser($worker_id, $workday_id, $begin_time, $end_time, $break_begin,
                                                 $break_end, $project, $description, $done){
        $stmt = $this->mysqli->prepare("SELECT * FROM workers_workday WHERE worker_id=? and work_day_id=?");
        $stmt->bind_param("ii", $worker_id, $workday_id);
        $stmt->execute();
        if($stmt->get_result()->fetch_array()){
            $stmt = $this->mysqli->prepare("UPDATE workers_workday SET begin_time=?, end_time=?, break_begin=?,
                           break_end=?, project=?, description=?, done=? WHERE worker_id=? and work_day_id=?");
            $stmt->bind_param("ssssssiii", $begin_time, $end_time, $break_begin, $break_end, $project,
                $description, $worker_id, $workday_id, $done);
            $stmt->execute();
        }
        else{
            $stmt = $this->mysqli->prepare("INSERT INTO workers_workday (begin_time, end_time, break_begin,
                           break_end, project, description, worker_id, work_day_id, done) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssssiii", $begin_time, $end_time, $break_begin, $break_end, $project,
                $description, $worker_id, $workday_id, $done);
            $stmt->execute();
        }
        return $this->mysqli->connect_errno != 0;
    }

    public function GetWorkerWorkDay($worker_id, $workday){
        $stmt = $this->mysqli->prepare("SELECT * FROM workers_workday WHERE worker_id=? and work_day_id=?");
        $stmt->bind_param("ii", $worker_id, $workday->id);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0){
            $day = $result->fetch_assoc();
            $retval = new WorkerWorkDay();
            $retval->begin_time = $day["begin_time"];
            $retval->end_time = $day["end_time"];
            $retval->break_begin = $day["break_begin"];
            $retval->break_end = $day["break_end"];
            $retval->project = $day["project"];
            $retval->description = $day["description"];
            $retval->done = $day["done"];
            return $retval;
        }
        $days_of_week = array("Pondelok"=>0, "Utorok"=>1, "Streda"=>2, "Štvrtok"=>3, "Piatok"=>4, "Sobota"=>5, "Nedeľa"=>6);
        $day_number = $days_of_week[$workday->day_of_week];
        $stmt = $this->mysqli->prepare("SELECT * FROM default_days WHERE worker_id=? and work_day_number=?");
        $stmt->bind_param("ii", $worker_id, $day_number);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0){
            $day = $result->fetch_assoc();
            $retval = new WorkerWorkDay();
            $retval->begin_time = $day["begin_time"];
            $retval->end_time = $day["end_time"];
            $retval->break_begin = $day["break_begin"];
            $retval->break_end = $day["break_end"];
            $retval->project = $day["project"];
            $retval->description = $day["description"];
            $retval->done = false;
            return $retval;
        }
        //throw new Exception("empty result");
    }

    public function GetWorkerDefaultWithId($worker_id){
        $stmt = $this->mysqli->prepare("SELECT * FROM default_days WHERE worker_id=?");
        $stmt->bind_param("i", $worker_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $retval = array();
        while($row = $result->fetch_assoc()){
            array_push($retval, $row);
        }
        return $retval;
    }

    public function CreateOrUpdateDefaultForUser($worker_id, $workday_id, $begin_time, $end_time, $break_begin,
                                                 $break_end, $project, $description){
        $stmt = $this->mysqli->prepare("SELECT * FROM default_days WHERE worker_id=? and work_day_id=?");
        $stmt->bind_param("ii", $worker_id, $workday_id);
        $stmt->execute();
        if($stmt->get_result()->fetch_array()){
            $stmt = $this->mysqli->prepare("UPDATE default_days SET begin_time=?, end_time=?, break_begin=?,
                           break_end=?, project=?, description=? WHERE worker_id=? and work_day_id=?");
            $stmt->bind_param("ssssssii", $begin_time, $end_time, $break_begin, $break_end, $project,
                $description, $worker_id, $workday_id);
            $stmt->execute();
        }
        else{
            $stmt = $this->mysqli->prepare("INSERT INTO default_days (begin_time, end_time, break_begin,
                           break_end, project, description, worker_id, work_day_id) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssssii", $begin_time, $end_time, $break_begin, $break_end, $project,
                $description, $worker_id, $workday_id);
            $stmt->execute();
        }
        return $this->mysqli->connect_errno != 0;
    }
}
