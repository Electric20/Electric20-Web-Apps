<?php

require_once("../dataStore/query.php");

$data = request(array('action'=>"getTotalConsumptionLast2Weeks"));

echo json_encode($data);
?>
