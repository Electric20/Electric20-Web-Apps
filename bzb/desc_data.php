<?php

	require_once('../dataStore/query.php');

	if ($_GET['sensorId'] == "undefined") {
		echo (json_encode(request(array('action'=>'getDescription', 'hubId'=>$_GET['hubId']))));
	} else {
		echo (json_encode(request(array('action'=>'getDescription', 'hubId'=>$_GET['hubId'], 'sensorId'=>$_GET['sensorId']))));
	}

?>
