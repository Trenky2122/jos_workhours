<?php
$active = "month";
include "header.php";
include_once "service.php";

if($_SESSION["user_role"] != 1)
    die();

$service = new Service();
$worker_id = $_SESSION["user_id"];
if(isset($_GET["id"] )){
    $worker_id = $_GET["id"];
}
$worker_name = $service->GetWorkerNameWithId($worker_id);
$year = date("Y");
$month = date('m', strtotime(' -1 month '));
if(isset($_GET["m"])){
    $year = substr($_GET["m"], 0, 4);
    $month = substr($_GET["m"], 5, 2);
}
$list_of_dates = array();
$list_of_days = ["Mon" => "po", "Tue" => "ut", "Wed" => "st", "Thu" => "št", "Fri" => "pi", "Sat" => "so", "Sun" => "ne"];
$start_date = "01-" . $month . "-" . $year;
$start_time = strtotime($start_date);
$end_time = strtotime("+1 month", $start_time);
for ($i = $start_time; $i < $end_time; $i += 86400) {
    $list_of_dates[] = array(date('d.m.Y', $i), date('D', $i), date("Y-m-d", $i));
}

$selected_worker_id = -1;
if(isset($_GET['wrk'])){
    $selected_worker_id = $_GET['wrk'];
}

$all_workers_id = $service->GetAllWorkersId();

include "message_bar.php";
?>
<table class="table table-striped">
    <thead>
    <tr>
        <th>
            Zamestnanec
        </th>
        <th>
            Mesiac uzavretý
        </th>
    </tr>
    </thead>
    <tbody>
     <?php
foreach ($all_workers_id as $wrk_id){
    echo "<tr><td><a href='month_view.php?m=$year-$month&id=$wrk_id'";
    if(!($closed = $service->WorkerHasMonthClosed($wrk_id, $year."-".$month)))
        echo    "style='color: red'";
    echo ">".$service->GetWorkerNameWithId2($wrk_id) . "</a></td>";
    echo "<td>".($closed?"áno": "nie")."</td></tr>";
}?>
    </tbody>
</table>
<?php

include "footer.php";



