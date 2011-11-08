<?php

	ini_set('display_errors',1);
	error_reporting(E_ALL|E_STRICT);
    include '/var/www/dataStore/dataAccess/db_connect.php';
	$user_email = $_GET['email'];
	$user_pass = md5($_GET['userPass']);
	$returnJson = array();
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
	mysql_select_db($database,$connection);
	$login_statement = "select userId from tblUser where email='$user_email' and password = '$user_pass'";
	$loginResult = mysql_query($login_statement);
	
	if(mysql_num_rows($loginResult) >= 1)
	{
		$returnJson['status'] = 1;
		$returnJson['email'] = $user_email;
		$dataset = mysql_fetch_assoc($loginResult);
		
		$returnJson['uid'] = $dataset['userId'];
		
	}
	else
	{
		$returnJson['status'] = 0;
	}
	
	mysql_close($connection);
	print preg_replace('/"(-?\d+\.?\d*)"/', '$1', json_encode($returnJson));

?>