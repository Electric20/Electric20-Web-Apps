<?php

	session_start();

	require_once('../dataStore/utility.php');

	logAction('arrive');

	if (!isset($_SESSION['loggedIn'])) {
		logAction('noauth');
		header('Location: http://www.electric20.com/bzb/mobileLogin.php');
	}

?>

<html>
<head>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="../dataStore/utility.js"></script>
	<link href="mobile.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">

var cost = false;

function updateValue () {
	logAction('mobile update_start');
	$.getJSON("mobile_data.php", function(json) {
		$('#description').html(json.description);
		$('#value').html(json.load + "W");
		var html;
		if (cost) {
			html = "&pound;"+(json.load / 1000 * 24 * 0.1285).toFixed(2);
		} else {
			html = (json.load / 1000 * 24 * 0.54522).toFixed(2) + "kg CO<sub>2</sub>";
		}
		$('#cost').html(html + " per day");
		shiftStuff();
		logAction('mobile update_success');
		setTimeout(updateValue, 5000);
	});
}

function switchUnits () {
	if (cost) cost = false;
	else cost = true;
	setTimeout(switchUnits, 15000);
}

$(window).resize(function() {
	shiftStuff();
});

function shiftStuff () {
	$('#container').css('top', ($(document).height() - $('#container').height()) / 2);
        $('#container').css('left', ($(document).width() - $('#container').width()) / 2);
}
	</script>
</head>
<body onload="switchUnits(); updateValue()">

<div id="container" class="container" style="position: absolute;">
<h1 id="description">Your home</h1>
<div class="innercontainer" style="text-align: center; padding: 5%;">
<div id="big" style="font-size: 120pt; font-weight: bold;">
<span id="value">Please wait</span>
</div>
<div id="small" style="font-size: 30pt;">
<span id="cost"></span>
</div>
</div>
</div>

</body>
</html>
