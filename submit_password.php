<?php
if(!isset($_POST['worker_id']) || !isset($_POST['old_password']) || !isset($_POST['new_password1']) || !isset($_POST['new_password2'])){
    header("Location: index.php?err=1");
    die();
}

include "service.php";
$service = new Service();
if(!$service->WorkerCorrectPassword($_POST["worker_id"], $_POST["old_password"])){
    header("Location: password_change.php?err=2");
    die();
}

if(!$service->CompareNewPasswords($_POST['new_password1'], $_POST['new_password2'])){
    header("Location: password_change.php?err=4");
    die();
}

if($service->UpdateWorkerPassword($_POST["worker_id"], $_POST['new_password1']))
    header("Location: index.php?succ=1");
else
    header("Location: password_change.php?err=3");
