<?php
include "header.php";
include "service.php";
$service = new Service();
echo json_encode(date("d m y",strtotime("monday this week")));
?>
<table class="table table-stripped">
    <thead>
        <tr>
            <th>Deň</th>
            <th>Začiatočný čas</th>
            <th>Konečný čas</th>
            <th>Začiatok pauzy</th>
            <th>Koniec pauzy</th>
            <th>Náplň práce</th>
            <th>Projekt</th>
        </tr>
    </thead>
    <tbody>

<?php
$workers = $service->GetAllWorkers();
foreach ($workers as $worker){
    ?>
        <tr class="pondelok">
            <td>Pondelok</td>
            <form method="post" action="submit.php">
                <td><input type="time" step="5" name=""></td>
            </form>
        </tr>
    <?php
}
?>
    </tbody>
</table>
    <div class="row">

    </div>
<?php
include "footer.php";