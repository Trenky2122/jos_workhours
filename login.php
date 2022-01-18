<?php
include "header.php";
?>
    <form action="submit_login.php" method="post">
        <div class="row mt-1">
            <div class="col">
                <label style="float: right" for="name">Meno:</label>
            </div>
            <div class="col">
                <input type="text" name="name" id="name">
            </div>
        </div>
        <div class="row">
            <div class="col">
                <label style="float: right" for="password">Heslo:</label>
            </div>
            <div class="col">
                <input type="password" name="password" id="password">
            </div>
        </div>
    </form>
<?php
include "footer.php";