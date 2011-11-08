<?php

    include '/var/www/dataStore/dataAccess/db_connect.php';
	$venues = array();
	$venue = array();
	$uid = $_GET['uid'];
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
	mysql_select_db($database);
	$results = mysql_query("select tblCheckin.venueId, asText(location) as location, description from tblVenue, tblCheckin where tblVenue.venueId = tblCheckin.venueId and tblCheckin.userId = $uid and tblCheckin.inout = 1 order by timeStamp desc limit 1",$connection);
	while($dataset = mysql_fetch_assoc($results))
	{
		$polyString = $dataset['location'];
		$venue["venueId"] = $dataset['venueId'];
		$venue["description"] = $dataset['description'];
		$venue["points"] = array();
		$tmp = split( 'POLYGON', $polyString ) ;
		$tmp = split( '\(\(', $tmp [ 1 ] ) ;
		$tmp = split( '\)\)', $tmp [ 1 ] ) ;
		$polygons = explode ( ',', $tmp [ 0 ] );
		foreach ( $polygons as $polygon ) 
		{
			$point = explode ( ' ', $polygon );
			array_push($venue['points'], array("lat" => $point[0], "lng" => $point[1]));
		}
		array_push($venues, $venue);
	}
	mysql_close($connection);
	print preg_replace('/"(-?\d+\.?\d*)"/', '$1', json_encode($venues));

?>