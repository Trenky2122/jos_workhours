<?php

include "authenticate.php";
if (!isset($_POST["worker_id"]) || !isset($_POST["month"]) || !isset($_POST["submit"])) {
    header("Location: index.php?err=1");
    die();
}
include_once("service.php");
include_once "mailer.php";
if ($_POST["worker_id"] == $_SESSION["user_id"] || $_SESSION["user_role"] == 1) {
    $service = new Service();
    $service->SetWorkerMonthForRework($_POST["worker_id"], $_POST["month"]);
    $mailer = new Mailer();
    $res=$mailer->SendEmailReworkMonth($_POST["worker_id"], $_POST["month"], $_POST["explanation"]);
    header("Location: month_view.php?succ=6&id=" . $_POST["worker_id"] . "&m=" . $_POST["month"]."&res=".$res);
    die();
} else {
    header("Location: month_view.php?id=" . $_POST["worker_id"] . "&m=" . $_POST["month"] . "&err=1");
    die();
}