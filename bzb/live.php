<?php

	session_start();

	require_once('../dataStore/utility.php');

	logAction('arrive');

        if (!isset($_SESSION['loggedIn'])) {
		logAction('noauth');
                header('Location: http://www.electric20.com/bzb/login.php?to=live');
        }

?>

<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7" >
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="d3.js"></script>
	<script type="text/javascript" src="d3.chart.js"></script>
	<script type="text/javascript" src="../dataStore/utility.js"></script>
	<script type="text/javascript">

function updateTimer () {
        var x = new Date();
        if (x.getMinutes() > 18) {
                if (x.getHours() == 23) {
                        x.setHours(0);
                        x.setDate(x.getDate()+1);
                } else {
                        x.setHours(x.getHours()+1);
                }
        }
        x.setMinutes(18);
        var y = Math.ceil((x.getTime()-new Date().getTime()) / 60000.0);
        setTimeout(updateTimer, 60000);
        if (y==0) {
                updateSummary();
        }
}

function getUnitsHTML (unit, value, average) {
        var unitHtml;
        if (unit == 0) {
                unitHtml = parseFloat(value).toFixed(2)+"KWh";
        } else if (unit == 1) {
                unitHtml = "&pound;"+(parseFloat(value)*0.1285).toFixed(2);
        } else if (unit == 2) {
                unitHtml = (parseFloat(value)*0.54522).toFixed(2)+"Kg CO<sub>2</sub>";
        } else {
                unitHtml = Math.round((parseFloat(value)/average).toFixed(2)*100)+"%";
        }
        return unitHtml;
}

function ord(n) {
	var sfx = ["th","st","nd","rd"];
	var val = n%100;
	return n>1 ? (n + (sfx[(val-20)%10] || sfx[val] || sfx[0])) : "";
}

function resetReset () {
	if (<?php echo $_GET['sensorId']; ?> == undefined) {
		$('#reset').hide();
	}
}

function setDescription () {
	if (<?php echo $_GET['hubId']; ?> != "undefined") {
		$.getJSON('desc_data.php?hubId='+<?php echo $_GET['hubId']; ?>+'&sensorId='+<?php echo $_GET['sensorId']; ?>, function (data) {
			$('#homeName').html(data.data.hDescription);
			$('#titleHomeName').html(data.data.hDescription);
			$('#summHomeName').html(data.data.hDescription);
			if (<?php echo $_GET['sensorId']; ?> != undefined) {
				$('#sensorName').html(": "+data.data.sDescription);
				$('#titleSensorName').html(": "+data.data.sDescription);
			}
		});
	}
}

