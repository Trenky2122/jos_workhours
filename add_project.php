<?php
$active = "settings";
include "header.php";
include "message_bar.php";
?>

<div style="padding-left: 1em">
    <div class="row" style="padding-top: 1em;">
        <form method="post" action="submit_project.php">
            <div class="row mb-1">
                <div class="col-3">
                    <label for="name">Názov:</label>
                </div>
                <div class="col-6">
                    <input type="text" name="name" id="name">
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-3">
                    <input required type="submit" class="btn btn-primary" value="Uložiť">
                </div>
            </div>
        </form>
    </div>
</div>