<?php
	ini_set('display_errors',1);
	error_reporting(E_ALL|E_STRICT);
	
	$CHECKIN = 0;
	$CHECKOUT = 1;
	$POSTUPDATE = 2;
	$DONOTHING = 3;
	
	$THRESHOLD = 3; 
	
	$action = -1;
	$action_venue;
	
    include '/var/www/dataStore/dataAccess/db_connect.php';
	
	$lat = $_POST['lat'];
	$lng = $_POST['lng'];
	$accuracy = $_POST['accuracy'];
	if($accuracy < 0.1)
	{
		
		
		$speed = $_POST['speed'];
		$uid = $_POST['uid'];
		$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
		mysql_select_db($database,$connection);
	
		mysql_query("update tblUser set lastActivity = NOW() where userId = $uid");

		$current_state;
		$current_venue = 0;
		$current_jitter;
		$chekin_time;
		$select_last_checkin_statement = "select * from tblCheckin where userId = $uid order by timeStamp desc limit 1";
		$last_checkin_results = mysql_query($select_last_checkin_statement);
		$select_venue_circle_statement = "select venueId, description from tblVenue where intersects(GETPOLYGON(" . $lat . "," . $lng . "," . $accuracy . ",50), tblVenue.location)";
		$venue_circle_results = mysql_query($select_venue_circle_statement);
		$select_venue_point_statement = "select venueId, description from tblVenue where intersects (geomFromText('point($lat $lng)'), tblVenue.location)";
		$venue_point_results = mysql_query($select_venue_point_statement);
		$select_user_data_statement = "select * from tblUser where userId = $uid";
		$user_data_result = mysql_query($select_user_data_statement);
		if(mysql_num_rows($venue_circle_results) == 0) // none in circle
		{

			if(mysql_num_rows($venue_point_results) == 0) // none in circle, none in point
			{
				
					while($row = mysql_fetch_assoc($last_checkin_results))
					{
						if($row['inout']==1) // user is checked in
						{
							$current_venue = $row['venueId'];
							$action = $CHECKOUT;
							$checkin_time = $row['timeStamp'];
						}
						else // user is not checked in
						{
							$action = $POSTUPDATE;
						}	
					}
			}
			else // none in circle, 1 in point
			{
				while($row = mysql_fetch_assoc($last_checkin_results))
				{
					if($row['inout']==1) // user is checked in
					{
						$venue_present = false;
						$current_venue = $row['venueId'];
						while($venue_point_row = mysql_fetch_assoc($venue_point_results))
						{
							if($venue_point_row['venueId'] == $current_venue)
								$venue_present = true;
								$action_venue = $venue_point_row['venueId'];
						}	
						if($venue_present)
						{
							$action = $DONOTHING;
						}
						else
						{
							$action = $CHECKOUT;
							$checkin_time = $row['timeStamp'];
						}
					}
					else // user is not checked in
					{
						$action = $CHECKIN; // which venue
						while($venue_point_row = mysql_fetch_assoc($venue_point_results))
						{
								$action_venue = $venue_point_row['venueId'];
						}
					}	
				}	
			}
		}
		else // venues exist in circle
		{
			while($row = mysql_fetch_assoc($last_checkin_results))
			{
				if($row['inout']==1) // user is checked in
				{
				
					$venue_present = false;
					$current_venue = $row['venueId'];
				
					while($venue_circle_row = mysql_fetch_assoc($venue_circle_results))
					{
						if($venue_circle_row['venueId'] == $current_venue)
						{
							$venue_present = true;
							$action_venue = $venue_circle_row['venueId'];
						}

					}
					if($venue_present)
					{
						$action = $DONOTHING;
					}
					else
					{
						$action = $CHECKOUT;
						$checkin_time = $row['timeStamp'];
					}
				}
				else // user is not checked in
				{
					$action = $CHECKIN;
					while($venue_circle_row = mysql_fetch_assoc($venue_circle_results))
					{
							$action_venue = $venue_circle_row['venueId'];
					
					}
				}
			}
		}
	
		while($user_data_row = mysql_fetch_assoc($user_data_result))
		{
			$last_activity = $user_data_row['lastActivity'];
			$jitter_count = $user_data_row['jitterCount'];
		}
	
		if($action == $CHECKIN)
		{
			$check_in_statement = "insert into tblCheckin values ($uid,$action_venue,1,current_timestamp)";
			mysql_query($check_in_statement);
		}
	
		if($action == $CHECKOUT)
		{

			$jitter_count++;

			if($jitter_count >= $THRESHOLD)
			{

				$reset_jitter_statement = "update tblUser set jitterCount=0 where userId = $uid";
				$check_out_statement = "insert into tblCheckin values ($uid,$current_venue,0,current_timestamp)";
				$update_calendar_statement = "insert into tblCalendar values($uid,$current_venue,'$checkin_time',current_timestamp)";
				mysql_query($reset_jitter_statement);
				mysql_query($check_out_statement);
				mysql_query($update_calendar_statement);
			}
			else
			{
				$update_jitter_statement = "update tblUser set jitterCount=$jitter_count where userId = $uid";
				mysql_query($update_jitter_statement);
			}
		}
	
		if($action == $POSTUPDATE)
		{

			$location_update_statement = "insert into tblUserLocationTrail values ($uid,current_timestamp,geomFromText('point($lat $lng)'),$speed,$accuracy)";
			mysql_query($location_update_statement);
		}
	
		if($action == $DONOTHING)
		{

			$reset_jitter_statement = "update tblUser set jitterCount=0 where userId = $uid";
			mysql_query($reset_jitter_statement);	
		}
	
		mysql_close($connection);
	}

?>
