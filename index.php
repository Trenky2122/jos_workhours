<?php
include "header.php";
include "service.php";
$service = new Service();
$year = date("Y");
$month = date("m");
if (isset($_GET["y"])) {
    if (is_numeric($_GET["y"])) {
        $year = $_GET["y"];
    } else {
        die("Bad format year: " . $_GET["y"]);
    }
}
$week = date("W");
if (isset($_GET["w"])) {
    if (is_numeric($_GET["w"])) {
        $week = $_GET["w"];
    } else {
        die("Bad format week: " . $_GET["w"]);
    }
}
$days = $service->GetDaysInWeek($year, $week);
$workers = $service->GetAllWorkers();
include "message_bar.php";
?>
    <div class="row mt-2">
        <div class="col-6">
            <label for="worker_select">Filter zamestnanec</label>
            <select id="worker_select" onchange="reloadFilter()">
                <option value="0">Všetci</option>
                <?php
                foreach ($workers as $worker) {
                    ?>
                    <option value="<?= "worker_" . $worker->id ?>"><?= $worker->GetFullName() ?></option>
                    <?php
                }
                ?>
            </select>
        </div>
        <div class="col-6">
            <label for="day_select">Filter deň</label>
            <select id="day_select" onchange="reloadFilter()">
                <option value="0">Celý týždeň</option>
                <?php
                foreach ($days as $day) {
                    ?>
                    <option value="<?= "day_" . $day->id ?>"><?= $day->day_of_week . " " . date("d.m.Y", strtotime($day->day)) ?></option>
                    <?php
                }
                ?>
            </select>
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
                    <th>Projekt</th>
                    <th>Vykonane</th>
                    <th>Heslo</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>

                <?php
                foreach ($workers as $worker) {
                    ?>
                    <tr class="table-row worker_<?= $worker->id ?> worker_name">
                        <td><strong><?= $worker->GetFullName() ?></strong></td>
                        <td><a href="default_change.php?id=<?= $worker->id ?>" class="btn btn-primary">upraviť default</a></td>
                        <td><a href="password_change.php?id=<?= $worker->id ?>" class="btn btn-primary"> zmeniť heslo</a></td>
                        <td colspan="8"><a href="month_view.php?id=<?= $worker->id ?>&m=<?= $month?>&y=<?=$year?>" class="btn btn-primary">mesačný prehľad</a></td>
                    </tr>
                    <?php
                    foreach ($days as $day) {
                        $workerData = $service->GetWorkerWorkDay($worker->id, $day);
                        ?>
                        <tr class="<?= "day_" . $day->id . " worker_" . $worker->id ?> table-row">
                            <td><?= date("d.m.Y", strtotime($day->day)) ?></td>
                            <form method="post" action="submit_workday.php">
                                <input required type="hidden" name="work_day_id" value="<?= $day->id ?>">
                                <input required type="hidden" name="worker_id" value="<?= $worker->id ?>">
                                <td><input required
                                           id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>_begin_time"
                                           onchange="recalculateHours('total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>')"
                                           type="time" step="300" name="begin_time" value="<?= $workerData->begin_time ?>"></td>
                                <td><input required id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>_end_time"
                                           onchange="recalculateHours('total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>')"
                                           type="time" step="300" name="end_time" value="<?= $workerData->end_time ?>"></td>
                                <td><input required
                                           id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>_break_begin"
                                           onchange="recalculateHours('total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>')"
                                           type="time" step="300" name="break_begin" value="<?= $workerData->break_begin ?>"></td>
                                <td><input required
                                           id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>_break_end"
                                           onchange="recalculateHours('total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>')"
                                           type="time" step="300" name="break_end" value="<?= $workerData->break_end ?>"></td>
                                <td id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>">0:00</td>
                                <td><textarea required name="description"><?= $workerData->description ?></textarea></td>
                                <td><input required type="text" name="project" value="<?php if($workerData != null) echo($workerData->project);?>"></td>
                                <td><input type="checkbox" name="done" <?php if(date("Y-m-d") < $day->day) echo "disabled" ?> <?php if($workerData->done)echo "checked" ?>></td>
                                <td><input required type="password" name="password"></td>
                                <td><input required type="submit" class="btn btn-primary" value="Uložiť"></td>
                            </form>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>

<?php
include "footer.php";