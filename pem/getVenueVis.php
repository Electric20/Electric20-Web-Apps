<?php
	//ini_set('display_errors',1);
	//error_reporting(E_ALL|E_STRICT);
    include '/var/www/dataStore/dataAccess/db_connect.php';
	$venues = array();
	$returnJson = array();
	$venue = array();
	$uid = $_GET['uid'];
	$start = $_GET['start'];
	$end = $_GET['end'];
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
	mysql_select_db($database,$connection);	
	$getDistinctVenueStatement = "select distinct(tblVenue.venueId), description, asText(location) as location from tblCalendar, tblVenue where tblCalendar.userId = 5 and tblCalendar.venueId = tblVenue.venueId and tblCalendar.start >= '$start' and tblCalendar.end <= '$end'";
	$results = mysql_query($getDistinctVenueStatement);
	while($dataset = mysql_fetch_assoc($results))
	{
		$polyString = $dataset['location'];
		$venue["venueId"] = $dataset['venueId'];
		$vid = $dataset['venueId'];
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
		$venue['presence'] = array();
		$timeInVenueStatement = "select start, end from tblCalendar where userId = $uid and venueId = $vid and tblCalendar.start >= '$start' and tblCalendar.end <= '$end'";
		$results2 = mysql_query($timeInVenueStatement);
		while($dataset2 = mysql_fetch_assoc($results2))
		{
			array_push($venue['presence'], array("start" => $dataset2['start'], "end" => $dataset2['end']));
		}
		array_push($venues, $venue);
	}
	$returnJson['status'] = 1;
	$returnJson['list'] = $venues;
	mysql_close($connection);
	print preg_replace('/"(-?\d+\.?\d*)"/', '$1', json_encode($returnJson));

	/**
	the return for this should be json with the following format:
		{
			"userTrail":[{lat: lng: timestamp: }...]},
			"totalTime":{hours: min: },
			"venueList":[{
							"vid": int,
							"points": [{lat: double, lng:double}...],
							"timeSpent": {hours: int, mins: int},
							"avgConsumption": double,
							"totalConsumption": double
						}] 
			
		
	
	
	**/


?>

