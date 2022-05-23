<?php
$active = "week";
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
$list_of_days = ["Mon" => "Pondelok", "Tue" => "Utorok", "Wed" => "Streda", "Thu" => "Štvrtok", "Fri" => "Piatok", "Sat" => "Sobota", "Sun" => "Nedeľa"];
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
                </div>
                <?php
                foreach ($workers as $worker) {
                    $workdays = $service->GetWorkerWorkDays($worker->id, $days[0]->day, $days[5]->day);
                    $clockify_entries = array();
                    if ($worker->clockify_api_key != "") {
                        $builder = new JDecool\Clockify\ClientBuilder();
                        $client = $builder->createClientV1($worker->clockify_api_key);
                        $apiFactory = new JDecool\Clockify\ApiFactory($client);
                        $userApi = $apiFactory->userApi();

                        $user = $userApi->current();
                        $clockify_entries = $client->get("workspaces/" . $user->activeWorkspace()
                            . "/user/" . $user->id() . "/time-entries?page-size=5000&start=" . date("Y-m-d\TH:i:s\Z", strtotime($days[0]->day)) .
                            "&end=" . date("Y-m-d\TH:i:s\Z", strtotime($days[0]->day . " +1 week")));
                        foreach ($workdays as $date => $day) {
                            if ($day["break_end"] == null) {
                                $clockify_entries[] = $service->DayFromDbToClockifyFormat($day["work_day_date"], $day["begin_time"], $day["end_time"], $day["description"], $day["id"]);
                            } else {
                                $clockify_entries[] = $service->DayFromDbToClockifyFormat($day["work_day_date"], $day["begin_time"], $day["break_begin"], $day["description"], $day["id"]);
                                $clockify_entries[] = $service->DayFromDbToClockifyFormat($day["work_day_date"], $day["break_end"], $day["end_time"], "", $day["id"]);
                            }
                        }
                        $workdays = $service->ClockifyEntriesToDayFormat($clockify_entries);
                    }

                    ?>
                    <div class="table-row table-row-index row worker_<?= $worker->id ?> worker_name">
                        <div class="col-xxl-1"><strong><?= $worker->GetFullName() ?></strong></div>
                        <div class="col"><a href="month_view.php?id=<?= $worker->id ?>&m=<?= $year ?>-<?= $month ?>"
                                            class="btn btn-primary">Mesačný prehľad</a></div>
                    </div>
                    <?php
                    foreach ($days as $day) {
                        $workerData = array();
                        if (!isset($workdays[$day->day])) {
                            $workerData = array();
                            $workerData["begin_time"] = null;
                            $workerData["end_time"] = null;
                            $workerData["break_begin"] = null;
                            $workerData["break_end"] = null;
                            $workerData["description"] = null;
                            $workerData["id"] = null;
                            $workerData["done"] = null;
                        } else {
                            $workerData = $workdays[$day->day];
                        }
                        ?>
                        <div class="<?= "day_" . $day->day . " worker_" . $worker->id ?> table-row row table-row-index <?php if (date("D", strtotime($day->day)) == 'Mon') echo "monday"; ?>">
                            <div class="col-xxl-1 col-12"><?= $list_of_days[date("D", strtotime($day->day))] . "<br>" . date("d.m.Y", strtotime($day->day)) ?></div>
                            <div class="col-2 d-xxl-none mb-xxl-0 mb-1"><strong>Od:</strong></div>
                            <div class="col-xxl-1 col-4 mb-xxl-0 mb-1"><?= substr($workerData["begin_time"], 0, 5) ?></div>
                            <div class="col-2 d-xxl-none mb-xxl-0 mb-1"><strong>do:</strong></div>
                            <div class="col-xxl-1 col-4 mb-xxl-0 mb-1"><?= substr($workerData["end_time"], 0, 5) ?></div>
                            <div class="col-2 d-xxl-none mb-xxl-0 mb-1"><strong>Pauza:</strong></div>
                            <div class="col-xxl-1 col-4 mb-xxl-0 mb-1"><?= substr($workerData["break_begin"], 0, 5) ?></div>
                            <div class="col-2 d-xxl-none mb-xxl-0 mb-1"><strong>do:</strong></div>
                            <div class="col-xxl-1 col-4 mb-xxl-0 mb-1"><?= substr($workerData["break_end"], 0, 5) ?></div>
                            <div class="col-6 d-xxl-none mb-xxl-0 mb-1"><strong>Čas celkom:</strong></div>
                            <div class="col-6 d-xxl-none mb-xxl-0 mb-1"><?= $service->CalculateDayTime($workerData["begin_time"], $workerData["end_time"], $workerData["break_begin"], $workerData["break_end"]) ?></div>
                            <div class="col-12 d-xxl-none"><strong>Popis práce:</strong></div>
                            <div class="col-xxl-2 col-12 mb-xxl-0 mb-1"><?= $workerData["description"] ?></div>
                            <div class="col-xxl-2 col-12 d-none d-xxl-block">
                                <a class="btn btn-primary" data-toggle="collapse"
                                   href="#project_<?= $worker->id . "_" . $day->day_of_week ?>" role="button"
                                   aria-expanded="false"
                                   aria-controls="project_<?= $worker->id . "_" . $day->day_of_week ?>">
                                    Rozbaliť projekty
                                </a>
                                <div class="collapse" id="project_<?= $worker->id . "_" . $day->day_of_week ?>">
                                    <div class="container-fluid">
                                        <?php
                                        $projectsData = $service->GetWorkedProjectsForWorkday($day->day, $worker->id, $clockify_entries);
                                        foreach ($projectsData as $name => $time) {
                                            ?>
                                            <div class="row">
                                                <div class="col-6">
                                                    <p><?= $name . ": " ?></p>
                                                </div>
                                                <div class="col-6">
                                                    <p><?= $service->NormalizeTime($time) ?></p>
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
                                <?php foreach ($projectsData as $name => $time) {
                                    ?>
                                    <div class="row mb-1">
                                        <div class="col-6">
                                            <?= $name ?>
                                        </div>
                                        <div class="col-6">
                                            <?= $service->NormalizeTime($time)?>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class="col-4 d-xxl-none mb-xxl-0 mb-1"><strong>Vykonané:</strong></div>
                            <div class="col-xxl-1 col-4 mb-xxl-0 mb-1"><input type="checkbox"
                                                          name="done"
                                                          onclick="return false" <?php if ($workerData["done"]) echo "checked" ?>
                                                          style="">
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>

<?php
include "footer.php";