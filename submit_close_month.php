<?php
include "authenticate.php";
if(!isset($_POST["worker_id"]) || !isset($_POST["month"])||!isset($_POST["submit"])){
    header("Location: index.php?err=1");
    die();
}
include_once("service.php");
if($_POST["worker_id"]==$_SESSION["user_id"]||$_SESSION["user_role"]==1){
    $service = new Service();
    $service->CloseMonthForWorker($_POST["worker_id"], $_POST["month"]);
    header("Location: month_view.php?succ=5&id=".$_POST["worker_id"]."&m=".$_POST["month"]);
    die();
}
else{
    header("Location: month_view.php?id=".$_POST["worker_id"]."&m=".$_POST["month"]."&err=1");
    die();
}