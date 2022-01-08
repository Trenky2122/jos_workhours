<?php
include "header.php";
include "service.php";
$service = new Service();
$workers = $service->GetAllWorkers();
foreach ($workers as $worker){
    echo $worker->GetFullName();
}
include "footer.php";