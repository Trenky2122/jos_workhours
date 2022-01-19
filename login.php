<?php

define("BYPASS_AUYHENTICATE", 1);

include "header.php";
include "message_bar.php";
?>
    <form action="submit_login.php" method="post" style="padding-top: 2em;">
        <div class="row mt-1">
            <div class="col">
                <label style="float: right" for="name">Meno:</label>
            </div>
            <div class="col">
                <input type="text" name="name" id="name">
            </div>
        </div>
        <div class="row mt-1">
            <div class="col">
                <label style="float: right" for="password">Heslo:</label>
            </div>
            <div class="col">
                <input type="password" name="password" id="password">
            </div>
        </div>
        <div class="row mt-1">
            <div class="col"></div>
            <div class="col">
                <input class="btn btn-primary" type="submit" value="Prihlásiť" name="submit">
            </div>
        </div>
    </form>
<?php
include "footer.php";