<?php
//die(json_encode($_POST));
if(!isset($_POST["worker_id"])||!isset($_POST["work_day_id"])||!isset($_POST["begin_time"])
    ||!isset($_POST["end_time"])||!isset($_POST["break_begin"])||!isset($_POST["break_end"])
    ||!isset($_POST["description"])){
    header("Location: index.php?err=1");
    die();
}
include "service.php";
$service = new Service();
if(!$service->WorkerCorrectPassword($_POST["worker_id"], $_POST["password"])){
    header("Location: index.php?err=2");
    die();
}
if($service->CreateOrUpdateWorkdayForUser($_POST["worker_id"], $_POST["work_day_id"], $_POST["begin_time"],
    $_POST["end_time"], $_POST["break_begin"], $_POST["break_end"], $_POST["description"], isset($_POST["done"]), $_POST["projects"])){
    header("Location: index.php?succ=1");
}
else{
    header("Location: index.php?err=3");
}