function updateSummary () {
	logAction('summary update_start');
	$.getJSON('cumul_data.php', function (data) {
		for (var i in data.consumption) {
			if (data.consumption[i].hubId == <?php echo $_GET['hubId']; ?>) {
				$('#summary').html(
					"<span style='font-weight: bold;'>Total consumption: "+
						getUnitsHTML(0, data.consumption[i].total, data.consumption.average)+", "+
						getUnitsHTML(1, data.consumption[i].total, data.consumption.average)+" or "+
						getUnitsHTML(2, data.consumption[i].total, data.consumption.average)+
						"</span><br />"+
					"<span id='summHomeName'>This home</span> has the <span style='font-weight:bold;'>"+ord(parseInt(i) + 1)+"</span> highest consumption in the neighbourhood (of "+data.consumption.count+" homes)" 
				);
			}
		}
		setDescription();
		logAction('summary update_complete');
	});
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

	</script>
	<link href="style.css" rel="stylesheet" type="text/css" />
	<style>
.bullet { font-size: 10px; }
.bullet .marker { stroke: #000; stroke-width: 3px; }
.bullet .tick line { stroke: #666; stroke-width: .5px; }
.bullet .range.s0 { fill: #fff; }
.bullet .range.s1 { fill: #fff; }
.bullet .range.s2 { fill: #F23805; }
.bullet .range.s3 { fill: gray; }
.bullet .range.s4 { fill: #66cc66; }
.bullet .measure.s0 { fill: white; }
.bullet .measure.s1 { fill: white; }
.bullet .title { font-size: 12px; font-weight: bold; }
.bullet .subtitle { fill: #999; }
	</style>
</head>
<body onload="updateTimer(); updateSummary(); resetReset()">
<div class="about" id="about">
<a title="How do I use this website?" href="about.php?concept=help" target="blank" onclick="logAction('click help')">Help</a>
</div>
<div class="status" id="status">
<a id="statusLink" title="Is my home connected to the Neighbourhood?" href="about.php?concept=status" target="blank" onclick="logAction('click status')">Status</a>
</div>
<div class="logo">
<a title="What is Electric20?" href="about.php?concept=electric20" target="blank" onclick="logAction('click electric20')"><img src="Electric20.png" /></a>
</div>

	<div class="tabOn">Live</div>
	<div class="tabOff"><a href="detailed.php?hubId=<?php echo $_GET['hubId'];?>&sensorId=<?php echo $_GET['sensorId'];?>" onclick="logAction('click_tab history')">Last 7 days</a></div>
	<div class="container">
	<div class="floater" style="width: 35%; text-align: right; margin-left: 20px; padding: 20px;">
	<h3 style="color: #FFA54F">Today</h3>
	<div id="summary" style="font-size: 10pt;"></div>
	</div>
	<h1>Live data (<span id="titleHomeName"></span><span id="titleSensorName"></span>)</h1>
	<p>The meter(s) below help to show how much electricity is being used (in Watts) in <span id="homeName">this home</span> right now. The 
white bar indicates the current usage, while the coloured 
ranges help you to judge how typical this level is for this home:</p>
	<ul>
		<li>Green: much lower than usual for past week</li>
		<li>Grey: typical for past week</li>
		<li>Red: much higher than usual for past week</li>
	</ul>
	<p>The meter(s) will update constantly to give you the most accurate picture of how the home is using electricity right now. 
If this is your home, you should be able to see a change in the meter(s) if you switch appliances on or off.</p>
	<p>
	If this home has multiple sensors it may be helpful for you to click on the name of a particular sensor (to the left of the bars below) to view detailed data about that sensor.
	</p>
	<p>
	<div style="float: left; border-color: #FFA54F white #FFA54F #FFA54F; margin-right: 10px; border-style: solid; border-width: 10px; width:0; height:0;"></div>
	<a href="graph.php" onclick="logAction('click overview')">Return to the neighbourhood</a></p>
	<div class="innercontainer" style="padding: 20px;">
	<div class="reset" id="reset"><a onclick="logAction('click showAll')" href="live.php?hubId=<?php echo $_GET['hubId']; ?>&sensorId=undefined">Show all sensors</a></div>
	<div id="chart"></div>
<script type="text/javascript">

var w = $(window).width() * 0.9,
    h = 65,
    m = [1, 0, 20, 150]; // top right bottom left

var chart = d3.chart.bullet()
    .width(w - m[1] - m[3])
    .height(h - m[0] - m[2]);

var vis;

logAction('live update_start');
resetStatus();
d3.json("live_data.php?hubId=<?php echo $_GET['hubId']; ?>&sensorId=<?php echo $_GET['sensorId']; ?>", function(data) {
  	vis = d3.select("#chart").selectAll("svg")
      		.data(data)
    		.enter().append("svg:svg")
      		.attr("class", "bullet")
      		.attr("width", w)
      		.attr("height", h)
    		.append("svg:g")
      		.attr("transform", "translate(" + m[3] + "," + m[0] + ")")
      		.call(chart);

  	var title = vis.append("svg:g")
      		.attr("text-anchor", "end")
      		.attr("transform", "translate(-6," + (h - m[0] - m[2]) / 2 + ")");

  	title.append("svg:text")
      		.attr("class", "title")
      		.text(function(d) { return d.subtitle; });

  	title.append("svg:text")
      		.attr("class", "subtitle")
      		.attr("dy", "1em")
      		.text(function(d) { return d.title; });

	title.on("mouseover", function (d) {
		logAction('mouseover_bar hubId: '+d.hubId+' sensorId: '+d.sensorId);
	});

	title.on("click", function (d) {
			logAction('click_bar hubId: '+d.hubId+' sensorId: '+d.sensorId);
			self.location = "detailed.php?hubId="+d.hubId+"&sensorId="+d.sensorId;
                });


	setTimeout("redrawGraph()", timeout);

	logAction('live update_complete');
});

var timeout = 6000;

function redrawGraph() {
	logAction('live update_start');
	resetStatus();
	d3.json("live_data.php?hubId=<?php echo $_GET['hubId']; ?>&sensorId=<?php echo $_GET['sensorId']; ?>", function(data) {
		var since = new Date().getTime();
		chart.duration(2000);
		window.transition = function () {
			vis
				.data(data)
				.call(chart);
		}
		transition();
		setTimeout("redrawGraph()", timeout);
		logAction('live update_complete');
	});
}

</script>
</div>
</div>
</body>

	  
