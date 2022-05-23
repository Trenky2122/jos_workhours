<?php

include "models.php";
include_once "config.php";
require_once './vendor/autoload.php';
//include_once "config_local.php";


class Service
{
    private mysqli $mysqli;

    /**
     * @throws Exception
     */
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
        $result = $this->mysqli->query("SELECT name, surname, id, password_hash, member_since, is_admin, email,
       clockify_api_key FROM workers WHERE id<>1 ");
        $retval = array();
        while ($row = $result->fetch_object("Worker")) {
            $retval[] = $row;
        }
        return $retval;
    }

    public function GetAllWorkersId(): array
    {
        $result = $this->mysqli->query("SELECT id FROM workers WHERE id<>1 ORDER BY surname, name");
        $retval = array();
        while ($row = $result->fetch_assoc()) {
            $retval[] = $row['id'];
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
            $retval[] = $day;
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
        if($this->WorkerHasDayClosed($worker_id, $workday_date)){
            return false;
        }
        $stmt = $this->mysqli->prepare("SELECT id FROM workers_workday WHERE worker_id=? and work_day_date=?");
        $stmt->bind_param("is", $worker_id, $workday_date);
        $stmt->execute();
        if($row = $stmt->get_result()->fetch_row()){
            $worker_workday_id = $row[0];
            $stmt = $this->mysqli->prepare("UPDATE workers_workday SET begin_time=?, end_time=?, break_begin=?,
                           break_end=?, description=?, done=? WHERE worker_id=? and work_day_date=?");
            $stmt->bind_param("sssssiis", $begin_time, $end_time, $break_begin, $break_end,
                $description, $done, $worker_id, $workday_date);
            $stmt->execute();
        }
        else{
            $stmt = $this->mysqli->prepare("INSERT INTO workers_workday (begin_time, end_time, break_begin,
                           break_end, description, worker_id, work_day_date, done) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssisi", $begin_time, $end_time, $break_begin, $break_end,
                $description, $worker_id, $workday_date, $done);
            $stmt->execute();
            $worker_workday_id = $stmt->insert_id;
        }
        foreach ($projects as $project_id=>$value){
            $this->CreateOrEditProjectDataForWorkday($project_id, $worker_workday_id, $value);
        }
        return $this->mysqli->connect_errno == 0;
    }

    /**
     * @throws Exception
     */
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
        if($workday->day<date("Y-m-d")){
            $retval = new WorkerWorkDay();
            $retval->id=0;
            $retval->begin_time = null;
            $retval->end_time = null;
            $retval->break_begin = null;
            $retval->break_end = null;
            $retval->description = null;
            $retval->done = false;
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
        $sql = "SELECT id, begin_time, end_time, break_begin, break_end, description, work_day_date FROM workers_workday";
        $sql.= " WHERE worker_id=? AND MONTH(work_day_date)=? AND YEAR(work_day_date)=? AND done = 1";
        $stmt = $this->mysqli->prepare( $sql);
        $stmt->bind_param("iii", $worker_id, $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $retval = array();
        while($row = $result->fetch_assoc()){
            $row["projectString"] = $this->GetProjectsStringForWorkersWorkday($row["id"]);
            $retval[$row['work_day_date']] = $row;
        }
        return $retval;
    }

    public function GetWorkerWorkDays($worker_id, $from, $to): array
    {
        $sql = "SELECT id, begin_time, end_time, break_begin, break_end, description, work_day_date, done FROM workers_workday";
        $sql.= " WHERE worker_id=? AND work_day_date>=? AND work_day_date<=?";
        $stmt = $this->mysqli->prepare( $sql);
        $stmt->bind_param("iss", $worker_id, $from, $to);
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
            $retval[] = $row;
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

    public function GetWorkerNameWithId2($worker_id): string
    {
        $stmt = $this->mysqli->prepare("SELECT name, surname FROM workers WHERE id=?");
        $stmt->bind_param("i", $worker_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['surname'] . ' ' . $result['name'];
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
        }
        else{
            $stmt = $this->mysqli->prepare("INSERT INTO default_days (begin_time, end_time, break_begin,
                           break_end, description, worker_id, work_day_number) VALUES (?,?,?,?,?,?,?)");
        }
        $stmt->bind_param("sssssii", $begin_time, $end_time, $break_begin, $break_end,
            $description, $worker_id, $workday_number);
        $stmt->execute();
        return $this->mysqli->errno==0;
    }

    public function SetWorkerDefaultToNull($worker_id, $day): string
    {
        $sql = "SELECT * FROM default_days WHERE worker_id = $worker_id AND work_day_number = $day";
        $result = $this->mysqli->query($sql);
        if($result->num_rows > 0){
            $sql = "UPDATE default_days SET work_day_number = $day, begin_time = null, end_time = null, break_begin = null, break_end = null, description = null";
            $sql.= " WHERE worker_id = $worker_id AND work_day_number = $day";
        }
        else{
            $sql = "INSERT INTO default_days (worker_id, work_day_number, begin_time, end_time, break_begin, break_end, description)";
            $sql.= " VALUES ($worker_id, $day, null, null, null, null, null)";
        }
        $this->mysqli->query($sql);
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
        if($begin_time == null || $end_time == null)
            return null;
        if($break_begin == null)
            $break_begin="00:00:00";
        if($break_end == null)
            $break_end="00:00:00";
        $base = strtotime('00:00:00');
        $begin_time = strtotime($begin_time) - $base;
        $end_time = strtotime($end_time) - $base;
        $break_begin = strtotime($break_begin) - $base;
        $break_end = strtotime($break_end) - $base;
        $totaltime = $end_time - $begin_time - ($break_end - $break_begin);
        $h = intval($totaltime / 3600);
        $totaltime = $totaltime - ($h * 3600);
        $m = intval($totaltime / 60);
        if($m < 10)
            $m = "0".$m;
        if($h < 10)
            $h = "0".$h;
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
        return ($hrs<10?"00":($hrs<100?"0":""))."$hrs:".($min<10?"0":"")."$min";
    }

    public function GetReducedTimeToMaximum($original_time, $overflow_time, $max_time):string{
        $minutes_orig = $this->GetMinutesFromHHMM($original_time);
        $minutes_overflow = $this->GetMinutesFromHHMM($overflow_time);
        $minutes_wanted = $this->GetMinutesFromHHMM($max_time);
        $result_minutes = $minutes_orig-($minutes_overflow - $minutes_wanted);
        $h = floor($result_minutes/60);
        $m = $result_minutes % 60;
        if($m < 10)
            $m = "0".$m;
        if($h < 10)
            $h = "0".$h;
        return "$h:$m";
    }

    public function GetMinutesFromHHMM($time):int{
        $split = explode(':', $time);
        return intval($split[0])*60 + intval($split[1]);
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
            $retval[] = $row;
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
            $retval[] = $row;
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
        if($this->TimeIs0($time)){
            $stmt = $this->mysqli->prepare("DELETE FROM workday_project WHERE project_id=? AND worker_workday_id=?");
            $stmt->bind_param("ii", $project_id, $worker_workday_id);
            $stmt->execute();
            return true;
        }
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
            $stmt2->bind_param("iis", $worker_workday_id, $project_id, $time);
            return $stmt2->execute();
        }
    }

    public function TimeIs0($time): bool
    {
        $myArray = str_split($time);
        foreach($myArray as $character){
            if($character != "0" && $character != ":" &&$character!="") {
                return false;
            }
        }
        return true;
    }

    public function GetWorkedProjectsForWorkday($date, $worker_id, $clockify_entries): array{
        $sql = "SELECT * FROM workday_project p, workers_workday d, projects pr WHERE pr.id=p.project_id 
                                                                  AND p.worker_workday_id=d.id AND d.work_day_date=? 
                                                                  AND d.worker_id=?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("si", $date, $worker_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $retval = array();
        while ($row = $result->fetch_assoc()){
            $retval[$row["name"]]=$row["time"];
        }
        $totalInterval = new DateInterval("PT0H");
        foreach ($clockify_entries as $entry) {
            if (isset($entry["timeInterval"]["duration"]) && $entry["timeInterval"]["duration"] != null
                && date("Y-m-d", strtotime($entry["timeInterval"]["start"])) == $date) {
                $totalInterval = $this->AddTimeIntervals($totalInterval, new DateInterval($entry["timeInterval"]["duration"]));
            }
        }
        $clockify_total = ($totalInterval->d * 24 + $totalInterval->h) . ":" . $totalInterval->i . ":" . $totalInterval->s;
        if($clockify_total != '0:0:0') {
            if (isset($retval["Partners"])) {
                $retval["Partners"] = $this->CalculateTotalTime(array($retval["Partners"], $clockify_total));
            } else {
                $retval["Partners"] = $clockify_total;
            }
        }
        return $retval;
    }

    public function GetProjectDataForWorkday($worker_workday_id): array
    {
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

    public function CreateWorker($name, $surname, $member_since, $username, $email): bool
    {
        $stmt = $this->mysqli->prepare("INSERT INTO workers (name, surname, member_since, username, email)
            VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $surname, $member_since, $username, $email);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt = $this->mysqli->prepare("INSERT INTO default_days(work_day_number, worker_id, begin_time, end_time, break_begin, break_end, description) 
                                                VALUES (?,?,null,null,null,null,null)");
        for($i=0; $i<7; $i++){
            $stmt->bind_param("ii", $i, $id);
            $stmt->execute();
        }
        return $this->mysqli->connect_errno == 0;
    }

    public function CreateProject($name): bool
    {
        $stmt = $this->mysqli->prepare("INSERT INTO projects (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        return $this->mysqli->connect_errno == 0;
    }

    public function GetAllProjects(): array
    {
        $sql = "SELECT *, '00:00' as time FROM projects";
        $result = $this->mysqli->query($sql);
        $retval = array();
        while ($row = $result->fetch_object("Project")) {
            $retval[] = $row;
        }
        return $retval;
    }

    public function GetProjectTimeSince($project_id, $from, $to): string
    {
        if($project_id == 8){
            $totalData = $this->GetWorkerDataForProjectPartners($from, $to);
            return $this->CalculateTotalTime(array_values($totalData));
        }
        $sql = "SELECT time FROM workers_workday w, workday_project p WHERE p.worker_workday_id = w.id AND p.project_id=? 
                AND w.work_day_date>=? AND w.work_day_date<=? AND w.done=1";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("iss", $project_id, $from, $to);
        $stmt->execute();
        $result = $stmt->get_result();
        $results = array();
        while($row = $result->fetch_array(MYSQLI_NUM)){
            $results[] = $row[0];
        }
        if($results == null)
            $results = array();
        return $this->CalculateTotalTime($results);
    }

    public function DisableProject($project_id): bool
    {
        $stmt = $this->mysqli->prepare("UPDATE projects SET active=0 WHERE id=?");
        $stmt->bind_param("i", $project_id);
        return $stmt->execute();
    }

    public function EnableProject($project_id): bool
    {
        $stmt = $this->mysqli->prepare("UPDATE projects SET active=1 WHERE id=?");
        $stmt->bind_param("i", $project_id);
        return $stmt->execute();
    }

    public function GetProjectsStringForWorkersWorkday($worker_workday_id, $partners = false): string
    {
        $sql="SELECT p.name FROM projects p, workers_workday w, workday_project wp WHERE 
                w.id=? AND p.id=wp.project_id AND wp.worker_workday_id = w.id";
        if($worker_workday_id == -2)
            return "Partners";
        $retval = "";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("i", $worker_workday_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $first = true;
        if($partners){
            $first = false;
            $retval = "Partners";
        }
        while ($row = $result->fetch_assoc()){
            if(!$first) {
                $retval .= ", ";
            }
            $retval.=$row["name"];
            $first = false;
        }
        return $retval;
    }

    public function CreateAdminWorker($name, $surname, $member_since): bool
    {
        $stmt = $this->mysqli->prepare("INSERT INTO workers (name, surname, member_since, is_admin) VALUES (?,?,?,1)");
        $stmt->bind_param("sss", $name, $surname, $member_since);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt = $this->mysqli->prepare("INSERT INTO default_days(work_day_number, worker_id, begin_time, end_time, break_begin, break_end, description) 
                                                VALUES (?,?,null,null,null,null,null)");
        for($i=0; $i<7; $i++){
            $stmt->bind_param("ii", $i, $id);
            $stmt->execute();
        }
        return $this->mysqli->connect_errno == 0;
    }

    /**
     * @return Worker[]
     */
    public function GetAllClockifyWorkers(): array
    {
        $result = $this->mysqli->query("SELECT name, surname, id, password_hash, member_since, is_admin, email,
       clockify_api_key FROM workers WHERE clockify_api_key <> '' ");
        $retval = array();
        while ($row = $result->fetch_object("Worker")) {
            $retval[] = $row;
        }
        return $retval;
    }

    function NormalizeTime($time): string
    {
        $timeSplit = explode(":", $time);
        $hours = intval($timeSplit[0]);
        return $hours.":".$timeSplit[1];
    }

    public function GetWorkerDataForProjectPartners($from, $to){
        $workers = $this -> GetAllClockifyWorkers();
        $builder = new JDecool\Clockify\ClientBuilder();
        $retval = array();
        foreach ($workers as $worker){
            $client = $builder->createClientV1($worker->clockify_api_key);
            $apiFactory = new JDecool\Clockify\ApiFactory($client);
            $userApi = $apiFactory->userApi();

            $user = $userApi->current();
            $entries = $client->get("workspaces/" . $user->activeWorkspace()
                . "/user/" . $user->id() . "/time-entries?page-size=5000&start=" . date("Y-m-d\TH:i:s\Z", strtotime($from)) .
                "&end=" . date("Y-m-d\TH:i:s\Z", strtotime($to)));
            $timeInterval = new DateInterval("PT0H");
            foreach ($entries as $entry){
                if($entry["timeInterval"]["duration"]==null)
                    continue;
                $timeInterval = $this->AddTimeIntervals($timeInterval, new DateInterval($entry["timeInterval"]["duration"]));
            }
            $retval[$worker->GetFullName()] = ($timeInterval->d * 24 + $timeInterval->h) . ":".($timeInterval->i<10?"0":"") . $timeInterval->i;
        }
        return $retval;
    }

    public function GetWorkerDataForProject($project_id, $from, $to): array
    {
        if($project_id == 8)
            return $this->GetWorkerDataForProjectPartners($from, $to);
        $sql = "SELECT u.name, u.surname, time FROM workers_workday w, workday_project p, workers u WHERE p.worker_workday_id = w.id AND p.project_id=? 
                AND u.id=w.worker_id AND w.work_day_date>=? AND w.work_day_date<=? AND w.done=1";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("iss", $project_id, $from, $to);
        $stmt->execute();
        $result = $stmt->get_result();
        $values = array();
        while ($row = $result->fetch_assoc()){
            if(!isset($values[$row["name"]." ".$row["surname"]])){
                $values[$row["name"]." ".$row["surname"]]=array();
            }
            $values[$row["name"]." ".$row["surname"]][]=$row["time"];
        }
        $retval = array();
        foreach ($values as $key=>$value){
            $retval[$key]=$this->CalculateTotalTime($value);
        }
        return $retval;
    }

    public function GetUser($username, $password){
        $sql = "SELECT * FROM workers WHERE username=? AND password_hash=MD5(?)";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_object("Worker");
    }

    public function SaveCookie($username, $cookie):bool{
        $sql0 = "DELETE FROM user_cookies WHERE username=? AND cookie=?";
        $stmt = $this->mysqli->prepare($sql0);
        $stmt->bind_param("ss", $username, $cookie);
        $stmt->execute();
        $sql = "INSERT INTO user_cookies(username, cookie) VALUES (?, ?)";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("ss", $username, $cookie);
        return $stmt->execute();
    }

    public function GetUserWithUsername($username){
        $sql = "SELECT * FROM workers WHERE username=?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_object("Worker");
    }

    public function CheckUserInCookies($username, $cookie): bool
    {
        $sql = "SELECT * FROM user_cookies WHERE username=? AND cookie=?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("ss", $username, $cookie);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0)
            return true;
        return false;
    }

    /**
     * @param $id
     * @return Worker
     */
    public function GetWorkerWithId($id): Worker
    {
        $sql = "SELECT * FROM workers WHERE id=?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_object("Worker");
    }

    public function GetProjectDataForWorker($worker_id, $from, $to, $clockify_data): array
    {
        $sql = "SELECT pj.name, time FROM projects pj, workers_workday w, workday_project wp WHERE wp.worker_workday_id = w.id AND wp.project_id=pj.id
                AND w.worker_id=? AND w.work_day_date>=? AND w.work_day_date<=? AND w.done=1";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("iss", $worker_id, $from, $to);
        $stmt->execute();
        $result = $stmt->get_result();
        $values = array();
        while ($row = $result->fetch_assoc()){
            if(!isset($values[$row["name"]])){
                $values[$row["name"]]=array();
            }
            $values[$row["name"]][]=$row["time"];
        }

        $retval = array();
        foreach ($values as $key=>$value){
            $retval[$key]=$this->CalculateTotalTime($value);
        }

        if($clockify_data) {
            $clockify_entries = json_decode($clockify_data, true);
            $totalInterval = new DateInterval("PT0H");
            foreach ($clockify_entries as $entry) {
                if (isset($entry["timeInterval"]["duration"]) && $entry["timeInterval"]["duration"] != null) {
                    $totalInterval = $this->AddTimeIntervals($totalInterval, new DateInterval($entry["timeInterval"]["duration"]));
                }
            }
            $clockify_total = ($totalInterval->d * 24 + $totalInterval->h) . ":" . $totalInterval->i . ":" . $totalInterval->s;
            if (isset($retval["Partners"])) {
                $retval["Partners"] = $this->CalculateTotalTime(array($retval["Partners"], $clockify_total));
            } else {
                $retval["Partners"] = $clockify_total;
            }
        }
        return $retval;
    }

    public function WorkerHasDayClosed($worker_id, $day):bool{
        return $this->WorkerHasMonthClosed($worker_id, date("Y-m", strtotime($day)));
    }

    public function WorkerHasMonthClosed($worker_id, $month):bool{
        $sql = "SELECT * FROM closed_months WHERE worker_id=? AND month=? AND to_be_reworked=0";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("is", $worker_id, $month);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function GetWorkerMonthClosedData($worker_id, $month){
        $sql = "SELECT * FROM closed_months WHERE worker_id=? AND month=?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("is", $worker_id, $month);
        $stmt->execute();
        $result = $stmt->get_result();
        $retval = $result->fetch_assoc();
        return $retval;
    }

    public function CloseMonthForWorker($worker_id, $month, $clockify_data): int
    {
        $sql = "INSERT INTO closed_months(worker_id, month, clockify_data) VALUES (?,?, ?)";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("iss", $worker_id, $month, $clockify_data);
        $stmt->execute();
        return $stmt->errno;
    }

    public function WorkerHasMonthForRework($worker_id, $month):bool{
        $sql = "SELECT * FROM closed_months WHERE worker_id=? AND month=? AND to_be_reworked=1";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("is", $worker_id, $month);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function SetWorkerMonthForRework($worker_id, $month){
        $sql = "UPDATE closed_months SET to_be_reworked=1 WHERE worker_id=? AND month=?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("is", $worker_id, $month);
        $stmt->execute();
        return $stmt->errno;
    }

    public function MarkWorkerMonthAsReworked($worker_id, $month, $clockify_data): int
    {
        $sql = "UPDATE closed_months SET to_be_reworked=0, clockify_data=? WHERE worker_id=? AND month=?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("sis", $clockify_data, $worker_id, $month);
        $stmt->execute();
        return $stmt->errno;
    }

    public function SetApiKey($worker_id, $key): int
    {
        $sql = "UPDATE workers SET clockify_api_key=? WHERE id=?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("si", $key, $worker_id);
        $stmt->execute();
        return $stmt->errno;
    }

    public function AddTimeIntervals($i1, $i2): DateInterval
    {
        $e = new DateTime('00:00');
        $f = clone $e;
        $e->add($i1);
        $e->add($i2);
        return $f->diff($e);
    }

    public function DayFromDbToClockifyFormat($date, $start, $end, $description, $id): array
    {
        $day_in_clockify_format = array();
        $day_in_clockify_format["timeInterval"] = array();
        $dateTimeStart = new DateTime($date."T".$start);
        $dateTimeEnd = new DateTime($date."T".$end);
        $day_in_clockify_format["timeInterval"]["start"] = $dateTimeStart->format(DATE_ATOM);
        $day_in_clockify_format["timeInterval"]["end"] = $dateTimeEnd->format(DATE_ATOM);
        $day_in_clockify_format["description"] = $description;
        $day_in_clockify_format["id"] = $id;
        return $day_in_clockify_format;
    }

    public function ClockifyEntriesToDayFormat($entries): array
    {
        $entriesToAdd = array();
        for ($i=0; $i<count($entries); $i++){
            $startDate = new DateTime($entries[$i]["timeInterval"]["start"]);
            $startDate->setTimezone(new DateTimeZone("Europe/Bratislava"));
            $endDate = new DateTime($entries[$i]["timeInterval"]["end"]);
            $endDate->setTimezone(new DateTimeZone("Europe/Bratislava"));
            $entries[$i]["timeInterval"]["start"] = $startDate->format(DATE_ATOM);
            $entries[$i]["timeInterval"]["end"] = $endDate->format(DATE_ATOM);
            while(date("Y-m-d", strtotime($entries[$i]["timeInterval"]["start"])) < date("Y-m-d", strtotime($entries[$i]["timeInterval"]["end"]))){
                $newEntry = array();
                $newEntry["timeInterval"]=array();
                $newEntry["timeInterval"]["start"]=$entries[$i]["timeInterval"]["start"];
                $date = new DateTime($entries[$i]["timeInterval"]["start"]);
                $date->setTime(23, 59);
                $newEntry["timeInterval"]["end"]=$date->format(DATE_ATOM);
                $newEntry["description"]=$entries[$i]["description"];
                $entriesToAdd[]=$newEntry;
                $date -> add(new DateInterval("P1D"));
                $date->setTime(0, 0);
                $entries[$i]["timeInterval"]["start"] = $date->format(DATE_ATOM);
            }
        }
        array_push($entries, ...$entriesToAdd);
        $entriesByDate = array();
        foreach ($entries as $entry){
            $entriesByDate[date("Y-m-d", strtotime($entry["timeInterval"]["start"]))][]=$entry;
        }
        if(!function_exists("cmp")) {
            function cmp($a, $b): int
            {
                if ($a["timeInterval"]["start"] == $b["timeInterval"]["start"]) {
                    return 0;
                }
                return ($a["timeInterval"]["start"] < $b["timeInterval"]["start"]) ? -1 : 1;
            }
        }
        $worker_workdays = array();
        foreach ($entriesByDate as $date=>$dayEntries){
            $worker_workday = array();
            $worker_workday["id"]=-2;
            $worker_workday["work_day_date"] = $date;
            $worker_workday["description"] = "";
            $worker_workday["done"] = true;
            foreach ($dayEntries as $dayEntry){
                $worker_workday["description"] .= " ".$dayEntry["description"];
            }
            usort($dayEntries, "cmp");
            $worker_workday["begin_time"] = date("H:i:s", strtotime($dayEntries[0]["timeInterval"]["start"]));
            $worker_workday["end_time"] = date("H:i:s", strtotime($dayEntries[count($dayEntries)-1]["timeInterval"]["end"]));
            $worker_workday["break_begin"] = date("H:i:s", strtotime($dayEntries[0]["timeInterval"]["end"]));
            $total_break_time = new DateInterval("PT0H");
            for($i=0; $i<count($dayEntries)-1; $i++){
                $break_begin_time = new DateTime($dayEntries[$i]["timeInterval"]["end"]);
                $break_end_time = new DateTime($dayEntries[$i+1]["timeInterval"]["start"]);
                $difference = $break_begin_time->diff($break_end_time);
                $total_break_time = $this->AddTimeIntervals($total_break_time, $difference);
            }
            $wasPartnersDoneThatDay = false;
            foreach ($dayEntries as $dayEntry){
                if(!is_numeric($dayEntry["id"])) {
                    $wasPartnersDoneThatDay = true;
                }
                else{
                    $worker_workday["id"] = $dayEntry["id"];
                }
            }
            $worker_workday["projectString"] = $this->GetProjectsStringForWorkersWorkday($worker_workday["id"], $wasPartnersDoneThatDay);
            $break_begin_time = new DateTime($dayEntries[0]["timeInterval"]["end"]);
            $break_end_time = $break_begin_time->add($total_break_time);
            $worker_workday["break_end"] = $break_end_time->format("H:i:s");
            if($worker_workday["break_begin"] == $worker_workday["break_end"]){
                $worker_workday["break_begin"] = null;
                $worker_workday["break_end"] = null;
            }
            $worker_workdays[$date]=$worker_workday;
        }
        return $worker_workdays;
    }
}
