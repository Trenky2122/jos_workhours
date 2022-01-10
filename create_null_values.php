<?php
$service = new Service();
$workers = $service->GetAllWorkers();

foreach ($workers as $worker){
    for($i = 0; $i < 7; $i++)
        echo($service->SetWorkerDefaultToNull($worker->id, $i));
}