<?php
$active = "";
include "header.php";
include_once "service.php";

if (!isset($_SESSION["user_id"])) {
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
elseif (isset($_GET['d'])){
    if(!(bool)strtotime($_GET['d'])){
        die("Bad format date: " . $_GET["d"]);
    }
    $year = date("Y", strtotime($_GET['d']));
    $month = date("m", strtotime($_GET['d']));
    $week = date("W", strtotime($_GET['d']));
}
$lastweek_week = date("W", strtotime("+".($week-1)." week", strtotime("1.1.".$year)));
$lastweek_year = date("Y", strtotime("+".($week-1)." week", strtotime("1.1.".$year)));
$lastweek_date = date("Y-m-d", strtotime("+".($week-1)." week", strtotime("1.1.".$year)));
$days = $service->GetDaysInWeek($year, $week);
$worker = $service->GetWorkerWithId($_SESSION["user_id"]);
$list_of_days = ["Mon" => "Pondelok", "Tue" => "Utorok", "Wed" => "Streda", "Thu" => "Štvrtok", "Fri" => "Piatok", "Sat" => "Sobota", "Sun" => "Nedeľa"];
include "message_bar.php";
?>
<div class="row mt-1 d-none d-xxl-flex">
    <div class="col-6">
        <form method="get" action="index.php">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xxl-6 col-10 col-xxl-8">
                        <label for="week">Týždeň</label>
                        <input type="week" id="week" name="w" value="<?=$lastweek_year."-W".$lastweek_week?>">
                    </div>
                    <div class="col-2">
                        <input type="submit" name="submit" value="Hľadať">
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="row mt-1 d-xxl-none">
    <div class="col-12">
        <form method="get" action="index.php">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <label for="day">Týždeň (ľubovoľný deň z neho):</label>
                    </div>
                    <div class="col-12">
                        <input type="date" id="day" name="d" value="<?=$lastweek_date?>">
                    </div>
                    <div class="col-12">
                        <input type="submit" name="submit" value="Hľadať" class="float-start">
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="row worker_<?= $worker->id ?> mb-1">
    <h1><strong><?= $worker->GetFullName() ?></strong></h1>
</div>
<div class="row">
    <div class="col" style="overflow-x: auto">
        <div class="container-fluid">
            <div class="row d-none d-xxl-flex">
                <div class="col-xxl-1"><strong>Deň</strong></div>
                <div class="col-xxl-1"><strong>Začiatočný čas</strong></div>
                <div class="col-xxl-1"><strong>Konečný čas</strong></div>
                <div class="col-xxl-1"><strong>Začiatok pauzy</strong></div>
                <div class="col-xxl-1"><strong>Koniec pauzy</strong></div>
                <div class="col-xxl-1"><strong>Celkový čas</strong></div>
                <div class="col-xxl-2"><strong>Náplň práce</strong></div>
                <div class="col-xxl-2"><strong>Projekt</strong></div>
                <div class="col-xxl-1"><strong>Vykonané</strong></div>
                <div class="col-xxl-1"><strong>Odoslať</strong></div>
                <div class="col-xxl-1"></div>
            </div>
            <?php
            foreach ($days as $day) {
                $closed = $service->WorkerHasDayClosed($worker->id, $day->day);
                $workerData = $service->GetWorkerWorkDay($worker->id, $day);
                $projectData = $service->GetProjectDataForWorkday($workerData->id);
                ?>
                <form method="post" action="submit_workday.php" onkeydown="return event.key != 'Enter';"
                      onsubmit="return recalculateHours('total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>')&&(verifyProjectInputs('projects_<?= $day->day ?>',
                              'total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>',
                              'project_<?= $worker->id . "_" . $day->day_of_week ?>',
                              'done_<?= $worker->id . "_" . $day->day_of_week ?>') ||
                              verifyProjectInputs('projects_m_<?= $day->day ?>',
                              'total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>',
                              'project_<?= $worker->id . "_" . $day->day_of_week ?>',
                              'done_<?= $worker->id . "_" . $day->day_of_week ?>'))">
                    <div class="row <?= "day_" . $day->day . " worker_" . $worker->id ?> table-row-index">
                        <div class="col-xxl-1 col-12"><?= $list_of_days[date("D", strtotime($day->day))] . " " . date("d.m.Y", strtotime($day->day)) ?></div>

                        <input required type="hidden" name="work_day_id" value="<?= $day->day ?>">
                        <input required type="hidden" name="worker_id" value="<?= $worker->id ?>">
                        <div class="col-2 d-xxl-none mb-xxl-0 mb-1"><label for="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>_begin_time"><strong>Od:</strong></label></div>
                        <div class="col-xxl-1 col-4 mb-xxl-0 mb-1"><input required
                                   id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>_begin_time"
                                   onchange="recalculateHours('total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>')"
                                   type="time" step="300" name="begin_time"
                                   value="<?= $workerData->begin_time ?>" <?= $closed ? "disabled" : "" ?>></div>
                        <div class="col-2 d-xxl-none mb-xxl-0 mb-1"><label for="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>_end_time"><strong>do:</strong></label></div>
                        <div class="col-xxl-1 col-4 mb-xxl-0 mb-1"><input required id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>_end_time"
                                   onchange="recalculateHours('total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>')"
                                   type="time" step="300" name="end_time"
                                   value="<?= $workerData->end_time ?>" <?= $closed ? "disabled" : "" ?>>
                        </div>
                        <div class="col-2 d-xxl-none mb-xxl-0 mb-1"><label for="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>_break_begin"><strong>Pauza:</strong></label></div>
                        <div class="col-xxl-1 col-4 mb-xxl-0 mb-1"><input
                                    id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>_break_begin"
                                    onchange="recalculateHours('total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>')"
                                    type="time" step="300" name="break_begin"
                                    value="<?= $workerData->break_begin ?>" <?= $closed ? "disabled" : "" ?>></div>
                        <div class="col-2 d-xxl-none mb-xxl-0 mb-1"><label for="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>_break_end"><strong>do:</strong></label></div>
                        <div class="col-xxl-1 col-4 mb-xxl-0 mb-1"><input
                                    id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>_break_end"
                                    onchange="recalculateHours('total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>')"
                                    type="time" step="300" name="break_end"
                                    value="<?= $workerData->break_end ?>" <?= $closed ? "disabled" : "" ?>></div>
                        <div class="col-6 d-xxl-none mb-xxl-0 mb-1"><strong>Čas celkom:</strong></div>
                        <div class="col-xxl-1 col-6 mb-xxl-0 mb-1" id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>" style="color: green; font-weight: bold">00:00</div>
                        <div class="col-12 d-xxl-none"><label for="textarea<?= $worker->id . "_" . $day->day_of_week ?>"><strong>Popis práce:</strong></label></div>
                        <div class="col-xxl-2 col-12 mb-xxl-0 mb-1"><textarea style="width: 100%; height: 100%" id="textarea<?= $worker->id . "_" . $day->day_of_week ?>" required
                                      name="description" <?= $closed ? "disabled" : "" ?>><?= $workerData->description ?></textarea>
                        </div>
                        <div class="col-xxl-2 col-12 d-none d-xxl-block">
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
                                            <div class="col-4">
                                                <label for="project_<?= $worker->id . "_" . $day->day_of_week . "_" . $project->id ?>"><?= $project->name ?></label>
                                            </div>
                                            <div class="col-8">
                                                <input step="300" type="time" class="projects_<?= $day->day ?>"
                                                       name="projects[<?= $project->id ?>]"
                                                       id="project_<?= $worker->id . "_" . $day->day_of_week . "_" . $project->id ?>"
                                                       value="<?= $projectData[$project->id] ?? null ?>" <?= $closed ? "disabled" : "" ?>>
                                                <button type="button" style="border: none" title="Robil som len na tomto projekte" onclick="setAllDay1Project('project_<?= $worker->id . "_" . $day->day_of_week . "_" . $project->id ?>',
                                                        'total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>', 'projects_<?= $day->day ?>')">&#128504;</button>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 d-xxl-none"><strong>Projekty:</strong></div>
                        <div class="d-xxl-none container-fluid">
                            <?php foreach ($projects as $project) {
                            ?>
                            <div class="row mb-1">
                                <div class="col-6">
                                    <label for="project_m_<?= $worker->id . "_" . $day->day_of_week . "_" . $project->id ?>"><?= $project->name ?></label>
                                </div>
                                <div class="col-6">
                                    <input step="300" type="time" class="projects_m_<?= $day->day ?>"
                                           name="projects_m[<?= $project->id ?>]"
                                           id="project_m_<?= $worker->id . "_" . $day->day_of_week . "_" . $project->id ?>"
                                           value="<?= $projectData[$project->id] ?? null ?>" <?= $closed ? "disabled" : "" ?>>
                                    <button type="button">&#128504;</button>
                                </div>
                            </div>
                            <?php
                            }
                            ?>
                        </div>
                        <div class="col-4 d-xxl-none mb-xxl-0 mb-1"><label for="done_<?= $worker->id . "_" . $day->day_of_week ?>"><strong>Vykonané:</strong></label></div>
                        <div class="col-xxl-1 col-2"><input type="checkbox" id="done_<?= $worker->id . "_" . $day->day_of_week ?>"
                                   name="done" <?php if (date("Y-m-d") < $day->day || $closed) echo "disabled" ?> <?php if ($workerData->done) echo "checked" ?>>
                        </div>
                        <div class="d-xxl-none col-6" id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>"><?php if (!$closed) { ?><input
                                    required
                                    type="submit"
                                    class="btn btn-primary"
                                    name="submit_m"
                                    value="Uložiť" "><?php } ?>
                        </div>
                        <div class="d-none col-1 d-xxl-block" id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>"><?php if (!$closed) { ?><input
                                    required
                                    type="submit"
                                    class="btn btn-primary"
                                    name="submit"
                                    value="Uložiť" "><?php } ?>
                        </div>
                        <script>
                             recalculateHours("total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>");
                        </script>
                    </div>
                </form>
                <?php
            }
            ?>
        </div>
    </div>
</div>