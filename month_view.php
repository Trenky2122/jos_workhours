<?php
include "header.php";
include "service.php";

$service = new Service();
$worker_id = $_GET["id"];
$worker_name = $service->GetWorkerNameWithId($worker_id);
$month = $_GET["m"];
$year = $_GET["y"];

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
/*print_r($list_of_dates);
echo("<br>");
print_r($done_days);
print_r(array_keys($done_days));*/
?>

    <div class="row">
        <div class="col-1" style="margin-top: 1em">
            <a href="index.php" class="btn btn-primary"> späť</a>
        </div>
    </div>

    <div class="row">
        <div class="col-10">
            <table>
                <thead>
                    <tr>
                        <td colspan="6" rowspan="3">Mesiac: <?= $month ?>/<?= $year ?></td>
                        <td colspan="4">Pracovný čas</td>
                    </tr>
                    <tr>
                        <td colspan="4">Firma: JOS GROUP s.r.o.</td>
                    </tr>
                    <tr>
                        <td colspan="4">Zamestnanec: <?= $worker_name ?></td>
                    </tr>
                    <tr>
                        <th>Mesiac</th>
                        <th>Deň</th>
                        <th>Začiatočný čas</th>
                        <th>Konečný čas</th>
                        <th>Začiatok pauzy</th>
                        <th>Koniec pauzy</th>
                        <th>Celkový čas</th>
                        <th>Náplň práce</th>
                        <th>Projekt</th>
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
                                $row['begin_time'] = null;
                                $row['end_time'] = null;
                                $row['break_begin'] = null;
                                $row['break_end'] = null;
                                $row['project'] = null;
                                $row['description'] = null;
                            }
                            if($day == 'nedeľa')
                                $class = 'wed';
                            else
                                $class = 'ord';
                            echo("<tr><td class='".$class."'>".$date."</td>");
                            echo("<td class='".$class."'>".$day."</td>");
                            echo("<td class='".$class."'>".substr($row['begin_time'], 0, 5)."</td>");
                            echo("<td class='".$class."'>".substr($row['end_time'], 0, 5)."</td>");
                            echo("<td class='".$class."'>".substr($row['break_begin'], 0, 5)."</td>");
                            echo("<td class='".$class."'>".substr($row['break_end'], 0, 5)."</td>");
                            echo("<td class='".$class."'>".$service->CalculateTotalTime($row['begin_time'], $row['end_time'], $row['break_begin'], $row['break_end'])."</td>");
                            echo("<td class='".$class."'>".$row['description']."</td>");
                            echo("<td class='".$class."'>".$row['project']."</td>");
                            echo("</tr>");
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <style>
        div{
            margin: 0.7em;
        }

        th, td{
            padding: 7px;
            text-align: left;
            border: 1px solid black;
            /*height: 40px;*/
        }

        table{
            border: solid black;
        }

        .wed{
            border-bottom: 2px solid black;
        }

        .ord{
            border-bottom: 1px solid black;
        }
    </style>

<?php
include "footer.php";
