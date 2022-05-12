<?php
$active = "month";
include "header.php";
include_once "service.php";
require_once './vendor/autoload.php';

$service = new Service();
$worker_id = $_SESSION["user_id"];
if(isset($_GET["id"])){
    $worker_id = $_GET["id"];
}
$worker = $service->GetWorkerWithId($worker_id);
$worker_name = $worker->GetFullName();
$year = date("Y");
$month = date("m");
if(isset($_GET["m"])){
    $year = substr($_GET["m"], 0, 4);
    $month = substr($_GET["m"], 5, 2);
}
$list_of_dates = array();
$list_of_days = ["Mon" => "po", "Tue" => "ut", "Wed" => "st", "Thu" => "št", "Fri" => "pi", "Sat" => "so", "Sun" => "ne"];
$start_date = "01-" . $month . "-" . $year;
$start_time = strtotime($start_date);
$end_time = strtotime("+1 month", $start_time);
$closed_month_data = $service->GetWorkerMonthClosedData($worker_id, $year."-".$month);
$clockify_data = "";
for ($i = $start_time; $i < $end_time; $i += 86400) {
    $list_of_dates[] = array(date('d.m.Y', $i), date('D', $i), date("Y-m-d", $i));
}
$done_days = $service->GetDoneWorkerWorkDays($worker_id, $month, $year);
$entries = array();
if($closed_month_data && $closed_month_data["to_be_reworked"]==0){
    $entries = json_decode($closed_month_data["clockify_data"]);
}
else{
    if($worker->clockify_api_key != "") {
        $builder = new JDecool\Clockify\ClientBuilder();
        $client = $builder->createClientV1($worker->clockify_api_key);
        $apiFactory = new JDecool\Clockify\ApiFactory($client);
        $userApi = $apiFactory->userApi();

        $user = $userApi->current();
        $entries = $client->get("workspaces/" . $user->activeWorkspace()
            . "/user/" . $user->id() . "/time-entries?start=" . date("Y-m-d\TH:i:s\Z", strtotime($start_date)) .
            "&end=" . date("Y-m-d\TH:i:s\Z", strtotime($start_date . " +1 month")));
        $clockify_data = json_encode($entries);
    }
}

foreach($done_days as $date=>$day){
    if($day["break_end"] == null){
        $entries[] = $service->DayFromDbToClockifyFormat($day["work_day_date"], $day["begin_time"], $day["end_time"], $day["description"], $day["id"]);
    }
    else{
        $entries[] = $service->DayFromDbToClockifyFormat($day["work_day_date"], $day["begin_time"], $day["break_begin"], $day["description"], $day["id"]);
        $entries[] = $service->DayFromDbToClockifyFormat($day["work_day_date"], $day["break_end"], $day["end_time"], "", $day["id"]);
    }
}
$worker_workdays = $service->ClockifyEntriesToDayFormat($entries);
$done_days = $worker_workdays;

