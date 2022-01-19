<?php
include "header.php";
include_once "service.php";
include "message_bar.php";

$service = new Service();
$worker_id = $_GET["id"];
$worker_default = $service->GetWorkerDefaultWithId($worker_id);
$worker_name = $service->GetWorkerNameWithId($worker_id);
?>
    <div class="row">
        <div class="col-1" style="margin-top: 1em">
            <a href="index.php" class="btn btn-primary"> späť</a>
        </div>
    </div>
    <div class="row">
        <div class="col-3" style="margin-top: 1em">
            <h3><?= $worker_name ?> DEFAULT</h3>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <table class="table table-stripped">
                <thead>
                <tr>
                    <th>Deň</th>
                    <th>Začiatočný čas</th>
                    <th>Konečný čas</th>
                    <th>Začiatok pauzy</th>
                    <th>Koniec pauzy</th>
                    <th>Celkový čas</th>
                    <th>Náplň práce</th>
                    <th>Heslo</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                    <?php
                        $days_of_week = array("Pondelok", "Utorok", "Streda", "Štvrtok", "Piatok", "Sobota", "Nedeľa");
                        foreach ($worker_default as $row){
                            ?>
                            <tr>
                                <form method="post" action="submit_default.php">
                                    <input required type="hidden" name="worker_id" value="<?= $worker_id ?>">
                                    <input required type="hidden" name="workday_number", value="<?= $row["work_day_number"] ?>">
                                    <td><?= $days_of_week[$row["work_day_number"]] ?></td>
                                    <td>
                                        <input id="total_hrs_<?= $row['id']?>_begin_time" onchange="recalculateHours('total_hrs_<?= $row['id']?>')"
                                               type="time" name="begin_time" value="<?= $row["begin_time"]?>">
                                    </td>
                                    <td>
                                        <input id="total_hrs_<?= $row['id']?>_end_time" onchange="recalculateHours('total_hrs_<?= $row['id']?>')"
                                               type="time" name="end_time" value="<?= $row["end_time"]?>">
                                    </td>
                                    <td>
                                        <input id="total_hrs_<?= $row['id']?>_break_begin" onchange="recalculateHours('total_hrs_<?= $row['id']?>')"
                                               type="time" name="break_begin" value="<?= $row["break_begin"]?>">
                                    </td>
                                    <td>
                                        <input id="total_hrs_<?= $row['id']?>_break_end" onchange="recalculateHours('total_hrs_<?= $row['id']?>')"
                                               type="time" name="break_end" value="<?= $row["break_end"]?>">
                                    </td>
                                    <td id="total_hrs_<?= $row['id']?>">
                                        0:00
                                    </td>
                                    <td>
                                        <textarea name="description"><?= $row["description"]?></textarea>
                                    </td>
                                    <td><input required type="submit" class="btn btn-primary" value="Uložiť"></td>
                                </form>
                            </tr>
                            <?php
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

<?php
include "footer.php";