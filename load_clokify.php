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
echo json_encode($entries);
