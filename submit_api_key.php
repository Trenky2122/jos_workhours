<?php
if(!isset($_POST['worker_id']) || !isset($_POST['workspace']) || !isset($_POST['key'])){
    header("Location: index.php?err=1");
    die();
}

include "service.php";
$service = new Service();

$service->SetApiKey($_POST['worker_id'], $_POST['workspace'], $_POST['key']);

