<?php
	session_start();

	require_once('../dataStore/utility.php');

logAction('arrive');

if (isset($_SESSION['loggedIn'])) {
	logAction('logout');
	unset($_SESSION['loggedIn']);
	session_destroy();
	header('Location: http://www.electric20.com/bzb/mobile.php');
} else {
	if (isset($_GET['u']) && isset($_GET['p'])) {
        include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
		$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
    		mysql_select_db($database);

		$results = mysql_query("SELECT * FROM tblUser WHERE email = '".$_GET['u']."' AND password = '".md5($_GET['p'])."' LIMIT 1");
		if (mysql_num_rows($results) > 0) {
			while ($row = mysql_fetch_assoc($results)) {
				$_SESSION['loggedIn'] = $row['userId'];
				break;
			}
			logAction('login_success');
			header('Location: http://www.electric20.com/bzb/mobile.php');
		} else {
			logAction('login_fail');
			$html = '<p>Log-in details incorrect; please <a href="mobileLogin.php" onclick="logAction(\'click retry\')">try again</a></p>';
		}
	} else {
		logAction('login_start');
		$html = '<form name="input" action="mobileLogin.php" method="get">Username: <input type="text" name="u" /><br />'.
			'Password: <input type="password" name="p" /><br />'.
			'<input type="submit" value="Submit" onclick="logAction(\'click submit\')" /></form>';
	}
}

?>

<html>
<head>
	<link href="mobile.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="../dataStore/utility.js"></script>
</head>
<body>
<div class="container">
<div>
<h1>Log in to the Neighbourhood</h1>
<p><?php echo $html; ?></p>
</div>
</div>

</body>
</html>
