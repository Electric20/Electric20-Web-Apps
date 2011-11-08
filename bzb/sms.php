<?php

require_once("../dataStore/query.php");

$data = request(array('action'=>"getTotalConsumptionLast2Days"));

echo json_encode($data);
?>