$total_time = array();
$sum_unitl_now = "00:00";
$overflow=false;
$set_overflow=false;
$max_sum = null;
if(isset($_GET["limit"])){
    $time_split = explode(":", $_GET["limit"]);
    if(count($time_split)== 2){
        if(is_numeric($time_split[0])&&is_numeric($time_split[1])){
            for($i=count(str_split($time_split[0])); $i<3; $i++){
                $time_split[0]="0".$time_split[0];
            }
            $max_sum=$time_split[0].":".$time_split[1];
        }
    }
}
function normalizeTime($time): string
{
    $timeSplit = explode(":", $time);
    $hours = intval($timeSplit[0]);
    return $hours.":".$timeSplit[1];
}
include "message_bar.php";
?>

    <div class="row noprint mb-1" style="margin-top: 1em">
        <div class="col-3">
            <form method="get" action="month_view.php">
                <input type="hidden" name="id" value="<?= $worker_id ?>">
                <label for="month" class="mb-1">mesiac/rok:</label>
                <input type="month" class="mb-1" id="month" name="m" value="<?=$year."-".$month?>">
                <input type="submit" name="submit" value="Hľadať">
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <table id="example" class="export_table">
                <thead>
                <tr>
                    <td colspan="6" rowspan="3" class="export_table_cell">Mesiac: <?= $month ?>/<?= $year ?></td>
                    <td colspan="3" class="last export_table_cell">Pracovný čas</td>
                </tr>
                <tr>
                    <td colspan="3" class="last export_table_cell">Firma: JOS GROUP s.r.o.</td>
                </tr>
                <tr>
                    <td colspan="3" class="last export_table_cell">Zamestnanec: <?= $worker_name ?></td>
                </tr>
                <tr>
                    <th scope="col" class="sun export_table_cell">Dátum</th>
                    <th scope="col" class="sun export_table_cell">Deň</th>
                    <th scope="col" class="sun export_table_cell">Začiatok</th>
                    <th scope="col" class="sun export_table_cell">Koniec</th>
                    <th scope="col" class="sun export_table_cell">Pauza</th>
                    <th scope="col" class="sun export_table_cell">Celkom</th>
                    <th scope="col" class="sun export_table_cell">Náplň práce</th>
                    <th scope="col" class="sun last export_table_cell">Projekt</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($list_of_dates as $date_day) {
                    if($set_overflow)
                        $overflow=true;
                    $date = $date_day[0];
                    $day = $list_of_days[$date_day[1]];
                    $date2 = date('Y-m-d', strtotime($date));
                    $row = array();
                    if (isset($done_days[$date2])) {
                        $row = $done_days[$date2];
                    } else {
                        $row["id"] = -1;
                        $row["begin_time"] = null;
                        $row["end_time"] = null;
                        $row["break_begin"] = null;
                        $row["break_end"] = null;
                        $row["description"] = null;
                        $row["projectString"] = null;
                    }
                    if ($day == 'nedeľa')
                        $class = 'sun';
                    else
                        $class = 'ord';
                    if(!$overflow) {
                        $day_time = $service->CalculateDayTime($row['begin_time'], $row['end_time'], $row['break_begin'], $row['break_end']);
                        $sum_unitl_now = $service->CalculateTotalTime(array($day_time, $sum_unitl_now));
                        if ($max_sum != null && $sum_unitl_now >= $max_sum) {
                            $day_time = $service->GetReducedTimeToMaximum($day_time, $sum_unitl_now, $max_sum);
                            $sum_unitl_now = $max_sum;
                            $set_overflow = true;
                            $row["break_begin"] = null;
                            $row["break_end"] = null;
                            $row["end_time"] = "16:00:00";
                            $row["begin_time"] = $service->CalculateDayTime($day_time.":00", "16:00:00", null, null);
                        }
                        $total_time[] = $day_time;
                    }
                    echo("<tr><td class='export_table_cell " . $class . "'>" . $date . "</td>");
                    echo("<td class='export_table_cell " . $class . "'>" . $day . "</td>");
                    echo("<td class='export_table_cell " . $class . "'>" . ($overflow ? "" :substr($row['begin_time'], 0, 5) ). "</td>");
                    echo("<td class='export_table_cell " . $class . "'>" . ($overflow ? "" :substr($row['end_time'], 0, 5)) . "</td>");
                    $pause = $service->CalculateDayTime($row["break_begin"], $row["break_end"], null, null);
                    echo("<td class='export_table_cell " . $class . "'>" . ($overflow ? "" :$pause) . "</td>");

                    echo("<td class='export_table_cell " . $class . " total'>" . ($overflow ? "" :$day_time) . "</td>");
                    echo("<td class='export_table_cell " . $class . "'>" . ($overflow ? "" :$row['description']) . "</td>");
                    echo("<td class='export_table_cell " . $class . " last'>" . ($overflow ? "" :$row["projectString"])."</td>");
                    echo("</tr>");
                }
                ?>
                <tr>
                    <td colspan="5" class="sum export_table_cell"><strong>Suma:</strong></td>
                    <td class="sum export_table_cell"><strong><?= normalizeTime($service->CalculateTotalTime($total_time)) ?></strong></td>
                    <td class="sum export_table_cell" colspan="2">
                        <?php
                        if(!$overflow) {
                            $projectData = $service->GetProjectDataForWorker($worker_id, $list_of_dates[0][2], end($list_of_dates)[2]);
                            foreach ($projectData as $key => $value) {
                                echo "<strong>" . $key . "</strong>: " . normalizeTime($value) . "&emsp;&emsp;&emsp;&emsp;";
                            }
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="export_table_cell bot-three" style='border-right: none;border-top: none;border-bottom: none;'>
                        Dátum: <?= $list_of_dates[count($list_of_dates) - 1][0] ?></td>
                    <td colspan="3" class="export_table_cell" style='border: none;'>Podpis zamestnanca</td>
                    <td colspan="2" class="export_table_cell last" style='border-left: none;border-top: none;border-bottom: none'>Pečiatka a podpis
                        zamestnávateľa
                    </td>
                </tr>
                <tr>
                    <td colspan="8" class="export_table_cell last sun bot-three" style="border-top: none; padding: 20px;border-right: 2px solid black;">
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row noprint mt-1 mb-1">
        <div class="col-12">
            <button class="btn btn-primary" id="pdf" onclick="window.print()">Export</button>
        </div>
        <?php
        $rework = false;
        $closed = $closed_month_data && $closed_month_data["to_be_reworked"]==0;
        $rework = $closed_month_data && $closed_month_data["to_be_reworked"]==1;
        if (!$closed && !$rework && ($_SESSION["user_id"] == $worker_id || $_SESSION["user_role"] == 1)) { ?>
            <div class="col-12 mt-1">
                <form action="submit_close_month.php" method="post">
                    <input type="hidden" value="<?= $worker_id ?>" name="worker_id">
                    <input type="hidden" value="<?= $year."-".$month ?>" name="month">
                    <input type="hidden" value="<?= $clockify_data ?>" name="clockify_data">
                    <input class="btn btn-primary" name="submit" value="Uzavrieť mesiac" type="submit">
                </form>
            </div>
        <?php } else if ($closed) { ?>
            <div class="col-12 mt-1" style="max-width: 400px">
                <div class="alert alert-info" role="alert">
                    Mesiac bol uzavretý.
                </div>
            </div>
            <?php
            if ($_SESSION["user_role"] == 1) {
                ?>
                <div class="col-12 mt-1" style="max-width: 400px">
                    <form action="submit_rework_month.php" method="post">
                        <div class="container-fluid">
                            <input type="hidden" value="<?= $worker_id ?>" name="worker_id">
                            <input type="hidden" value="<?= $year."-".$month ?>" name="month">
                            <div class="row">
                                <div class="col-6">
                                    <label for="explanation" style="float: right">Zdôvodnenie:</label>
                                </div>
                                <div class="col-6">
                                    <textarea name="explanation" id="explanation"></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6"></div>
                                <div class="col-6"><input class="btn btn-primary" name="submit"
                                                          value="Poslať mesiac na opravu" type="submit"></div>
                            </div>
                        </div>
                    </form>
                </div>
                <?php
            }
        }
        if ($rework) {
            ?>
            <div class="col-12 mt-1">
                <form action="submit_close_month_correction.php" method="post">
                    <input type="hidden" value="<?= $worker_id ?>" name="worker_id">
                    <input type="hidden" value="<?= $year."-".$month ?>" name="month">
                    <input type="hidden" value="<?= $clockify_data ?>" name="clockify_data">
                    <input class="btn btn-primary" name="submit" value="Uzavrieť mesiac (oprava)" type="submit">
                </form>
            </div>
            <?php
        }
        ?>
        <div class="row noprint mt-1">
            <div class="col-12">
                <form method="get">
                    <input type="hidden" value="<?=$worker_id?>" name="id">
                    <input type="hidden" value="<?=$year.'-'.$month?>" name="m">
                    <label for="limit">Limit (h:mm)</label>
                    <input type="text" name="limit" id="limit" />
                    <input type="submit" class="btn btn-primary" value="Nastaviť limit na maximálny počet hodín" />
                </form>
            </div>
        </div>
    </div>

<?php
include "footer.php";
