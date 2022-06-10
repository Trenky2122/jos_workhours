<?php
if(!isset($_POST["project_id"])||!isset($_POST["submit"])){
    header("Location: projects.php?err=1");
    die();
}
include_once "service.php";
$service = new Service();
if($_POST["submit"]=="Aktivovať"){
    if($service->EnableProject($_POST["project_id"])){
        header("Location: projects.php?succ=1");
    }
    else{
        header("Location: projects.php?err=3");
    }
    die();
}
if($_POST["submit"]=="Deaktivovať"){
    if($service->DisableProject($_POST["project_id"])){
        header("Location: projects.php?succ=1");
    }
    else{
        header("Location: projects.php?err=3");
    }
    die();
}
die("Bad request");