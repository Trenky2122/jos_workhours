<?php
include "header.php";
include "service.php";
$service = new Service();
$year = date("Y");
$month = date("m");
$week = date("W");

if (isset($_GET['w'])) {
    $arr = explode("-W", $_GET['w']);
    if (!isset($arr[1]) || !isset($arr[0])) {
        die("Bad format week: " . $_GET["w"]);
    }
    if (!is_numeric($arr[0]) || !is_numeric($arr[1])) {
        die("Bad format week: " . $_GET["w"]);
    }
    $week = $arr[1];
    $year = $arr[0];
}

$days = $service->GetDaysInWeek($year, $week);
$workers = $service->GetAllWorkers();
include "message_bar.php";
?>
    <div class="row mt-2">
        <div class="col-3">
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
        <div class="col-3">
            <label for="day_select">Filter deň</label>
            <select id="day_select" onchange="reloadFilter()">
                <option value="0">Celý týždeň</option>
                <?php
                foreach ($days as $day) {
                    ?>
                    <option value="<?= "day_" . $day->day ?>"><?= $day->day_of_week . " " . date("d.m.Y", strtotime($day->day)) ?></option>
                    <?php
                }
                ?>
            </select>
        </div>
        <div class="col-6">
            <form method="get" action="index.php">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xl-6 col-10 col-lg-8">
                            <label for="week">Týždeň</label>
                            <input type="week" id="week" name="w">
                        </div>
                        <div class="col-2">
                            <input type="submit" name="submit" value="Hľadať">
                        </div>
                    </div>
                </div>
            </form>
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
                    <th>Vykonané</th>
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
                        <td colspan="2"><a href="default_change.php?id=<?= $worker->id ?>" class="btn btn-primary">upraviť
                                default</a></td>
                        <td colspan="2"><a href="password_change.php?id=<?= $worker->id ?>" class="btn btn-primary"> zmeniť
                                heslo</a></td>
                        <td colspan="6"><a href="month_view.php?id=<?= $worker->id ?>&m=<?= $year ?>-<?= $month ?>"
                                           class="btn btn-primary">mesačný prehľad</a></td>
                    </tr>
                    <?php
                    foreach ($days as $day) {
                        $workerData = $service->GetWorkerWorkDay($worker->id, $day);
                        ?>
                        <tr class="<?= "day_" . $day->day . " worker_" . $worker->id ?> table-row">
                            <td><?= date("d.m.Y", strtotime($day->day)) ?></td>
                            <form method="post" action="submit_workday.php">
                                <input required type="hidden" name="work_day_id" value="<?= $day->day ?>">
                                <input required type="hidden" name="worker_id" value="<?= $worker->id ?>">
                                <td><input required
                                           id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>_begin_time"
                                           onchange="recalculateHours('total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>')"
                                           type="time" step="300" name="begin_time"
                                           value="<?= $workerData->begin_time ?>"></td>
                                <td><input required id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>_end_time"
                                           onchange="recalculateHours('total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>')"
                                           type="time" step="300" name="end_time" value="<?= $workerData->end_time ?>">
                                </td>
                                <td><input
                                            id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>_break_begin"
                                            onchange="recalculateHours('total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>')"
                                            type="time" step="300" name="break_begin"
                                            value="<?= $workerData->break_begin ?>"></td>
                                <td><input
                                            id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>_break_end"
                                            onchange="recalculateHours('total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>')"
                                            type="time" step="300" name="break_end"
                                            value="<?= $workerData->break_end ?>"></td>
                                <td id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>">0:00</td>
                                <td><textarea required name="description"><?= $workerData->description ?></textarea>
                                </td>
                                <td>
                                    <a class="btn btn-primary" data-toggle="collapse" href="#project_<?= $workerData->id ?>" role="button" aria-expanded="false" aria-controls="project_<?= $workerData->id ?>">
                                        Rozbaliť projekty
                                    </a>
                                    <div class="collapse" id="project_<?= $workerData->id ?>">
                                        <div class="card card-body">
                                            Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident.
                                        </div>
                                    </div>
                                    <input required type="text" name="project"
                                           value="<?php if ($workerData != null) echo($workerData->project); ?>">
                                </td>
                                <td><input type="checkbox"
                                           name="done" <?php if (date("Y-m-d") < $day->day) echo "disabled" ?> <?php if ($workerData->done) echo "checked" ?>>
                                </td>
                                <td><input required type="password" name="password"></td>
                                <td id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>"><input required
                                                                                                       type="submit"
                                                                                                       class="btn btn-primary"
                                                                                                       value="Uložiť">
                                </td>
                                <script>
                                    document.getElementById("total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>").addEventListener("load", recalculateHours("total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>"))
                                </script>
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