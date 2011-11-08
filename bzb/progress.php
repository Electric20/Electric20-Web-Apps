<?php

	session_start();

	require_once('../dataStore/utility.php');

	logAction('arrive');

        if (!isset($_SESSION['loggedIn'])) {
		logAction('noauth');
                header('Location: http://www.electric20.com/bzb/login.php?to=progress');
        }

?>

<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7" >
	<script type="text/javascript" src="jquery.min.js"></script>
	<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="excanvas.js"></script><![endif]-->
	<script type="text/javascript" src="jquery.flot.js"></script>
	<script type="text/javascript" src="jquery.flot.navigate.js"></script>
	<script type="text/javascript" src="../dataStore/utility.js"></script>
	<script type="text/javascript">

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

	</script>
	<link href="style.css" rel="stylesheet" type="text/css" />
</head>
<body onload="resetStatus()">
<div class="about" id="about">
<a title="How do I use this website?" href="about.php?concept=help" target="blank" onclick="logAction('click help')">Help</a>
</div>
<div class="status" id="status">
<a id="statusLink" title="Is my home connected to the Neighbourhood?" href="about.php?concept=status" target="blank" onclick="logAction('click status')">Status</a>
</div>
<div class="logo">
<a title="What is Electric20?" href="about.php?concept=electric20" target="blank" onclick="logAction('click electric20')"><img src="Electric20.png" /></a>
</div>

	<div class="tabOff"><a href="graph.php" onclick="logAction('click_tab now')">What's happening right now?</a></div>
	<div class="tabOff"><a href="graphT.php" onclick="logAction('click_tab directory')">Neighbourhood directory</a></div>
	<div class="tabOn">Progress</div>
	<div class="container">
	<h1>Progress</h1>
	<p>This graph shows the progress of each of the neighbours over the last 7 days in terms of the daily cost of electricity used. Your home's daily 
consumption is highlighted in blue, while the neighbourhood average is shown in orange.</p>
	<p>If you move your mouse over any of the points on the graph you will be able to see which home the data belongs to and approximately how high the cost was on 
that day. Click on a point to view detailed information about a particular home. You can also zoom in on a point on the graph by double-clicking on that point or (if you 
have one) by 
using your mouse wheel.</p>
	<p>
	<div style="float: left; border-color: #FFA54F white #FFA54F #FFA54F; margin-right: 10px; border-style: solid; border-width: 10px; width:0; height:0;"></div>
	<div><a href="overview.php" onclick="logAction('click overview')">Return to the overview</a></div>
	</p>
	<div class="innercontainer" style="padding: 20px;">
	<div id="chart" style="height: 70%; margin: 0px 20px 0px 10px;"></div>
<script type="text/javascript">

logAction('progress update_start');

$.getJSON("progress_data.php", function(json) {
	logAction('progress update_complete');

	var data = new Array();

	for (var i in json.data) {
		data.push({'data': json.data[i].data, 'label':json.data[i].label, 'color':json.data[i].color, 'hubId':json.data[i].hubId});
	}

	var now = new Date();
	var then = new Date();
	then.setDate(then.getDate() - 8);

        $.plot($("#chart"), data , {
            	series: {
                	stack: false,
                	lines: { show: true, fill: false, steps: false },
			points: { show: true },
			shadowSize: 0			
            	},
		grid: { hoverable: true, clickable: true },
		xaxis: { mode: 'time', timeformat: "%d/%m/%y", min: then.getTime(), max: now.getTime(), minTickSize: [1, "day"], zoomRange: [0.1, 10], panRange: 
[then.getTime(), 
now.getTime()] },
		yaxis: { zoomRange: [0.1, 10], panRange: [0, 10], tickFormatter: kwhFormatter },
		legend: { show: false },
		zoom: { interactive: true },
        	pan: { interactive: true }
        });

	function kwhFormatter (v, axis) {
		return "&pound;" + v.toFixed(2);
	}

	function showTooltip(x, y, contents, color) {
        	$('<div id="tooltip">' + contents + '</div>').css( {
            		position: 'absolute',
            		display: 'none',
            		top: y + 5,
            		left: x - 40,
			'border-radius': '20px',
			'-moz-border-radius': '20px',
            		padding: '10px',
            		'background-color': color,
			color: 'white'
        	}).appendTo("body").fadeIn(200);
    	}
	var msg = "";
	var previousPoint = null;
    	$("#chart").bind("plothover", function (event, pos, item) {
        	$("#x").text(pos.x.toFixed(2));
        	$("#y").text(pos.y.toFixed(2));

		if (item) {
                	if (previousPoint != item.dataIndex) {
                    		previousPoint = item.dataIndex;
                    
                    		$("#tooltip").remove();
              			var x = item.datapoint[0].toFixed(2),
                        		y = item.datapoint[1].toFixed(2);
                    
                    		showTooltip(item.pageX, item.pageY,
                                	item.series.label + " at &pound;" + y,
					item.series.color);

				if (msg != item.series.label + " at &pound;" + y + ' day: ' + x) {
					msg = item.series.label + " at &pound;" + y + ' day: ' + x;
					logAction('node_mouseover tooltip: ' + msg);
				}
                	}
            	} else {
                	$("#tooltip").remove();
                	previousPoint = null;
        	}
    	});

	$("#chart").bind("plotclick", function (event, pos, item) {
        	if (item) {
			logAction('node_click hubId: '+item.series.hubId);
                        self.location = "detailed.php?hubId="+item.series.hubId+"&sensorId=undefined";
        	}
    	});
});
</script>
</div>
</div>
</body>
</html>
	  
