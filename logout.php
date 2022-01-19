<?php
if(!isset($_POST["submit"])){
    header("Location: index.php");
    die();
}

session_unset();
session_destroy();

if(!isset($_COOKIE["jos_username"]) || !isset($_COOKIE["jos_user_auth"])){
    header("Location: index.php");
    die();
}

if(isset($_COOKIE["jos_username"])){
    setcookie("jos_username", null);
}
if(isset($_COOKIE["jos_user_auth"])){
    setcookie("jos_user_auth", null);
}

header("Location: login.php?succ=4");