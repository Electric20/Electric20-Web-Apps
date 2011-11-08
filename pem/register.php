<?php
	ini_set('display_errors',1);
	error_reporting(E_ALL|E_STRICT);
	
    include '/var/www/dataStore/dataAccess/db_connect.php';
	$returnJson = array();
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
	mysql_select_db($database,$connection);
	$check_statement = "select * from tblUser where email='" . $_GET['email'] . "'"; 
	$uid_statement = "select max(userId) + 1 as user from tblUser";
	$uid_result = mysql_query($uid_statement);
	$uid_dataset = mysql_fetch_assoc($uid_result);
	$uid = $uid_dataset['user'];
	
	$register_result = mysql_query($check_statement); 
	if (mysql_num_rows($register_result) >= 1)
	{ 
		$status = 0;  
	}
	else 
	{ 
		$email = $_GET['email']; 
		$userpass = md5($_GET['userPass']);
		$apiKey = md5($email);
		$update_statement = "insert into tblUser values(" .$uid. ",'" .$apiKey. "','".$email."','".$userpass."')"; 
		mysql_query($update_statement); 
		$status = 1;
	}
	$returnJson['status'] = $status;
	mysql_close($connection);
	print preg_replace('/"(-?\d+\.?\d*)"/', '$1', json_encode($returnJson));
?>