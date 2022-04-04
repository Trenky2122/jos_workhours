<?php

include "authenticate.php";
if (!isset($_POST["month"]) || !isset($_POST["submit"])) {
    header("Location: index.php?err=1");
    die();
}
include_once("mailer.php");
if ($_SESSION["user_role"] == 1) {
    $mailer = new Mailer();
    $res=$mailer->SendCloseMonthReminder($_POST["month"]);
    header("Location: admin_month_view.php?succ=7&m=".$_POST["month"]."&res=$res");
    die();
} else {
    header("Location: index.php?err=1");
    die();
}