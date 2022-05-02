<?php
require_once './vendor/autoload.php';
include_once "service.php";

$service = new Service();
$apiKeyWorkspace = $service->GetWorkerWithId($_POST["worker_id"])->clockify_api_key;

$builder = new JDecool\Clockify\ClientBuilder();
$client = $builder->createClientV1($apiKeyWorkspace);

$apiFactory = new JDecool\Clockify\ApiFactory($client);
$userApi = $apiFactory->userApi();

$user = $userApi->current();
$time_entries = $apiFactory->timeEntryApi();
$params = array();

echo json_encode($user);
$entries = $client->get("workspaces/".$user->activeWorkspace()."/user/".$user->id()."/time-entries");
$entriesToAdd = array();
foreach ($entries as $entry){
    while(date("Y-m-d", strtotime($entry["timeInterval"]["start"])) < date("Y-m-d", strtotime($entry["timeInterval"]["end"]))){
        $newEntry = array();
        $newEntry["timeInterval"]=array();
        $newEntry["timeInterval"]["start"]=date("Y-m-d", strtotime($entry["timeInterval"]["start"]));
        $date = new DateTime($entry["timeInterval"]["start"]);
        $date->setTime(23, 59);
        $newEntry["timeInterval"]["end"]=$date->format(DateTimeInterface::ATOM);
        $newEntry["description"]=$entry["description"];
        $entriesToAdd[]=$newEntry;
        $date -> add(new DateInterval("1 day"));
        $date->setTime(0, 0);
        $entry["timeInterval"]["start"] = $date->format(DateTimeInterface::ATOM);
    }
}
$entriesByDate = array();
foreach ($entries as $entry){
    $entriesByDate[date("Y-m-d", strtotime($entry["timeInterval"]["start"]))][]=$entry;
}
echo json_encode($entriesByDate, JSON_PRETTY_PRINT);
