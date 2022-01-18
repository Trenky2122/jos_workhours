<?php
include "header.php";
include "service.php";

$service = new Service();
$worker_id = $_GET["id"];
$worker_name = $service->GetWorkerNameWithId($worker_id);
$year = substr($_GET["m"],0,4);
$month = substr($_GET["m"],5,2);

$list_of_dates = array();
$list_of_days = ["Mon"=>"pondelok", "Tue"=>"utorok", "Wed"=>"streda", "Thu"=>"štvrtok", "Fri"=>"piatok", "Sat"=>"sobota", "Sun"=>"nedeľa"];
$start_date = "01-".$month."-".$year;
$start_time = strtotime($start_date);
$end_time = strtotime("+1 month", $start_time);
for($i=$start_time; $i<$end_time; $i+=86400)
{
    array_push($list_of_dates, array(date('d.m.Y', $i), date('D', $i)));
}
$done_days = $service->GetDoneWorkerWorkDays($worker_id, $month, $year);
$total_time = array();
?>

    <div class="row noprint mb-1" style="margin-top: 1em">
        <div class="col-1">
            <a href="index.php" class="btn btn-primary"> späť</a>
        </div>
        <div class="col-3">
            <form method="get" action="month_view.php">
                <input type="hidden" name="id" value="<?=$worker_id?>">
                <label for="month" class="mb-1">mesiac/rok:</label>
                <input type="month" class="mb-1" id="month" name="m" value="<?=$_GET["m"]?>">
                <input type="submit" name="submit" value="Hľadať">
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <table id="example" style="width: ">
                <thead>
                    <tr>
                        <td colspan="6" rowspan="3">Mesiac: <?= $month ?>/<?= $year ?></td>
                        <td colspan="3">Pracovný čas</td>
                    </tr>
                    <tr>
                        <td colspan="3">Firma: JOS GROUP s.r.o.</td>
                    </tr>
                    <tr>
                        <td colspan="3">Zamestnanec: <?= $worker_name ?></td>
                    </tr>
                    <tr>
                        <th scope="col" class="sun">Mesiac</th>
                        <th scope="col" class="sun">Deň</th>
                        <th scope="col" class="sun">Začiatočný čas</th>
                        <th scope="col" class="sun">Konečný čas</th>
                        <th scope="col" class="sun">Pauza</th>
                        <th scope="col" class="sun">Celkový čas</th>
                        <th scope="col" class="sun">Náplň práce</th>
                        <th scope="col" class="sun">Projekt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach ($list_of_dates as $date_day){
                            $date = $date_day[0];
                            $day = $list_of_days[$date_day[1]];
                            $date2 = date('Y-m-d', strtotime($date));
                            $row = array();
                            if(isset($done_days[$date2])) {
                                $row = $done_days[$date2];
                            }
                            else {
                                $row['id']=-1;
                                $row['begin_time'] = null;
                                $row['end_time'] = null;
                                $row['break_begin'] = null;
                                $row['break_end'] = null;
                                $row['project'] = null;
                                $row['description'] = null;
                            }
                            if($day == 'nedeľa')
                                $class = 'sun';
                            else
                                $class = 'ord';
                            echo("<tr><td class='".$class."'>".$date."</td>");
                            echo("<td class='".$class."'>".$day."</td>");
                            echo("<td class='".$class."'>".substr($row['begin_time'], 0, 5)."</td>");
                            echo("<td class='".$class."'>".substr($row['end_time'], 0, 5)."</td>");
                            $pause=$service->CalculateDayTime($row["break_begin"], $row["break_end"], null, null);
                            echo("<td class='".$class."'>".$pause."</td>");
                            $day_time = $service->CalculateDayTime($row['begin_time'], $row['end_time'], $row['break_begin'], $row['break_end']);
                            array_push($total_time, $day_time);
                            echo("<td class='".$class."'>".$day_time."</td>");
                            echo("<td class='".$class."'>".$row['description']."</td>");
                            echo("<td class='".$class."'>".($row["id"]==-1?"":$service->GetProjectsStringForWorkersWorkday($row["id"]))."</td>");
                            echo("</tr>");
                        }
                    ?>
                    <tr>
                        <td colspan="5"><strong>Suma:</strong></td>
                        <td><strong><?=$service->CalculateTotalTime($total_time)?></strong></td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td colspan="3" style='border: none;'>Dátum: <?=$list_of_dates[count($list_of_dates) - 1][0]?></td>
                        <td colspan="3" style='border: none;'>Podpis zamestnanca</td>
                        <td colspan="2" style='border: none;'>Pečiatka a podpis zamestnávateľa</td>
                    </tr>
                    <tr><td colspan="8" style="border: none; padding: 20px;"></td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row noprint mt-1">
        <div class="col-1">
            <button class="btn btn-primary" id="pdf" onclick="window.print()">Export</button>
        </div>
    </div>


    <style>
        th, td{
            padding: 5px;
            text-align: left;
            border: 1px solid black;
        }

        table{
            border: solid black;
            font-size: small;
        }

        .sun{
            border-bottom: 2px solid black;
        }

        .ord{
            border-bottom: 1px solid black;
        }

        @media print{
            th, td{
                padding: 1px;
            }
            table{
                border: solid black;
                font-size: 8px;
                margin-top: -50px;
            }
            .noprint {
                visibility: hidden;
            }
        }
    </style>

<?php
include "footer.php";
