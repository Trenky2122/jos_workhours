<?php
require_once './vendor/autoload.php';
use MoIsmailzai\Clockify;

if(!isset($_POST['worker_id']) || !isset($_POST['dates'])){
    header("Location: index.php?err=1");
    die();
}
$service = new Service();
$apiKeyWorkspace = $service->GetApiKey($_POST['worker_id']);
try {
    $clockify = new Clockify($apiKeyWorkspace['key'], $apiKeyWorkspace['workspace']);
} catch (Exception $e) {
    header("Location: index.php?err=1");
    die();
}
$dates = unserialize($_POST['dates']);

$result = "";

foreach ( $dates as $date ) {
    $report = $clockify->getReportByDay( $date );
    $result .= $clockify->formatReport( $report );
}

print_r( $result );
