<?php

	require_once('../dataStore/utility.php');

	logAction('arrive');

?>

<html>
<head>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="../dataStore/utility.js"></script>
	<link href="mobile.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
$(window).resize(function() {
	shiftStuff();
});

function shiftStuff () {
	$('#container').css('top', ($(document).height() - $('#container').height()) / 2);
        $('#container').css('left', ($(document).width() - $('#container').width()) / 2);
}
	</script>
</head>
<body onload="shiftStuff()">

<div id="container" class="container" style="position: absolute;">
<h1 id="description">Mobile?</h1>
<div class="innercontainer" style="text-align: center; padding: 5%;">
<p>Electric20.com has detected that you are using a mobile device to browse the web. 
We would suggest that you use the mobile electricity meter at <span style="font-weight: bold;">http://www.electric20.com/m</span> as the main website might appear very cramped (and uses browser 
features 
that many mobiles do not yet 
support).</p>
<p>To continue to the mobile meter, <a href="mobile.php">click here</a>. It may be helpful to bookmark <span style="font-weight: bold;">http://www.electric20.com/m</span> to avoid this choice in 
the 
future.</p>
<p>To try the main website on your mobile, <a href="overview.php">click here</a>.</p>
</div>
</div>

</body>
</html>
