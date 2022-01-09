<?php
if(!isset($_POST["user_id"])){
    die;
}
include "service.php";
$service = new Service();
if(!$service->WorkerCorrectPassword($_POST["worker_id"], $_POST["password"])){
    die;
}
$service->CreateOrUpdateWorkdayForUser($_POST["worker_id"], $_POST["work_day_id"], $_POST["begin_time"],
    $_POST["end_time"], $_POST["break_begin"], $_POST["break_end"], $_POST["project"], $_POST["description"]);
