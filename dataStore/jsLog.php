<?php
	session_start();

	require_once("utility.php");

	if (!isset($_SESSION['loggedIn'])) {
		$_SESSION['loggedIn'] = -1;
	}

	logActionRequest ($_SESSION['loggedIn'], $_SERVER['HTTP_REFERER'], $_GET['action'], $_GET['agent']);

?>
