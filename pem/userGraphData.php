<?php
	/**
	Error reporting / logging
	**/
	//ini_set('display_errors',1);
	//error_reporting(E_ALL|E_STRICT);
	/**
	get parameters from URL / Header
	**/
	$uid = $_GET['uid'];
	$dater = $_GET['dater'];
	$sTime = "00:00";//$_GET['sTime'];
	$eTime = "23:59";//$_GET['eTime'];
	/**
	database connection
	**/
    include '/var/www/dataStore/dataAccess/db_connect.php';
	$db_conn = mysql_connect($db_host,$db_user,$db_pass) or die('MYSQL Fail ' . mysql_error());
	mysql_select_db($db_schema,$db_conn);	
	/**
	local (assoc) object arrays
	**/
	$venue_array = array();
	$return_json = array();
	$user_location_trail = array(); 
	/**
	SQL Statements
	with exception to yesterday and this time last week queries as these have to be generated on an adhoc basis from venue presence from initial query
	**/ 
	// get total time in minutes monitoring has been active for
	$sql_stat_totTimeMonitored = "select timestampdiff(MINUTE, min(p.start), max(p.end)) as ttm from( select start, end from tblCalendar where userId = $uid and ((tblCalendar.start between '$dater $sTime:00' and '$dater $eTime:00') or (tblCalendar.end between '$dater $sTime:00' and '$dater $eTime:00'))) p" ;	
	//get venue information v2
	$sql_stat_venueInformation2 = "
		select 
			p.venueId,
			hubId,
			points, 
			description, 
			sum(timediff) as timeInVenue,
			cat
			
		from 
			(select timestampdiff(MINUTE,t2.start,t2.end) as timediff, 
			t2.start, 
			t2.end, 
			t2.venueId, 
			t4.description,
			t4.cat, 
			asText(t4.location) as points
			
			from
				tblVenue t4,  
				(select 
					start, 
					end, 
					venueId 
					from 
					tblCalendar 
				where 
					userId = $uid and 
					((start between '$dater $sTime:00' and '$dater $eTime:00') or (end between '$dater $sTime:00' and '$dater $eTime:00'))
						  ) t2
				   where t2.venueId = t4.venueId group by t2.start
				  ) p,
				  tblHubVenue t1
			 where 
				  p.venueId = t1.venueId
			 group by venueId";
	
