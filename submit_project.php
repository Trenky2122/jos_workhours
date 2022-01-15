<?php
if(!isset($_POST["name"])||!isset($_POST["admin_password"])) {
    header("Location: add_project.php?err=1");
    die();
}
include "service.php";
$service = new Service();
if(!$service->WorkerCorrectPassword(-1, $_POST["admin_password"])){
    header("Location: add_project.php?err=2");
    die();
}
if($service->CreateProject($_POST["name"])){
    header("Location: index.php?succ=1");
}
else{
    header("Location add_project.php?err=3");
}
