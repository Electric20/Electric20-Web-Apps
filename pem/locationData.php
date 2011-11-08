<?php	
    include '/var/www/dataStore/dataAccess/db_connect.php';
	$start = $_GET['start'];
	$end = $_GET['end'];
	$uid = $_GET['uid'];
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
	mysql_select_db($database,$connection);
	$getPointsStatement = "select x(location) as x, y(location) as y, timeStamp, accuracy from tblUserLocationTrail where userId = $uid and timeStamp between '$start' and '$end' limit 500";
	$resultset = mysql_query($getPointsStatement);
	$returnJson = array();
	$returnJson['trail'] = array();
	while($dataset = mysql_fetch_assoc($resultset))
	{
		$placemark = array();
		$placemark['time'] = $dataset['timeStamp'];
		$placemark['accuracy'] = $dataset['accuracy'];
		$placemark['point'] = array('lat' => $dataset['x'], 'lng' => $dataset['y']);
		array_push($returnJson['trail'],$placemark);
	}
	mysql_close($connection);
	print preg_replace('/"(-?\d+\.?\d*)"/', '$1', json_encode($returnJson));

?>