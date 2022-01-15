<?php
include "header.php";
include "message_bar.php";
?>
<div class="row">
    <div class="col-1" style="margin-top: 1em">
        <a href="admin.php" class="btn btn-primary"> späť</a>
    </div>
</div>

<div class="row">
    <form method="post" action="submit_worker.php">
        <div class="row">
            <div class="col-3">
                <label for="name">Meno:</label>
                <input type="text" name="name" id="name">
            </div>
        </div>
        <div class="row">
            <div class="col-3">
                <label for="surname">Priezvisko:</label>
                <input type="text" name="surname" id="surname">
            </div>
        </div>
        <div class="row">
            <div class="col-3">
                <label for="since">Členom od:</label>
                <input type="date" name="since" id="since" value="<?=date("Y-m-d")?>">
            </div>
        </div>
        <div class="row">
            <div class="col-3">
                <label for="admin_pwd">Admin heslo:</label>
                <input type="password" name="admin_password" id="admin_pwd">
            </div>
        </div>
        <div class="row">
            <div class="col-3">
                <input required type="submit" class="btn btn-primary" value="Uložiť">
            </div>
        </div>
    </form>
</div>