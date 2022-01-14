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

    /**
     * @return Worker[]
     */
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

    public function GetDaysInWeek($year, $week): array
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
            //array_push($retval, $this->CreateOrGetWorkday($day));
            array_push($retval, $day);
        }
        return $retval;
    }

    public function WorkerCorrectPassword($user_id, $password): bool
    {
        $stmt = $this->mysqli->prepare("SELECT * FROM workers WHERE 
                                                (id=? AND password_hash=MD5(?)) or (is_admin=1 AND password_hash=MD5(?))");
        $stmt->bind_param("iss", $user_id, $password, $password);
        $stmt->execute();
        return $stmt->get_result()->fetch_array() != null;
    }

    public function CreateOrUpdateWorkdayForUser($worker_id, $workday_date, $begin_time, $end_time, $break_begin,
                                                 $break_end, $description, $done, $projects): bool
    {
        if($break_begin == "") $break_begin = null;
        if($break_end == "") $break_end = null;
        $worker_workday_id = 0;
        $stmt = $this->mysqli->prepare("SELECT id FROM workers_workday WHERE worker_id=? and work_day_date=?");
        $stmt->bind_param("is", $worker_id, $workday_date);
        $stmt->execute();
        if($worker_workday_id = $stmt->get_result()->fetch_field()){
            $stmt = $this->mysqli->prepare("UPDATE workers_workday SET begin_time=?, end_time=?, break_begin=?,
                           break_end=?, description=?, done=? WHERE worker_id=? and work_day_date=?");
            $stmt->bind_param("sssssiis", $begin_time, $end_time, $break_begin, $break_end,
                $description, $done, $worker_id, $workday_date);
            $stmt->execute();
        }
        else{
            $stmt = $this->mysqli->prepare("INSERT INTO workers_workday (begin_time, end_time, break_begin,
                           break_end, description, worker_id, work_day_date, done) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssisi", $begin_time, $end_time, $break_begin, $break_end,
                $description, $worker_id, $workday_date, $done);
            $stmt->execute();
            $worker_workday_id = $this->stmt->insert_id;
        }
        foreach ($projects as $project_id=>$value){
            $this->CreateOrEditProjectDataForWorkday($project_id, $worker_workday_id, $value);
        }
        return $this->mysqli->connect_errno == 0;
    }

    public function GetWorkerWorkDay($worker_id, $workday): WorkerWorkDay
    {
        $stmt = $this->mysqli->prepare("SELECT * FROM workers_workday WHERE worker_id=? and work_day_date=?");
        $stmt->bind_param("is", $worker_id, $workday->day);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0){
            $day = $result->fetch_assoc();
            $retval = new WorkerWorkDay();
            $retval->id = $day["id"];
            $retval->begin_time = $day["begin_time"];
            $retval->end_time = $day["end_time"];
            $retval->break_begin = $day["break_begin"];
            $retval->break_end = $day["break_end"];
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
            $retval->id=0;
            $retval->begin_time = $day["begin_time"];
            $retval->end_time = $day["end_time"];
            $retval->break_begin = $day["break_begin"];
            $retval->break_end = $day["break_end"];
            $retval->description = $day["description"];
            $retval->done = false;
            return $retval;
        }
        throw new Exception("empty result");
    }

    public function GetDoneWorkerWorkDays($worker_id, $month, $year): array
    {
        $sql = "SELECT begin_time, end_time, break_begin, break_end, description, work_day_date FROM workers_workday";
        $sql.= " WHERE worker_id=? AND MONTH(work_day_date)=? AND YEAR(work_day_date)=? AND done = 1";
        $stmt = $this->mysqli->prepare( $sql);
        $stmt->bind_param("iii", $worker_id, $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $retval = array();
        while($row = $result->fetch_assoc()){
            $retval[$row['work_day_date']] = $row;
        }
        return $retval;
    }

    /**
     * @param $worker_id
     * @return array
     */
    public function GetWorkerDefaultWithId($worker_id): array
    {
        $stmt = $this->mysqli->prepare("SELECT * FROM default_days WHERE worker_id=? ORDER BY work_day_number ASC");
        $stmt->bind_param("i", $worker_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $retval = array();
        while($row = $result->fetch_assoc()){
            array_push($retval, $row);
        }
        return $retval;
    }

    public function GetWorkerNameWithId($worker_id): string
    {
        $stmt = $this->mysqli->prepare("SELECT name, surname FROM workers WHERE id=?");
        $stmt->bind_param("i", $worker_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['name'] . ' ' . $result['surname'];
    }

    public function CreateOrUpdateDefaultForUser($worker_id, $workday_number, $begin_time, $end_time, $break_begin,
                                                 $break_end, $description): string
    {
        if($begin_time == "") $begin_time = null;
        if($end_time == "") $end_time = null;
        if($break_begin == "") $break_begin = null;
        if($break_end == "") $break_end = null;
        $stmt = $this->mysqli->prepare("SELECT * FROM default_days WHERE worker_id=? and work_day_number=?");
        $stmt->bind_param("ii", $worker_id, $workday_number);
        $stmt->execute();
        if($stmt->get_result()->fetch_array()){
            $stmt = $this->mysqli->prepare("UPDATE default_days SET begin_time=?, end_time=?, break_begin=?,
                           break_end=?, description=? WHERE worker_id=? and work_day_number=?");
            $stmt->bind_param("ssssssii", $begin_time, $end_time, $break_begin, $break_end,
                $description, $worker_id, $workday_number);
            $stmt->execute();
        }
        else{
            $stmt = $this->mysqli->prepare("INSERT INTO default_days (begin_time, end_time, break_begin,
                           break_end, description, worker_id, work_day_number) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssssii", $begin_time, $end_time, $break_begin, $break_end,
                $description, $worker_id, $workday_number);
            $stmt->execute();
        }
        return $this->mysqli->error;
    }

    public function SetWorkerDefaultToNull($worker_id, $day): string
    {
        $sql = "SELECT * FROM default_days WHERE worker_id = $worker_id AND work_day_number = $day";
        $result = $this->mysqli->query($sql);
        if($result->num_rows > 0){
            $sql = "UPDATE default_days SET work_day_number = $day, begin_time = null, end_time = null, break_begin = null, break_end = null, description = null";
            $sql.= " WHERE worker_id = $worker_id AND work_day_number = $day";
            $this->mysqli->query($sql);
        }
        else{
            $sql = "INSERT INTO default_days (worker_id, work_day_number, begin_time, end_time, break_begin, break_end, description)";
            $sql.= " VALUES ($worker_id, $day, null, null, null, null, null)";
            $this->mysqli->query($sql);
        }
        return $this->mysqli->error;
    }

    public function CompareNewPasswords($new_pass1, $new_pass2): bool
    {
        return $new_pass1 == $new_pass2;
    }

    public function UpdateWorkerPassword($worker_id, $new_password): bool
    {
        $stmt = $this->mysqli->prepare("UPDATE workers SET password_hash=MD5(?) WHERE id=?");
        $stmt->bind_param("si", $new_password, $worker_id);
        $stmt->execute();
        if($stmt->affected_rows == 1)
            return true;
        return false;
    }

    public function CalculateDayTime($begin_time, $end_time, $break_begin, $break_end): ?string
    {
        if($begin_time == null || $end_time == null || $break_begin == null || $break_end == null)
            return null;
        $base = strtotime('00:00:00');
        $begin_time = strtotime($begin_time) - $base;
        $end_time = strtotime($end_time) - $base;
        $break_begin = strtotime($break_begin) - $base;
        $break_end = strtotime($break_end) - $base;
        $totaltime = $end_time - $begin_time - ($break_end - $break_begin);
        $h = intval($totaltime / 3600);
        $totaltime = $totaltime - ($h * 3600);
        $m = intval($totaltime / 60);
        if($m == 0)
            $m = "00";
        return "$h:$m";
    }

    public function CalculateTotalTime($time): string
    {
        $total = 0;
        foreach ($time as $t){
            if($t == null)
                continue;
            $arr = explode(':', $t);
            $total += (int)$arr[0] * 60 + (int)$arr[1];
        }
        $min = $total % 60;
        $hrs = floor($total / 60);
        return "$hrs:$min";
    }

    /**
     * @return Project[]
    */
    public function GetAllActiveProjects(): array
    {
        $sql = "SELECT id, name, active, '00:00:00' as time FROM projects WHERE active=1";
        $result = $this->mysqli->query($sql);
        $retval = array();
        while ($row = $result->fetch_object("Project")) {
            array_push($retval, $row);
        }
        return $retval;
    }

    /**
     * @param $worker_workday_id
     * @return Project[]
     */
    public function GetProjectsForWorkersDay($worker_workday_id): array
    {
        $sql = "SELECT p.id as id, p.name as name, p.active as active, w.time as time FROM projects p, workday_project w
WHERE w.worker_workday_id=? AND p.id=w.project_id";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("i",$worker_workday_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $retval = array();
        while ($row = $result->fetch_object("Project")) {
            array_push($retval, $row);
        }
        return $retval;
    }

    /**
     * @param $worker_workday_id
     * @return Project[]
     */
    public function GetProjectsForWorkersDayAlreadyInactive($worker_workday_id): array
    {
        $sql = "SELECT p.id as id, p.name as name, p.active as active, w.time as time FROM projects p, workday_project w
                WHERE w.worker_workday_id=? AND p.id=w.project_id AND p.active=0";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("i",$worker_workday_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $retval = array();
        while ($row = $result->fetch_object("Project")) {
            array_push($retval, $row);
        }
        return $retval;
    }

    /**
     * @param $worker_workday_id
     * @return Project[]
     */
    public function GetRelevantProjectsForDay($worker_workday_id): array
    {
        return array_merge($this->GetProjectsForWorkersDayAlreadyInactive($worker_workday_id), $this->GetAllActiveProjects());
    }

    public function CreateOrEditProjectDataForWorkday($project_id, $worker_workday_id, $time): bool
    {
        if($this->TimeIs0($time))
            return true;
        $stmt = $this->mysqli->prepare("SELECT * FROM workday_project WHERE project_id=? AND worker_workday_id=?");
        $stmt->bind_param("ii", $project_id, $worker_workday_id);
        $stmt->execute();
        $result=$stmt->get_result();
        if($result->num_rows>0){
            $stmt2 = $this->mysqli->prepare("UPDATE workday_project SET time=? WHERE project_id=? AND worker_workday_id=?");
            $stmt2->bind_param("sii", $time, $project_id, $worker_workday_id);
            return $stmt2->execute();
        }
        else{
            $stmt2 = $this->mysqli->prepare("INSERT INTO workday_project (worker_workday_id, project_id, time) VALUES (?,?,?)");
            $stmt2->bind_param("iis", $project_id, $worker_workday_id, $time);
            return $stmt2->execute();
        }
    }

    public function TimeIs0($time): bool
    {
        $myArray = str_split($time);
        //echo json_encode($myArray);
        foreach($myArray as $character){
            if($character != "0" && $character != ":" &&$character!="") {
                return false;
            }
        }
        return true;
    }

    public function GetProjectDataForWorkday($worker_workday_id){
        if($worker_workday_id == 0)
            return array();
        $stmt = $this->mysqli->prepare("SELECT * FROM workday_project WHERE worker_workday_id=?");
        $stmt->bind_param("i", $worker_workday_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $retval = array();
        while ($row = $result->fetch_assoc()){
            $retval[$row["project_id"]]=$row["time"];
        }
        return $retval;
    }
}
