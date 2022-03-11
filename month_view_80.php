<?php
$active = "month";
include "header.php";
include_once "service.php";

$service = new Service();
$worker_id = $_GET["id"];
$worker_name = $service->GetWorkerNameWithId($worker_id);
$year = substr($_GET["m"], 0, 4);
$month = substr($_GET["m"], 5, 2);

$list_of_dates = array();
$list_of_days = ["Mon" => "po", "Tue" => "ut", "Wed" => "st", "Thu" => "št", "Fri" => "pi", "Sat" => "so", "Sun" => "ne"];
$start_date = "01-" . $month . "-" . $year;
$start_time = strtotime($start_date);
$end_time = strtotime("+1 month", $start_time);
for ($i = $start_time; $i < $end_time; $i += 86400) {
    $list_of_dates[] = array(date('d.m.Y', $i), date('D', $i), date("Y-m-d", $i));
}
$done_days = $service->GetDoneWorkerWorkDays($worker_id, $month, $year);
$total_time = array();
$sum_unitl_now = "00:00";
$overflow=false;
$set_overflow=false;
include "message_bar.php";
?>

    <div class="row noprint mb-1" style="margin-top: 1em">
        <div class="col-3">
            <form method="get" action="month_view_80.php">
                <input type="hidden" name="id" value="<?= $worker_id ?>">
                <label for="month" class="mb-1">mesiac/rok:</label>
                <input type="month" class="mb-1" id="month" name="m" value="<?= $_GET["m"] ?>">
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
                        $row['id'] = -1;
                        $row['begin_time'] = null;
                        $row['end_time'] = null;
                        $row['break_begin'] = null;
                        $row['break_end'] = null;
                        $row['project'] = null;
                        $row['description'] = null;
                    }
                    if ($day == 'nedeľa')
                        $class = 'sun';
                    else
                        $class = 'ord';
                    if(!$overflow) {
                        $day_time = $service->CalculateDayTime($row['begin_time'], $row['end_time'], $row['break_begin'], $row['break_end']);
                        $sum_unitl_now = $service->CalculateTotalTime(array($day_time, $sum_unitl_now));
                        if ($sum_unitl_now >= "80:00") {
                            $day_time = $service->GetReducedTimeTo80($day_time, $sum_unitl_now);
                            $sum_unitl_now = "80:00";
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
                    echo("<td class='export_table_cell " . $class . " last'>" . ($overflow ? "" :($row["id"] == -1 ? "" : $service->GetProjectsStringForWorkersWorkday($row["id"]))) . "</td>");
                    echo("</tr>");
                }
                ?>
                <tr>
                    <td colspan="5" class="sum export_table_cell"><strong>Suma:</strong></td>
                    <td class="sum export_table_cell"><strong><?= $service->CalculateTotalTime($total_time) ?></strong></td>
                    <td class="sum export_table_cell" colspan="2"></td>
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
        <div class="col-1">
            <button class="btn btn-primary" id="pdf" onclick="window.print()">Export</button>
        </div>
    </div>

<?php
include "footer.php";
