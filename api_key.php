<?php
$active = "month";
include "header.php";
include_once "service.php";

$service = new Service();
$worker_id = $_SESSION["user_id"];
?>

<form action="submit_api_key.php" method="post" style="padding-top: 2em;">
    <input type="hidden" value="<?= $worker_id ?>" name="worker_id">
        <div class="row mt-1">
            <div class="col">
                <label style="float: right" for="key">Kľúč:</label>
            </div>
            <div class="col">
                <input type="text" name="key" id="key">
            </div>
        </div>
        <div class="row mt-1">
            <div class="col">
                <label style="float: right" for="workspace">Workspace:</label>
            </div>
            <div class="col">
                <input type="text" name="workspace" id="workspace">
            </div>
        </div>
        <div class="row mt-1">
            <div class="col"></div>
            <div class="col">
                <input class="btn btn-primary" type="submit" value="Odoslať" name="submit">
            </div>
        </div>
    </form>