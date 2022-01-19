<?php
include "header.php";
include_once "service.php";
include "message_bar.php";

$service = new Service();
$worker_id = $_GET["id"];
$worker_name = $service->GetWorkerNameWithId($worker_id);
?>

    <div class="row">
        <div class="col-1" style="margin-top: 1em">
            <a href="index.php" class="btn btn-primary"> späť</a>
        </div>
    </div>

    <div class="row">
        <form method="post" action="submit_password.php">
            <div class="row">
                <input type="hidden" name="worker_id" value="<?= $worker_id ?>">
            </div>
            <div class="row mb-1">
                <div class="col-3">
                    <p>Meno: <strong><?= $worker_name ?></strong></p>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-3">
                    <label for="old_password">Staré heslo:</label>
                </div>
                <div class="col-6">
                    <input type="password" name="old_password" id="old_password">
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-3">
                    <label for="new_password1">Nové heslo:</label>
                </div>
                <div class="col-6">
                    <input type="password" name="new_password1" id="new_password1">
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-3">
                    <label for="new_password2">Nové heslo:</label>
                </div>
                <div class="col-6">
                    <input type="password" name="new_password2" id="new_password2">
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-3">
                    <input required type="submit" class="btn btn-primary mt-1" value="Uložiť">
                </div>
            </div>
        </form>
    </div>

<?php
include "footer.php";

