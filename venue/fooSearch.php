<?php
	//ini_set('display_errors',1);
	//error_reporting(E_ALL|E_STRICT);
	$lat = 10;
	$lng = 10;
	$radius = 2;
	$output = array();
	for(i=0; i <= 360; i += 360/24)
	{
	    $extra_point = "POINT(". $radius*cos(i)." ". $radius*sin(i) . ")";
		array_push($output,$extra_point);
	}
print json_encode($output);




?>