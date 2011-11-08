<?php
    include '/var/www/dataStore/dataAccess/db_connect.php';
	$venues = array();
	$venue = array();
	$uid = $_GET['uid'];
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
	mysql_select_db($database);
	$results = mysql_query("select sec_to_time(sum(time_to_sec(timeDiff(end, start)))) as duration, description from tblCalendar, tblVenue where tblCalendar.venueId = tblVenue.venueId and tblCalendar.userId = $uid group by tblCalendar.venueId;
	",$connection);
	while($dataset = mysql_fetch_assoc($results))
	{
		$venue["description"] = $dataset['description'];
		$venue["duration"] = $dataset['duration'];
		array_push($venues, $venue);
	}
	mysql_close($connection);
	print preg_replace('/"(-?\d+\.?\d*)"/', '$1', json_encode($venues));
	
	
	

?>