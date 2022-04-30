<?php
$active = "settings";
include "header.php";
include "message_bar.php";
?>
<script>
    function updateMail(){
        document.getElementById("email").value = document.getElementById("surname").value.toLowerCase().normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "") +
            "." + document.getElementById("name").value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    }
</script>
<div style="padding-left: 1em">
    <div class="row" style="padding-top: 1.5em;">
        <form method="post" action="submit_worker.php">
            <div class="row mb-1">
                <div class="col-3">
                    <label for="name">Meno:</label>
                </div>
                <div class="col-6">
                    <input type="text" name="name" id="name" onchange="updateMail();">
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-3">
                    <label for="surname">Priezvisko:</label>
                </div>
                <div class="col-6">
                    <input type="text" name="surname" id="surname" onchange="updateMail();">
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-3">
                    <label for="username">Prihlasovacie meno:</label>
                </div>
                <div class="col-6">
                    <input type="text" name="username" id="username">
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-3">
                    <label for="since">Členom od:</label>
                </div>
                <div class="col-6">
                    <input type="date" name="since" id="since" value="<?=date("Y-m-d")?>">
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-3">
                    <label for="email">Členom od:</label>
                </div>
                <div class="col-6">
                    <input type="text" name="email" id="email">@josgroup.sk
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-3">
                    <label for="admin_pwd">Admin heslo:</label>
                </div>
                <div class="col-6">
                    <input type="password" name="admin_password" id="admin_pwd">
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