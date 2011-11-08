<?php
	session_start();

	require_once('../dataStore/utility.php');

logAction('arrive');

if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] >= 0) {
	logAction('logout');
	session_unset();
	session_destroy();
	$html = '<p>Logout successful. Click <a href="login.php">here</a> to login again.</p>';
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
			/*if (isset($_GET['to'])) {
				header('Location: http://www.electric20.com/bzb/'.$_GET['to'].'.php');
				$html = '<p>Log-in details incorrect; please <a href="login.php" onclick="logAction(\'click retry\')">try again</a></p>';
			} else {*/
				//header('Location: http://www.electric20.com/bzb/overview.php');
				$html = '<p>Login successful. Please <a href="overview.php">continue</a>.</p>';
			//}
		} else {
			logAction('login_fail');
			$html = '<p>Log-in details incorrect; please <a href="login.php" onclick="logAction(\'click retry\')">try again</a></p>';
		}
	} else {
		logAction('login_start');
		$html = '<form name="input" action="login.php" method="get">Username: <input type="text" name="u" /><br />'.
			'Password: <input type="password" name="p" /><br />'.
			'<input type="submit" value="Submit" onclick="logAction(\'click submit\')" /></form>';
	}
}

?>

<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7" >
	<link href="style.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="../dataStore/utility.js"></script>
	<script type="text/javascript">

function recentre () {
	$('#login').css(
		"padding-top", ($('#help').height() - $('#login').height()) / 2);
	$('#container').css(
                "top", ($(document).height() - $('#container').height()) / 2 - 20);
	$('#container').css(
                "left", ($(document).width() - $('#container').width()) / 2 - 20);
	$('#helpText').css(
                "width", ($('#help').width() - $('#pic').width()));
	$('#pic').css(
                "padding-top", ($('#help').height() - $('#pic').height()) / 2);
}

	</script>
</head>
<body onload="recentre()">
<div class="container" style="overflow: hidden; position: absolute; width: 90%;" id="container">
<div class="innertext_hor" id="login">
<h1 style="text-align: center;">Welcome to the Neighbourhood</h1>
<div style="text-align: center;"><?php echo $html; ?></div>
</div>
<div class="innercontainer_hor" style="padding: 20px 0px;" id="help">
<img style="padding: 20px; float: right;" src="docs.png" id="pic" />
<div style="padding: 20px;" id="helpText">
<h2>.How do I log in?</h2>
<p style="color: black;">
If you have volunteered to join <span style="font-weight: bold;">the neighbourhood</span> as part of the University of Nottingham electricity monitoring trial, you will have received a <span 
style="font-weight: bold;">welcome pack</span> by post. This pack contains the instructions to get you started with your online electricity feedback tools, most importantly your <span 
style="font-weight: bold;">username</span> and <span style="font-weight: bold;">password</span> to get you in.
</p>
<h3>But we've lost our welcome pack ...</h3>
<p style="color: black;">
If you have misplaced your welcome pack, please contact one of the monitoring team via phone or email to retrieve your log-in details.
We do not offer an online recovery service for these details as we prefer to keep a close watch on who has access to the neighbourhood data.
</p>
<h3>I'd like to join the neighbourhood</h3>
<p style="color: black;">
We'd love to hear from people who would like to join our energy monitoring research. There are a few practical requirements, and we have a limited number of places in our trials, so we advise that you 
contact us by <a href="mailto:info@electric20.com" class="green" onclick="logAction('click email')>email</a> to find out about any current 
opportunities to get 
involved.
</p>
</span>
</div>
</div>
</div>

<div class="logo">
<a title="What is Electric20?" href="about.php?concept=electric20" target="blank" onclick="logAction('click electric20')><img src="Electric20.png" /></a>
</div>

</body>
</html>
