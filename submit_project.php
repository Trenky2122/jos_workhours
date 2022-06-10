<?php
if(!isset($_POST["name"])) {
    header("Location: add_project.php?err=1");
    die();
}
include "service.php";
$service = new Service();
if($service->CreateProject($_POST["name"])){
    header("Location: index.php?succ=1");
}
else{
    header("Location add_project.php?err=3");
}