// get user location trail
	$sql_stat_userLocationTrail = "select x(location) as lat, y(location) as lng, timestamp from tblUserLocationTrail where userId = $uid and timestamp between '$dater $sTime:00' and '$dater $eTime:00' limit 2000";	
	/**
	Populate user location trail
	**/
	$sql_results_userLocationTrail = mysql_query($sql_stat_userLocationTrail);
	while($sql_ds_ult = mysql_fetch_assoc($sql_results_userLocationTrail))
	{
		array_push($user_location_trail, array("lat" => $sql_ds_ult['lat'], "lng" => $sql_ds_ult['lng'], "timestamp" => $sql_ds_ult['timestamp']));
	}	
	/**
	Get total time spent being monitored
	**/
	$total_time_monitored = 0;
	$sql_results_totTimeMonitored = mysql_query($sql_stat_totTimeMonitored);
	while($sql_ds_ttm = mysql_fetch_assoc($sql_results_totTimeMonitored))
	{
		$total_time_monitored = $sql_ds_ttm['ttm'];
	}
	/**
	Get venue information
	**/
	$sql_reuslts_venueInformation = mysql_query($sql_stat_venueInformation2);
	$venue = array();
	while($sql_ds_vi = mysql_fetch_assoc($sql_reuslts_venueInformation))
	{
		$polygon_string = $sql_ds_vi['points'];
		$venue['venueId'] = $sql_ds_vi['venueId'];
		$venue['cat'] = $sql_ds_vi['cat'];
		$currVenueId = $venue['venueId'];
		$venue['description'] = $sql_ds_vi['description'];
		//$venue['points'] = array();	$tmp = split( 'POLYGON', $polygon_string );
		$venue['timeSpent'] = $sql_ds_vi['timeInVenue'];
		$venue['percentInVenue'] = ($venue['timeSpent'] / $total_time_monitored) * 100;
		$hubId = $sql_ds_vi['hubId'];
		$sql_stat_getBestSensorForHub = "select min(category) as cat from tblSensorDir where hubId = $hubId";
		$sql_result_getBestSensorForHub = mysql_query($sql_stat_getBestSensorForHub);
		$bestCat = 0;
		while($sql_ds_bestCat = mysql_fetch_assoc($sql_result_getBestSensorForHub))
		{
			$bestCat = $sql_ds_bestCat['cat'];
		}
		
		
		$sql_stat_consInfo_td = "
			select sum(cons) as cons from	
				(select 
					   (avg(loadAvg)/1000)*(timediff/60) as cons,
					   sensorId,
					   hubId
				from 
					   (select 
							   loadAvg,
							   tblLoadAgg.hubId,
							   tblLoadAgg.sensorId,
							   tblLoadAgg.timestamp,
							   t2.timediff,
							   t2.start,
							   t2.end
						from 
							   tblLoadAgg, 
							   tblSensorDir,
							   (select start, 
									   end,
									   venueId,
									   timestampdiff(MINUTE,start,end) as timediff
								from   
									   tblCalendar 
								where  
									   userId = $uid and 
									   venueId = $currVenueId and 
									   ((start between '$dater $sTime:00' and '$dater $eTime:00') or (end between '$dater $sTime:00' and '$dater $eTime:00'))
							   ) t2
						where  
							   (tblLoadAgg.timestamp between t2.start and t2.end) and 
							   tblLoadAgg.hubId = $hubId and
							   tblLoadAgg.sensorId = tblSensorDir.sensorId and 
							   tblLoadAgg.hubId = tblSensorDir.hubId and 
							   tblSensorDir.category = $bestCat
					   ) t1
				group by start, sensorId) p";
			
			$sql_stat_consInfo_ld = "
				select sum(cons) as cons from	
					(select 
						   (avg(loadAvg)/1000)*(timediff/60) as cons,
						   sensorId,
						   hubId
					from 
						   (select 
								   loadAvg,
								   tblLoadAgg.hubId,
								   tblLoadAgg.sensorId,
								   tblLoadAgg.timestamp,
								   t2.timediff,
								   t2.start,
								   t2.end
							from 
								   tblLoadAgg, 
								   tblSensorDir,
								   (select start, 
										   end,
										   venueId,
										   timestampdiff(MINUTE,start,end) as timediff
									from   
										   tblCalendar 
									where  
										   userId = $uid and 
										   venueId = $currVenueId and 
										   ((start between '$dater $sTime:00' and '$dater $eTime:00') or (end between '$dater $sTime:00' and '$dater $eTime:00'))
								   ) t2
							where  
								   (tblLoadAgg.timestamp between (t2.start - interval 1 day) and (t2.end - interval 1 day)) and 
								   tblLoadAgg.hubId = $hubId and
								   tblLoadAgg.sensorId = tblSensorDir.sensorId and 
								   tblLoadAgg.hubId = tblSensorDir.hubId and 
								   tblSensorDir.category = $bestCat
						   ) t1
					group by start, sensorId) p";
			
				$sql_stat_consInfo_lw = "
					select sum(cons) as cons from	
						(select 
							   (avg(loadAvg)/1000)*(timediff/60) as cons,
							   sensorId,
							   hubId
						from 
							   (select 
									   loadAvg,
									   tblLoadAgg.hubId,
									   tblLoadAgg.sensorId,
									   tblLoadAgg.timestamp,
									   t2.timediff,
									   t2.start,
									   t2.end
								from 
									   tblLoadAgg, 
									   tblSensorDir,
									   (select start, 
											   end,
											   venueId,
											   timestampdiff(MINUTE,start,end) as timediff
										from   
											   tblCalendar 
										where  
											   userId = $uid and 
											   venueId = $currVenueId and 
											   ((start between '$dater $sTime:00' and '$dater $eTime:00') or (end between '$dater $sTime:00' and '$dater $eTime:00'))
									   ) t2
								where  
									   (tblLoadAgg.timestamp between (t2.start - interval 1 week) and (t2.end - interval 1 week)) and 
									   tblLoadAgg.hubId = $hubId and
									   tblLoadAgg.sensorId = tblSensorDir.sensorId and 
									   tblLoadAgg.hubId = tblSensorDir.hubId and 
									   tblSensorDir.category = $bestCat 
							   ) t1
						group by start, sensorId) p";
		
		
		$sql_result_today = mysql_query($sql_stat_consInfo_td);
		while($sql_ds_vitd = mysql_fetch_assoc($sql_result_today))
		{
			$venue['totalConsumption'] = $sql_ds_vitd['cons'];
		}
		
		$sql_resultsLastDay = mysql_query($sql_stat_consInfo_ld);
		while($sql_ds_vild = mysql_fetch_assoc($sql_resultsLastDay))
		{
			//$venue['totalConsumptionLd'] = $sql_ds_vild['cons'];
		}
		$sql_resultsLastWeek = mysql_query($sql_stat_consInfo_lw);
		while($sql_ds_vilw = mysql_fetch_assoc($sql_resultsLastWeek))
		{
			//$venue['totalConsumptionLw'] = $sql_ds_vilw['cons'];
		}
		
		
		
		$tmp = split( 'POLYGON', $polygon_string ) ;
		$tmp = split( '\(\(', $tmp [ 1 ] ) ;
		$tmp = split( '\)\)', $tmp [ 1 ] ) ;
		$polygons = explode ( ',', $tmp [ 0 ] );
		//foreach ( $polygons as $polygon ) 
		//{
	//		$point = explode ( ' ', $polygon );
//			array_push($venue['points'], array("lat" => $point[0], "lng" => $point[1]));//
//		}
		array_push($venue_array, $venue);
	}
	/**
	Populate return json array
	**/
	;
	$return_json['venueList'] = $venue_array;
	$return_json['totalTimeMonitored'] = $total_time_monitored;
	//$return_json['userLocationTrail'] = $user_location_trail;
	
	foreach($return_json['venueList'] as $venueI)
	{
		$_venueId = $venueI['venueId'];
		$_cat = $venueI['cat'];
		$_timeSpent = $venueI['timeSpent'];
		$_percentSpent = $venueI['percentInVenue'];
		$_consumption = $venueI['totalConsumption'];
		$mysql_stat_graphInsert = "insert into tblPemGraphData values($uid,'$dater',$_venueId,$_cat,$_timeSpent,$_percentSpent,$_consumption)";
		//print($mysql_stat_graphInsert);
		mysql_query($mysql_stat_graphInsert);
	}
	
	/**
	Encode, clean up and return json
	**/
	mysql_close($db_conn);
	print preg_replace('/"(-?\d+\.?\d*)"/', '$1', json_encode($return_json['venueList']));
?>