<?php
if(!isset($_POST["name"])||!isset($_POST["surname"])||!isset($_POST["admin_password"])
    ||!isset($_POST["since"])) {
    header("Location: add_worker.php?err=1");
    die();
}
include "service.php";
$service = new Service();
if(!$service->WorkerCorrectPassword(-1, $_POST["admin_password"])){
    header("Location: add_worker.php?err=2");
    die();
}
if($service->CreateWorker($_POST["name"], $_POST["surname"], $_POST["since"], $_POST["username"], $_POST["email"]."@josgroup.sk")){
    header("Location: index.php?succ=2");
}
else{
    header("Location add_worker.php?err=3");
}
