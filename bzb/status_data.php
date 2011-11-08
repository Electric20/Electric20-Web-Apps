<?php

	session_start();

	require_once('../dataStore/query.php');

	$hubId = -1;

	if (isset($_SESSION['loggedIn'])) {
            include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
	        mysql_select_db($database);
        	$results = mysql_query("SELECT hubId FROM tblUserInfo WHERE userId=".$_SESSION['loggedIn']);
        	while ($row = mysql_fetch_assoc($results)) {
			$hubId = $row['hubId'];
        	}
        	mysql_free_result($results);
        	unset($results);
        	mysql_close();
	}
	echo (json_encode(request(array('action'=>'currentHubStatus', 'hubId'=>$hubId))));
?>
