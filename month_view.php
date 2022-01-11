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

//print_r($list_of_dates);
?>

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
                        foreach ($list_of_dates as $date){
                            echo("<tr><td>".$date[0]."</td><td>".$list_of_days[$date[1]]."</td></tr>");
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
    </style>

<?php
include "footer.php";
