<?php
include "header.php";
include_once "service.php";
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
$list_of_days = ["Mon"=>"Pondelok", "Tue"=>"Utorok", "Wed"=>"Streda", "Thu"=>"Štvrtok", "Fri"=>"Piatok", "Sat"=>"Sobota", "Sun"=>"Nedeľa"];
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
            <form method="get" action="overview.php">
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
        <div class="col" style="overflow: auto">
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
                    <th></th>
                </tr>
                </thead>
                <tbody>

                <?php
                foreach ($workers as $worker) {
                    ?>
                    <tr class="table-row worker_<?= $worker->id ?> worker_name">
                        <td><strong><?= $worker->GetFullName() ?></strong></td>
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
                                <td><?= substr($workerData->begin_time,0,  5) ?></td>
                                <td><?= substr($workerData->end_time,0,  5) ?></td>
                                <td><?= substr($workerData->break_begin,0,  5) ?></td>
                                <td><?= substr($workerData->break_end,0,  5) ?></td>
                                <td><?= $service->CalculateDayTime($workerData->begin_time, $workerData->end_time, $workerData->break_begin, $workerData->break_end) ?></td>
                                <td><?= $workerData->description ?></td>
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
                                                <div class="row">
                                                    <div class="col-6">
                                                        <p><?= $project->name ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <p><?php if(isset($projectData[$project->id])) echo(substr($projectData[$project->id],0, 5)); else echo("00:00") ?></p>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </td>
                                <td><input type="checkbox"
                                           name="done" onclick="return false" <?php if ($workerData->done) echo "checked" ?> style="">
                                </td>
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