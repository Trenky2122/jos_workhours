<?php
require_once './vendor/autoload.php';
include_once "service.php";
include_once "models.php";

$service = new Service();
$apiKeyWorkspace = $service->GetWorkerWithId($_POST["worker_id"])->clockify_api_key;

$builder = new JDecool\Clockify\ClientBuilder();
$client = $builder->createClientV1($apiKeyWorkspace);

$apiFactory = new JDecool\Clockify\ApiFactory($client);
$userApi = $apiFactory->userApi();

$user = $userApi->current();
$time_entries = $apiFactory->timeEntryApi();
$params = array();

$entries = $client->get("workspaces/".$user->activeWorkspace()."/user/".$user->id()."/time-entries?start=".$_POST["from"]."&end=".$_POST["to"]);
$days_in_db = $service->GetDoneWorkerWorkDays($_POST["worker_id"], date("m", strtotime($_POST["from"])), date("Y", strtotime($_POST["from"])));
function dayFromDbToClockifyFormat($date, $start, $end, $description){
    $day_in_clockify_format = array();
    $day_in_clockify_format["timeInterval"] = array();
    $dateTimeStart = new DateTime($date."T".$start);
    $dateTimeEnd = new DateTime($date."T".$end);
    $day_in_clockify_format["timeInterval"]["start"] = $dateTimeStart->format(DATE_ATOM);
    $day_in_clockify_format["timeInterval"]["end"] = $dateTimeEnd->format(DATE_ATOM);
    $day_in_clockify_format["description"] = $description;
    return $day_in_clockify_format;
}
foreach($days_in_db as $date=>$day){
    if($day["break_end"] == null){
        $entries[] = dayFromDbToClockifyFormat($day["work_day_date"], $day["begin_time"], $day["end_time"], $day["description"]);
    }
    else{
        $entries[] = dayFromDbToClockifyFormat($day["work_day_date"], $day["begin_time"], $day["break_begin"], $day["description"]);
        $entries[] = dayFromDbToClockifyFormat($day["work_day_date"], $day["break_end"], $day["end_time"], "");
    }
}
$entriesToAdd = array();
for ($i=0; $i<count($entries); $i++){
    $startDate = new DateTime($entries[$i]["timeInterval"]["start"]);
    $startDate->setTimezone(new DateTimeZone("Europe/Bratislava"));
    $endDate = new DateTime($entries[$i]["timeInterval"]["end"]);
    $endDate->setTimezone(new DateTimeZone("Europe/Bratislava"));
    $entries[$i]["timeInterval"]["start"] = $startDate->format(DATE_ATOM);
    $entries[$i]["timeInterval"]["end"] = $endDate->format(DATE_ATOM);
    $entries[$i]["project"] = PARTNERS_PROJECT_ID;
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

function cmp($a, $b): int
{
    if ($a["timeInterval"]["start"] == $b["timeInterval"]["start"]) {
        return 0;
    }
    return ($a["timeInterval"]["start"] < $b["timeInterval"]["start"]) ? -1 : 1;
}

function addIntervals($i1, $i2): DateInterval
{
    $e = new DateTime('00:00');
    $f = clone $e;
    $e->add($i1);
    $e->add($i2);
    return $f->diff($e);
}
$worker_workdays = array();
foreach ($entriesByDate as $date=>$entries){
    $worker_workday = new WorkerDayClockify();
    $worker_workday->worker_id = $_POST["worker_id"];
    $worker_workday->work_day_date = $date;
    usort($entries, "cmp");
    echo json_encode($entries, JSON_PRETTY_PRINT);
    $worker_workday->begin_time = date("H:i:s", strtotime($entries[0]["timeInterval"]["start"]));
    $worker_workday->end_time = date("H:i:s", strtotime($entries[count($entries)-1]["timeInterval"]["end"]));
    $worker_workday->break_begin = date("H:i:s", strtotime($entries[0]["timeInterval"]["end"]));
    $total_break_time = new DateInterval("PT0H");
    for($i=0; $i<count($entries)-1; $i++){
        $break_begin_time = new DateTime($entries[$i]["timeInterval"]["end"]);
        $break_end_time = new DateTime($entries[$i+1]["timeInterval"]["start"]);
        $difference = $break_begin_time->diff($break_end_time);
        $total_break_time = addIntervals($total_break_time, $difference);
    }
    $break_begin_time = new DateTime($entries[0]["timeInterval"]["end"]);
    $break_end_time = $break_begin_time->add($total_break_time);
    $worker_workday->break_end = $break_end_time->format("H:i:s");
    if($worker_workday->break_begin == $worker_workday->break_end){
        $worker_workday->break_begin = null;
        $worker_workday->break_end = null;
    }
    $worker_workdays[]=$worker_workday;
}
header('Content-type: application/json');
echo json_encode($worker_workdays, JSON_PRETTY_PRINT);
