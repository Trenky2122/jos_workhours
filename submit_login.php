<?php
if(!isset($_POST["name"])||!isset($_POST["password"])){
    header("Location: login.php?err=1");
    die();
}
include_once "service.php";
$service = new Service();
if($user = $service->GetUser($_POST["name"], $_POST["password"])){
    setcookie("jos_username", $user->username, time()+60*60*24*60);
    setcookie("jos_user_auth", md5("askjfh".$user->username."weq".$user->password_hash), time()+60*60*24*60);
    $service->SaveCookie($user->username, md5("askjfh".$user->username."weq".$user->password_hash));
    header("Location: index.php?succ=3");
}else{
    header("Location: login.php?err=2");
    die();
}