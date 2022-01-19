<?php
include "header.php";
include_once "service.php";

if(!isset($_SESSION["user_id"])){
    header("Location: login.php?err=1");
    die();
}

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
$worker = $service->GetWorkerWithId($_SESSION["user_id"]);
$list_of_days = ["Mon"=>"Pondelok", "Tue"=>"Utorok", "Wed"=>"Streda", "Thu"=>"Štvrtok", "Fri"=>"Piatok", "Sat"=>"Sobota", "Sun"=>"Nedeľa"];
include "message_bar.php";
?>

<div class="row">
        <div class="col" style="overflow-x: auto">
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
                    <tr class="table-row worker_<?= $worker->id ?> worker_name">
                        <td><strong><?= $worker->GetFullName() ?></strong></td>
                        <td colspan="2"><a href="default_change.php?id=<?= $worker->id ?>" class="btn btn-primary">upraviť
                                default</a></td>
                        <td colspan="2"><a href="password_change.php?id=<?= $worker->id ?>" class="btn btn-primary">
                                zmeniť
                                heslo</a></td>
                        <td colspan="6"><a href="month_view.php?id=<?= $worker->id ?>&m=<?= $year ?>-<?= $month ?>"
                                           class="btn btn-primary">mesačný prehľad</a></td>
                    </tr>
                    <?php
                    foreach ($days as $day) {
                        $workerData = $service->GetWorkerWorkDay($worker->id, $day);
                        $projectData = $service->GetProjectDataForWorkday($workerData->id);
                        ?>
                        <tr class="<?= "day_" . $day->day . " worker_" . $worker->id ?> table-row">
                            <td><?= $list_of_days[date("D", strtotime($day->day))]."<br>".date("d.m.Y", strtotime($day->day)) ?></td>
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
                                    <a class="btn btn-primary" data-toggle="collapse"
                                       href="#project_<?= $worker->id . "_" . $day->day_of_week ?>" role="button"
                                       aria-expanded="false"
                                       aria-controls="project_<?= $worker->id . "_" . $day->day_of_week ?>">
                                        Rozbaliť projekty
                                    </a>
                                    <div class="collapse" id="project_<?= $worker->id . "_" . $day->day_of_week ?>">
                                        <div class="container-fluid">
                                            <?php $projects = $service->GetRelevantProjectsForDay($workerData->id);
                                            foreach ($projects as $project) {
                                                ?>
                                                <div class="row" style="padding: 5px;">
                                                    <div class="col-4">
                                                        <label for="project_<?= $worker->id . "_" . $day->day_of_week . "_" . $project->id ?>"><?= $project->name ?></label>
                                                    </div>
                                                    <div class="col-8">
                                                        <input type="time" name="projects[<?= $project->id ?>]"
                                                               id="project_<?= $worker->id . "_" . $day->day_of_week . "_" . $project->id ?>"
                                                               value="<?= $projectData[$project->id] ?? "00:00:00" ?>">
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </td>
                                <td><input type="checkbox"
                                           name="done" <?php if (date("Y-m-d") < $day->day) echo "disabled" ?> <?php if ($workerData->done) echo "checked" ?>>
                                </td>
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
                ?>
                </tbody>
            </table>
        </div>
    </div>