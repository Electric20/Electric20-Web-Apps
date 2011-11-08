<?php
	error_reporting(0);
     	session_start();
		
	require_once('../dataStore/utility.php');

	logAction('arrive');

        if (!isset($_SESSION['loggedIn'])) {
		logAction('noauth');
                header('Location: http://www.electric20.com/bzb/login.php?to=overview');
        }

?>

<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link href="style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="top_up-min.js"></script>
<script type="text/javascript" src="jquery.min.js"></script>
<script type="text/javascript" src="../dataStore/utility.js"></script>
<script type="text/javascript">
function updatePage () {
	logAction("update_start");
	resetStatus();
	$.ajax({
    		url: 'overview_data.php',
    		type: "GET",
    		dataType: "json",
    		timeout: 4000,
    		success: function(data) {
			$('#tweetUser').attr('href', "http://www.twitter.com/"+data.twitterUser);
			var src;
	                if (data.freqStyle == "red") {
        	                src = "high";
                	} else {
                        	src = "low";
                	}
                	if (data.valueStyle == "red") {
                        	src = src + "high";
                	} else if (data.valueStyle == "orange") {
                        	src = src + "typ";
                	} else {
                        	src = src + "low";
                	}
                	src = "url(" + src + ".png)";
                	$('#map').css("background-image", src);
                	$('#count').html(data.count);
                	$('#comparison').html(data.comparison);
                	logAction("update_complete map: "+src+" count: "+data.count+" comparison: "+data.comparison);
                	updatedTimerAt = new Date();
                	setTimeout("updatePage()", 300000);
		},
    		error: function(x, t, m) {
			$('#tweetUser').attr('href', "");
                        var src = "url(hightyp.png)";
                        $('#map').css("background-image", src);
                        $('#count').html("20");
                        $('#comparison').html("?");
                        updatedTimerAt = new Date();
                        setTimeout("updatePage()", 300000);
			logAction("update_fail map");
    		}
	});
/*
	$.getJSON('overview_data.php', function(data) {
		var src;
		if (data.freqStyle == "red") {
			src = "high";
		} else {
			src = "low";
		}
		if (data.valueStyle == "red") {
			src = src + "high";
		} else if (data.valueStyle == "orange") {
			src = src + "typ";
		} else {
			src = src + "low";
		}
		src = "url(" + src + ".png)";
		$('#map').css("background-image", src);
		$('#count').html(data.count);
		$('#comparison').html(data.comparison);
		logAction("update_complete map: "+src+" count: "+data.count+" comparison: "+data.comparison);
		updatedTimerAt = new Date();
		setTimeout("updatePage()", 300000);
	});*/
}

function resetStatus () {
        $.getJSON("status_data.php", function(json) {
                var blurb = json.data.description;
                if (json.data.code == 0) {
                        blurb += ": warning!";
                        $('#status').css('background-color', "#F00");
                } else if (json.data.code == 1) {
                        $('#status').css('background-color', "#FF4500");
                } else {
                        $('#status').css('background-color', "#66CC66");
                }
                $('#statusLink').html(blurb);
                $('#status').css('right', $(window).width() - $('#about').offset().left);
        });
}

$(window).resize(function(){
        recentre();
});

function recentre () {
        $('.container').css({
                position:'absolute',
                left: ($(window).width() - $('.container').outerWidth())/2,
                top: ($(window).height() - $('.container').outerHeight())/2
        });

	$('.inner_container').css(
		"width", $('.container').innerWidth()
        );
}

function randomiseLinks () {
	if (Math.random() > 0.5) {
		logAction("overview_links_randomised");
		var a = $('#link1a').html();
		var b = $('#link1b').html();
		$('#link1a').html($('#link2a').html());
		$('#link1b').html($('#link2b').html());
		$('#link2a').html(a);
		$('#link2b').html(b);
	}
}

var updatedTimerAt;

function updateTimer () {
        if (updatedTimerAt != null) {
		var remain = updatedTimerAt.getTime()+300000-new Date().getTime();
                var next = Math.floor(remain/60000);
		var secs = Math.floor((remain%60000)/1000);
                $('#timer').html("Next update in "+next+":"+(secs>=10 ? secs : "0"+secs));
        }
        setTimeout(updateTimer, 1000);
}
</script>
</head>
<body onLoad="randomiseLinks(); recentre(); updatePage(); updateTimer()">

<div class="container" style="width: 70%;">	

<div class="floater" style="width: 35%; text-align: right; margin-left: 20px; padding: 20px;">
        <h3 style="font-size: 14pt; color: #FFA54F">The Neighbourhood</h3>
        <p style="font-size: 8pt;">Right now the <span id="count"></span> neighbours are using electricity equivalent to <span id="comparison"></span>.
To find out more, choose one of the links below:</p>
        <table style="float: right;">
	<tr>
	<td id="link1a"><a style="font-size: 8pt; color: #33ccff; font-weight: bold;" href="http://www.twitter.com/mrlcoffee" onclick="logAction('click twitter')" target="new"><img 
src="twitter.png" 
/></a></td>
	<td id="link1b"><a style="font-size: 8pt; border-bottom: 1px dotted #33ccff; color: #33ccff; font-weight: bold;" href="" id="tweetUser" 
target="new" onclick="logAction('click twitter')">Your home's Tweets</a></td>
	<td id="link2a"><a href="graph.php" style="font-size: 8pt; font-weight: bold; color: #33ccff;" onclick="logAction('click ourvis')"><img src="data.png" /></a></td>
	<td id="link2b"><a href="graph.php" style="font-size: 8pt; border-bottom: 1px dotted #33ccff; font-weight: bold; color: #33ccff;" onclick="logAction('click ourvis')">Electricity 
data</a></td>
	</tr>
	</table>
	</div>

<h1>The Big Picture</h1>
<p>The diagram below uses data from the <span style="font-weight: bold;">National Grid</span> and from the <span style="font-weight: bold;">Neighbourhood homes</span> to 
show how 
<span style="font-weight: bold;">demand for 
electricity</span> in the 
Neighbourhood compares to the rest 
of the UK right now.</p>
<p>If UK demand is high, extra strain will be placed on the national electricity infrastructure (reducing efficiency and increasing costs to maintain the Grid); to help 
reduce the national demand the Neighbourhood can do their bit by reducing their use of electricity when national levels are high.</p>
<p>The diagram automatically updates every 5 minutes to reflect changes in electricity use in the UK and in the Neighbourhood.</p>

<div class="innercontainer" id="map" style="padding: 20px; background-image: url(typtyp.png); background-position: center; background-repeat: no-repeat; height: 420px;">
<div id="timer" class="timer" style="float: left;"></div>

</div>

</div>

<div class="about" id="about">
<a title="How do I use this website?" href="about.php?concept=overview" 
target="blank" onclick="logAction('click help')">Help</a>
</div>
<div class="status" id="status">
<a id="statusLink" title="Is my home connected to the Neighbourhood?" href="about.php?concept=status" target="blank" onclick="logAction('click status')">Status</a>
</div>
<div class="logo">
<a title="What is Electric20?" href="about.php?concept=electric20" target="blank" onclick="logAction('click electric20')"><img src="Electric20.png" /></a>
</div>

</body>
</html>
