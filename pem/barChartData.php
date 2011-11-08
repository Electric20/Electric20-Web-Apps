<?php
/**
Error reporting / logging
**/
ini_set('display_errors',1);
error_reporting(E_ALL|E_STRICT);
date_default_timezone_set('Europe/London');
include 'auth/db_connect.php';	
/**
get parameters from URL / Header
**/
$uid = $_GET['uid'];
$dater = $_GET['dater'];
/**
database connection
**/
$db_conn = mysql_connect($hostname,$username,$password) or die('MYSQL Fail ' . mysql_error());
mysql_select_db($database,$db_conn);	
/**
local (assoc) object arrays
**/
$cons_data = array();
$return_json = array();
/**
SQL Statements
with exception to yesterday and this time last week queries as these have to be generated on an adhoc basis from venue presence from initial query
**/	

// get venue present in for each hour period
$sql_stat_venuePresence = "(Select tblCalendar.venueId, 
                          tblCalendar.start, 
                          tblCalendar.end, 
                          tblVenue.description, 
                          tblHubVenue.hubId, 
                          i, 
                          h as start, 
                          h + interval 1 hour as end
                   from tblHubVenue, 
                        tblVenue, 
                        (SELECT '$dater' + INTERVAL i*1 hour AS h, i
                         FROM integers 
                         WHERE i BETWEEN 0 AND 23
                        ) as times
                   left outer join tblCalendar on 
                        ((h>= tblCalendar.start and h + interval 1 hour <= tblCalendar.end) or
                        (tblCalendar.end >= h and tblCalendar.end <= h + interval 1 hour) or
                        (tblCalendar.start >= h and tblCalendar.start <= h + interval 1 hour))
                        and tblCalendar.userId = $uid
                   where tblHubVenue.venueId = tblCalendar.venueId
                        and tblVenue.venueId = tblCalendar.venueId
                   group by h)";

/**
Get consumption value for each item, place all in assoc array
**/

$sql_results_venuePresence = mysql_query($sql_stat_venuePresence);

while($sql_ds_vP = mysql_fetch_assoc($sql_results_venuePresence))
{
	$point_in_day = $sql_ds_vP['i'];
	$venue_id = $sql_ds_vP['venueId'];
	$description = $sql_ds_vP['description'];
	$start = $sql_ds_vP['start'];
	$end = $sql_ds_vP['end'];
	$hubId = $sql_ds_vP['hubId'];
	$consumption = 0;
	$sql_stat_lowestCat = "select min(category) as cat from tblSensorDir where hubId = $hubId";
	$lowestCat = 3;
	$sql_results_lowestCat = mysql_query($sql_stat_lowestCat);
	while($sql_ds_lowestCat = mysql_fetch_assoc($sql_results_lowestCat))
	{
		$lowestCat = $sql_ds_lowestCat['cat'];
	}
	
	$sql_stat_consumption = "select sum(loadAvg)/1000 as cons from 
	   	(select (avg(loadAvg)) as loadAvg, tblLoadAgg.hubId, tblLoadAgg.sensorId from tblLoadAgg, tblSensorDir 
where (timestamp between '$start' and '$end') and 
tblLoadAgg.hubId = $hubId and tblLoadAgg.sensorId = tblSensorDir.sensorId and tblLoadAgg.hubId = tblSensorDir.hubId and tblSensorDir.category = $lowestCat group by tblLoadAgg.sensorId) t1;";
	
	$sql_results_consumption = mysql_query($sql_stat_consumption);
	while($sql_ds_consumption = mysql_fetch_assoc($sql_results_consumption))
	{
		$consumption = $sql_ds_consumption['cons'];
	}
	
	array_push($cons_data, array("i" => $point_in_day, "desc" => $description, "venueId" => $venue_id, "cons" => $consumption));
	
}

$return_json['data'] = $cons_data;
mysql_close($db_conn);
print preg_replace('/"(-?\d+\.?\d*)"/', '$1', json_encode($return_json));

?>
