<?php
if(!isset($_COOKIE["jos_username"])||!isset($_COOKIE["jos_user_auth"])){
    header("Location: login.php");
    die();
}
include_once 'service.php';
$service = new Service();
$user = $service->GetUserWithUsername($_COOKIE["jos_username"]);

if($_COOKIE["jos_user_auth"] != md5("askjfh".$user->username."weq".$user->password_hash)){
    header("Location: login.php");
    die();
}

if(!$service->CheckUserInCookies($_COOKIE["jos_username"], $_COOKIE["jos_user_auth"])){
    header("Location: login.php");
    die();
}

session_start();

$_SESSION["user_id"] = $user->id;
$_SESSION["user_role"] = $user->is_admin;
