<?php
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
include "message_bar.php";
?>

    <div class="row noprint mb-1" style="margin-top: 1em">
        <div class="col-1">
            <a href="index.php" class="btn btn-primary"> späť</a>
        </div>
        <div class="col-3">
            <form method="get" action="month_view.php">
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
                    echo("<tr><td class='export_table_cell " . $class . "'>" . $date . "</td>");
                    echo("<td class='export_table_cell " . $class . "'>" . $day . "</td>");
                    echo("<td class='export_table_cell " . $class . "'>" . substr($row['begin_time'], 0, 5) . "</td>");
                    echo("<td class='export_table_cell " . $class . "'>" . substr($row['end_time'], 0, 5) . "</td>");
                    $pause = $service->CalculateDayTime($row["break_begin"], $row["break_end"], null, null);
                    echo("<td class='export_table_cell " . $class . "'>" . $pause . "</td>");
                    $day_time = $service->CalculateDayTime($row['begin_time'], $row['end_time'], $row['break_begin'], $row['break_end']);
                    $total_time[] = $day_time;
                    echo("<td class='export_table_cell " . $class . " total'>" . $day_time . "</td>");
                    echo("<td class='export_table_cell " . $class . "'>" . $row['description'] . "</td>");
                    echo("<td class='export_table_cell " . $class . " last'>" . ($row["id"] == -1 ? "" : $service->GetProjectsStringForWorkersWorkday($row["id"])) . "</td>");
                    echo("</tr>");
                }
                ?>
                <tr>
                    <td colspan="5" class="sum export_table_cell"><strong>Suma:</strong></td>
                    <td class="sum export_table_cell"><strong><?= $service->CalculateTotalTime($total_time) ?></strong>
                    </td>
                    <td class="sum export_table_cell" colspan="2">
                        <?php
                        $projectData = $service->GetProjectDataForWorker($worker_id, $list_of_dates[0][2], end($list_of_dates)[2]);
                        foreach ($projectData as $key => $value) {
                            echo "<strong>" . $key . "</strong>: " . $value . "&emsp;&emsp;&emsp;&emsp;";
                        }
                        ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="export_table_cell bot-three"
                        style='border-right: none;border-top: none;border-bottom: none;'>
                        Dátum: <?= $list_of_dates[count($list_of_dates) - 1][0] ?></td>
                    <td colspan="3" class="export_table_cell" style='border: none;'>Podpis zamestnanca</td>
                    <td colspan="2" class="export_table_cell last"
                        style='border-left: none;border-top: none;border-bottom: none'>Pečiatka a podpis
                        zamestnávateľa
                    </td>
                </tr>
                <tr>
                    <td colspan="8" class="export_table_cell last sun bot-three"
                        style="border-top: none; padding: 20px;border-right: 2px solid black;">
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
        <div class="col-1">
            <a class="btn btn-primary" href="month_view_80.php?id=<?= $worker_id ?>&m=<?= $_GET["m"] ?>">80 hodinová
                verzia</a>
        </div>
        <?php
        $rework = false;
        if (!($closed = $service->WorkerHasMonthClosed($worker_id, $_GET["m"])) && !($rework = $service->WorkerHasMonthForRework($worker_id, $_GET["m"])) && ($_SESSION["user_id"] == $worker_id || $_SESSION["user_role"] == 1)) { ?>
            <div class="col-4">
                <form action="submit_close_month.php" method="post">
                    <input type="hidden" value="<?= $worker_id ?>" name="worker_id">
                    <input type="hidden" value="<?= $_GET["m"] ?>" name="month">
                    <input class="btn btn-primary" name="submit" value="Uzavrieť mesiac" type="submit">
                </form>
            </div>
        <?php } else if ($closed) { ?>
            <div class="col-3">
                <div class="alert alert-info" role="alert">
                    Mesiac bol uzavretý.
                </div>
            </div>
            <?php
            if ($_SESSION["user_role"] == 1) {
                ?>
                <div class="col-4">
                    <form action="submit_rework_month.php" method="post">
                        <div class="container-fluid">
                            <input type="hidden" value="<?= $worker_id ?>" name="worker_id">
                            <input type="hidden" value="<?= $_GET["m"] ?>" name="month">
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
            <div class="col-2">
                <form action="submit_close_month_correction.php" method="post">
                    <input type="hidden" value="<?= $worker_id ?>" name="worker_id">
                    <input type="hidden" value="<?= $_GET["m"] ?>" name="month">
                    <input class="btn btn-primary" name="submit" value="Uzavrieť mesiac (oprava)" type="submit">
                </form>
            </div>
            <?php
        }
        ?>
    </div>
<?php
include "footer.php";
