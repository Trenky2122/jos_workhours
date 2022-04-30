<?php
if(!isset($_POST['worker_id']) || !isset($_POST['key'])){
    header("Location: index.php?err=1");
    die();
}

include "service.php";
$service = new Service();

$res = $service->SetApiKey($_POST['worker_id'], $_POST['key']);
if($res == 0){
    header("Location: index.php?succ=1");
}
else{
    header("Location: index.php?err=3");
}

