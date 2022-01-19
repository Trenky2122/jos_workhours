<?php
if(!isset($_POST["worker_id"])||!isset($_POST["begin_time"])
    ||!isset($_POST["end_time"])||!isset($_POST["break_begin"])||!isset($_POST["break_end"])
    ||!isset($_POST["description"])){
    header("Location: default_change.php?id=" . $_POST["worker_id"] . "&err=1");
    die();
}

include "service.php";
$service = new Service();

if($service->CreateOrUpdateDefaultForUser($_POST["worker_id"], $_POST['workday_number'], $_POST['begin_time'], $_POST['end_time'],
                                            $_POST['break_begin'], $_POST['break_end'], $_POST['description']))
    header("Location: index.php?succ=1");
else
    header("Location: default_change.php?id=" . $_POST["worker_id"] . "&err=3");

