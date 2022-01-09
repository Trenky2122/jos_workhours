<?php
include "header.php";
include "service.php";
$service = new Service();
$year = date("Y");
if(isset($_GET["y"])){
    if(is_numeric($_GET["y"])){
        $year=$_GET["y"];
    }
    else{
        die("Bad format year: ".$_GET["y"]);
    }
}
$week = date("W");
if(isset($_GET["w"])){
    if(is_numeric($_GET["w"])){
        $week=$_GET["w"];
    }
    else{
        die("Bad format week: ".$_GET["w"]);
    }
}
$days = $service->GetDaysInWeek($year, $week);
?>
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
            <th>Heslo</th>
            <th></th>
        </tr>
    </thead>
    <tbody>

<?php
$workers = $service->GetAllWorkers();
foreach ($workers as $worker){
    ?>
    <tr>
        <td colspan="8"><strong><?= $worker->GetFullName() ?></strong></td>
    </tr>
<?php
    foreach ($days as $day){
    ?>
        <tr class="<?= $day->day_of_week." ".$worker->id ?>">
            <td><?= $day->day_of_week?></td>
            <form method="post" action="submit.php">
                <input type="hidden" name="work_day_id" value="<?= $day->id?>">
                <input type="hidden" name="worker_id" value="<?= $worker->id?>">
                <td><input id="total_hrs_<?=$worker->id."_".$day->day_of_week ?>_begin_time" onchange="recalculateHours('total_hrs_<?=$worker->id."_".$day->day_of_week ?>')" type="time" step="300" name="begin_time"></td>
                <td><input id="total_hrs_<?=$worker->id."_".$day->day_of_week ?>_end_time" onchange="recalculateHours('total_hrs_<?=$worker->id."_".$day->day_of_week ?>')" type="time" step="300" name="end_time"></td>
                <td><input id="total_hrs_<?=$worker->id."_".$day->day_of_week ?>_break_begin" onchange="recalculateHours('total_hrs_<?=$worker->id."_".$day->day_of_week ?>')" type="time" step="300" name="break_begin"></td>
                <td><input id="total_hrs_<?=$worker->id."_".$day->day_of_week ?>_break_end" onchange="recalculateHours('total_hrs_<?=$worker->id."_".$day->day_of_week ?>')" type="time" step="300" name="break_end"></td>
                <td id="total_hrs_<?=$worker->id."_".$day->day_of_week ?>">0:00</td>
                <td><textarea name="description"></textarea></td>
                <td><input type="text" name="project"></td>
                <td><input type="password" name="password"></td>
                <td><input type="submit" class="btn btn-primary" value="Uložiť"></td>
            </form>
        </tr>
    <?php
    }
}
?>
    </tbody>
</table>
<script>
    function recalculateHours(element_id){
        let begin_time=document.getElementById(element_id+"_begin_time").value;
        let end_time=document.getElementById(element_id+"_end_time").value;
        if(begin_time && end_time){
            console.log(calculateTimeDifference(begin_time, end_time));
        }
    }

    function calculateTimeDifference(time_begin, time_end){
        let time_begin_min = time_begin.substring(3);
        let time_end_min = time_end.substring(3);
        let minutes = time_end_min - time_begin_min>=0 ? time_end_min - time_begin_min : 60 + (time_end_min - time_begin_min);
        let hours_begin = time_begin.substring(0, 2);
        let hours_end = time_end.substring(0, 2);
        let hours = hours_end - hours_begin -(time_end_min - time_begin_min>=0?0:1);
        return hours.toString()+":"+minutes.toString();
    }
</script>

<?php
include "footer.php";