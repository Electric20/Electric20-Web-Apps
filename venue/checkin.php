<?php
	//ini_set('display_errors',1);
	//error_reporting(E_ALL|E_STRICT);
	
    include '/var/www/dataStore/dataAccess/db_connect.php';
	
	$lat = $_POST['lat'];
	$lng = $_POST['lng'];
	$accuracy = $_POST['accuracy'];
	$speed = $_POST['speed'];
	
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
	mysql_select_db($database);
	
	$select_statement = "select venueId, description from tblVenue where intersects(GETPOLYGON(" . $lat . "," . $lng . "," . $accuracy . ",20), tblVenue.location)";
	$results = mysql_query($select_statement);
	$venues = array();
	$places = 0;
	while($row = mysql_fetch_assoc($results))
	{
		array_push($venues, array("venueId" => $row['venueId'],"description" => $row['description']);
		$places++;
	}
	mysql_close($connection);
	//print $places . "£" . preg_replace('/"(-?\d+\.?\d*)"/', '$1', json_encode($venues));
	print "10";
?>