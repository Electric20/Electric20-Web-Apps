<?php
	ini_set('display_errors',1);
	error_reporting(E_ALL|E_STRICT);
    include '/var/www/dataStore/dataAccess/db_connect.php';
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
	mysql_select_db($database,$connection);
	$userEmail = $_POST['userEmail'];
	$userPass = md5($_POST['userPass']);
	$varifyStatement = "select count(*) from tblUser where email = '$userEmail' and password = '$userPass'";
	$results = mysql_query($varifyStatement);
	$dataSet = mysql_fetch_assoc($results);
	$count = $dataSet['count(*)'];
	if($count == 0)
	{
		$return = -1;
	}
	else
	{
		$getUidStatement = "select userId from tblUser where email = '$userEmail'";
		$results = mysql_query($getUidStatement);
		$dataSet = mysql_fetch_assoc($results);
		$return = $dataSet['userId'];
	}
	mysql_close($connection);
	print($return);
?>