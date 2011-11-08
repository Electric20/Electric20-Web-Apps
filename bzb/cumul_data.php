<?php
	error_reporting(0);
	session_start();

	require_once('../dataStore/utility.php');
	require_once('../dataStore/query.php');

	$raw = getTotalConsumptionToday();

	$accuracy = date("G")/24 * 0.50;
	$filtered = array();

	$filtered = array_filter($raw, function ($element) use ($accuracy) {
		return $element['accuracy'] > $accuracy;
	});

	$total = 0;
	$consumption = array();

	foreach ($filtered as $c) {
		$consumption[] = $c;
		$total += $c['total'];
	}

	$consumption['count'] =	count($consumption);
	$consumption['average'] = $total / $consumption['count'];
	
	echo json_encode(array('consumption'=>$consumption));

	unset($raw);
	unset($filtered);
	unset($consumption);

?>	  
