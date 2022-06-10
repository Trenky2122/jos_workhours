<?php
$active = "projects";
include "header.php";
include "message_bar.php";
include_once "service.php";
$service = new Service();
$from = "0000-01-01";
$to = "9999-12-31";
if(isset($_GET["from"])&&!empty($_GET["from"]))
    $from=$_GET["from"];
if(isset($_GET["to"])&&!empty($_GET["to"]))
    $to=$_GET["to"];
?>
<div class="row" style="margin-top: 1.5em">
    <div class="col">
        <form method="get">
            <label for="from">Od:</label>
            <input type="date" id="from" name="from" <?php if($from!="0000-01-01")echo "value='$from'" ?>>
            <label for="to">Do:</label>
            <input type="date" id="to" name="to" <?php if($to!="9999-12-31")echo "value='$to'" ?>>
            <input type="submit" value="Hľadať">
        </form>
    </div>
</div>
<div class="row">
    <div class="col">
        <table class="table table-stripped">
            <thead>
                <tr>
                    <th>Projekt</th>
                    <th>Čas</th>
                    <th>Aktívny</th>
                    <th>Akcia</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $projects = $service->GetAllProjects();
            foreach ($projects as $project){
                $workerData=$service->GetWorkerDataForProject($project->id, $from, $to);
                ?>
                <tr>
                    <td style="border-bottom: black solid 1px; border-top: black solid 2px"><strong><?=$project->name?></strong></td>
                    <td style="border-bottom: black solid 1px; border-top: black solid 2px"><?=$service->NormalizeTime($service->GetProjectTimeSince($project->id, $from, $to))?></td>
                    <td style="border-bottom: black solid 1px; border-top: black solid 2px"><?=$project->active?"áno":"nie"?></td>
                    <td style="border-bottom: black solid 1px; border-top: black solid 2px">
                        <form action="project_action.php" method="post">
                            <input type="hidden" value="<?=$project->id?>" name="project_id">
                            <input type="submit" name="submit" class="mb-1 btn btn-<?=$project->active?"danger":"success"?>" value="<?=$project->active?"Deaktivovať":"Aktivovať"?>"><br>
                        </form>
                    </td>
                </tr>
                <?php
                foreach ($workerData as $name=>$time){
                    ?>
                    <tr>
                        <td><?=$name?></td>
                        <td><?=$service->NormalizeTime($time)?></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>

    </div>
</div>
<?php
include "footer.php";