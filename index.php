<?php
include "header.php";
include "service.php";
$service = new Service();
$year = date("Y");
if (isset($_GET["y"])) {
    if (is_numeric($_GET["y"])) {
        $year = $_GET["y"];
    } else {
        die("Bad format year: " . $_GET["y"]);
    }
}
$week = date("W");
if (isset($_GET["w"])) {
    if (is_numeric($_GET["w"])) {
        $week = $_GET["w"];
    } else {
        die("Bad format week: " . $_GET["w"]);
    }
}
$days = $service->GetDaysInWeek($year, $week);
$workers = $service->GetAllWorkers();
include "message_bar.php";
?>
    <div class="row mt-2">
        <div class="col-6">
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
        <div class="col-6">
            <label for="day_select">Filter deň</label>
            <select id="day_select" onchange="reloadFilter()">
                <option value="0">Celý týždeň</option>
                <?php
                foreach ($days as $day) {
                    ?>
                    <option value="<?= "day_" . $day->id ?>"><?= $day->day_of_week . " " . date("d.m.Y", strtotime($day->day)) ?></option>
                    <?php
                }
                ?>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col">
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
                foreach ($workers as $worker) {
                    ?>
                    <tr class="table-row worker_<?= $worker->id ?> worker_name">
                        <td colspan="8"><strong><?= $worker->GetFullName() ?></strong></td>
                    </tr>
                    <?php
                    foreach ($days as $day) {
                        ?>
                        <tr class="<?= "day_" . $day->id . " worker_" . $worker->id ?> table-row">
                            <td><?= date("d.m.Y", strtotime($day->day)) ?></td>
                            <form method="post" action="submit_workday.php">
                                <input required type="hidden" name="work_day_id" value="<?= $day->id ?>">
                                <input required type="hidden" name="worker_id" value="<?= $worker->id ?>">
                                <td><input required
                                           id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>_begin_time"
                                           onchange="recalculateHours('total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>')"
                                           type="time" step="300" name="begin_time"></td>
                                <td><input required id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>_end_time"
                                           onchange="recalculateHours('total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>')"
                                           type="time" step="300" name="end_time"></td>
                                <td><input required
                                           id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>_break_begin"
                                           onchange="recalculateHours('total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>')"
                                           type="time" step="300" name="break_begin"></td>
                                <td><input required
                                           id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>_break_end"
                                           onchange="recalculateHours('total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>')"
                                           type="time" step="300" name="break_end"></td>
                                <td id="total_hrs_<?= $worker->id . "_" . $day->day_of_week ?>">0:00</td>
                                <td required><textarea name="description"></textarea></td>
                                <td required><input type="text" name="project"></td>
                                <td required><input type="password" name="password"></td>
                                <td required><input type="submit" class="btn btn-primary" value="Uložiť"></td>
                            </form>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function recalculateHours(element_id) {
            let begin_time = document.getElementById(element_id + "_begin_time").value;
            let end_time = document.getElementById(element_id + "_end_time").value;
            if (begin_time && end_time) {
                let time = calculateTimeDifference(begin_time, end_time);
                let break_begin = document.getElementById(element_id + "_break_begin").value;
                let break_end = document.getElementById(element_id + "_break_end").value;
                if (break_begin && break_end) {
                    time = calculateTimeDifference(calculateTimeDifference(break_begin, break_end), time);
                }
                document.getElementById(element_id).innerHTML = time;
            } else {
                document.getElementById(element_id).innerHTML = "0:00";
            }
        }

        function calculateTimeDifference(time_begin, time_end) {
            console.log(arguments);
            let time_begin_min = time_begin.substring(3);
            let time_end_min = time_end.substring(3);
            let minutes = time_end_min - time_begin_min >= 0 ? time_end_min - time_begin_min : 60 + (time_end_min - time_begin_min);
            let hours_begin = time_begin.substring(0, 2);
            let hours_end = time_end.substring(0, 2);
            let hours = hours_end - hours_begin - (time_end_min - time_begin_min >= 0 ? 0 : 1);
            if (hours < 0)
                return "00:00";
            return (hours < 10 ? "0" : "") + hours.toString() + ":" + (minutes < 10 ? "0" : "") + minutes.toString();
        }

        function reloadFilter() {
            let allRows = document.getElementsByClassName("table-row");
            [].forEach.call(allRows, (element) => element.style.display = "none");
            let className = "";
            if (document.getElementById("worker_select").value != "0") {
                className += document.getElementById("worker_select").value;

                let elsToShow = document.getElementsByClassName("worker_name "+ className);
                [].forEach.call(elsToShow, (el) => el.style.display = "table-row");
            }
            else {
                let elsToShow = document.getElementsByClassName("worker_name");
                [].forEach.call(elsToShow, (el) => el.style.display = "table-row");
            }
            if (document.getElementById("day_select").value != "0") {
                className += " " + document.getElementById("day_select").value;
            }
            if (className == "")
                return;
            if(className.charAt(0)==" ")
                className=className.substring(1);
            let elsToShow = document.getElementsByClassName(className);
            [].forEach.call(elsToShow, (el) => el.style.display = "table-row");
        }
    </script>

<?php
include "footer.php";