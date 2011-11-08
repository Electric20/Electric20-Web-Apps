<?php
	error_reporting(0);
	session_start();

	require_once('../dataStore/utility.php');

	logAction('arrive');

	if (!isset($_SESSION['loggedIn'])) {
		logAction('noauth');
		header('Location: http://www.electric20.com/bzb/login.php?to=graph');
	}

        include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
        mysql_select_db($database);

        $raw = array();
        $max = 0;

        $myHubId = -1;

        $results = mysql_query("SELECT hubId FROM tblUserInfo WHERE userId=".$_SESSION['loggedIn']);
        while ($row = mysql_fetch_assoc($results)) {
                $myHubId = $row['hubId'];
        }

        mysql_free_result($results);
	mysql_close();

?>

<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7" >
	<script type="text/javascript" src="sizzle.js"></script>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="d3.js"></script>
	<script type="text/javascript" src="d3.layout.js"></script>
	<script type="text/javascript" src="d3.geom.js"></script>
	<script type="text/javascript" src="jquery.color.js"></script>
	<script type="text/javascript" src="jquery.animate-enhanced.js"></script>
	<script type="text/javascript" src="../dataStore/utility.js"></script>
	<link href="style.css" rel="stylesheet" type="text/css" />
	<style type="text/css">
		.link { stroke: #ccc; opacity: 0.7; }
		.nodetext { pointer-events: none; font: sans-serif; }
	</style>
	<script type="text/javascript">

var dropDown = [false, false, false, false];
var dropDownHTML = [
		[
			"<p>The neighbourhood hub at the centre of the network is shaded orange. (<a onclick='dropDownSwitch(0)' class='dark'>Click for more</a>)</p>",
			"<p>The neighbourhood hub at the centre of the network is shaded orange. The size of this orange circle indicates the amount of electricity being used by the whole neighbourhood right now.</p>"+
			"<p>Individual homes are connected to the hub."+
			"The circles representing the homes are sized according to the electricity they are currently using: a large circle indicates higher electricity use.</p>"+
			"<p>You can click on any of the homes to view live information about the electricity being used by the home, or historical information about the home's electricity use over the past 7 days.</p>"+
			"<p>Each home has one or more sensors monitoring the electricity being used."+
			"If a home has more than one sensor, the major sensors are shown connected to the home on the diagram."+
			"For a complete picture of all homes and sensors in the network see the <a href='graphT.php' class='dark'>Neighbourhood Directory</a>.</p>"+
			"<p>If you want to see live or historical information about a particular sensor rather than a whole household, click on the sensor. (<a onclick='dropDownSwitch(0)' class='dark'>Click for less</a>)</p>"
		],
		["",""],
		["",""],
		[
			"<p style='color: black; text-align: justify;'>The table below shows total electricity consumption so far today. (<a onclick='dropDownSwitch(3)' class='dark'>Click for more</a>)</p>",
			"<p style='color: black; text-align: justify;'>The table below shows total electricity consumption so far today for the three neighbours who are furthest below and above the average (in green and red respectively)." +
			"Neighbours below the average should have lower bills and produce less carbon emissions. The table updates once an hour and at midnight every day each home's counter is reset to zero. (<a onclick='dropDownSwitch(3)' class='dark'>Click for less</a>)</p>"
		]
	];

function refreshDropdowns () {
	for (var i = 0; i < dropDown.length; i++) {
		if (dropDown[i]) {
			$('#dd'+i).html(dropDownHTML[i][1]);
		} else {
			$('#dd'+i).html(dropDownHTML[i][0]);
		}
	}
}

function dropDownSwitch (index) {
	if (dropDown[index]) {
		logAction('hide_dropdown springy_dropdown: ' + index);
		dropDown[index] = false;
	} else {
		logAction('show_dropdown springy_dropdown: ' + index);
		dropDown[index] = true;
	}
	refreshDropdowns();
}

function updateTimer () {
	var x =	new Date();
        if (x.getMinutes() > 17) {
                if (x.getHours() == 23) {
                        x.setHours(0);
                        x.setDate(x.getDate()+1);
                } else {
                        x.setHours(x.getHours()+1);
                }
        }
        x.setMinutes(17);
	var y = Math.ceil((x.getTime()-new Date().getTime()) / 60000.0);
        $('.timer').html("Next update in "+(y>0 ? (y>1 ? y+" minutes" : "1 minute") : "an hour"));
	setTimeout(updateTimer, 60000);
	if (y==0) {
		updateTable();
	}
}

var updatedSpringAt;

function updateSpringTimer () {
	if (updatedSpringAt != null) {
		var next = Math.round((updatedSpringAt.getTime()+120000-new Date().getTime())/1000);
		$('#springTimer').html("Next update in "+(next>0 ? (next>1 ? next+" seconds" : "1 second") : "soon"));
	}
	setTimeout(updateSpringTimer, 1000);
}

var unitButtonsOn = [true, false, false, false];

var unit = 0;

function switchUnits (unitIndex) {
	console.log("asd");
	unit = unitIndex;
	logAction('league switch_units_to: ' + unit);
	for (var i = 0; i < unitButtonsOn.length; i++) {
		if (unitIndex == i) {
			unitButtonsOn[i] = true;
		} else {
			unitButtonsOn[i] = false;
		}
                $('#unitButton'+i).toggleClass('unitButtonOn', unitButtonsOn[i]);
		$('#unitButton'+i).toggleClass('unitButtonOff', !unitButtonsOn[i]);
        }
	updateTable();
}

function getUnitsHTML (value, average) {
	var unitHtml;
	if (unit == 0) {
        	unitHtml = value.toFixed(2)+"KWh";
        } else if (unit == 1) {
        	unitHtml = "&pound;"+(value*0.1285).toFixed(2);                  
        } else if (unit == 2) {
                unitHtml = (value*0.54522).toFixed(2)+"Kg CO<sub>2</sub>";
        } else {
                unitHtml = Math.round((value/average).toFixed(2)*100)+"%";
        }
	return unitHtml;
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

<![if !IE]>
<script	type="text/javascript">
function updateTable () {
	logAction('league update_start');
	d3.json("cumul_data.php", function(json) {
		if (json.consumption != null) {
	                var j = 0;	
			var unitHtml;
			$('#a').slideUp(1000, function() {
                	        $('#a').html("Average = <span style='font-weight: bold;'>"+getUnitsHTML(json.consumption.average, json.consumption.average)+"</span>");
				$(this).slideDown(1000);
                	});
			for (var i = 0; i < 3; i++) {
				$('#l'+i).slideUp(1000);
                	        $('#h'+i).slideUp(1000, function() {
                        	        $('#h'+j).html(
                                	        "<a href='live.php?hubId="+
	                                        json.consumption[j].hubId+
        	                                "&sensorId=undefined'>"+
                	                        json.consumption[j].description+
                        	                "</a> = <span style='font-weight: bold;'>"+
                                	        getUnitsHTML(parseFloat(json.consumption[j].total), json.consumption.average)+
                                        	"</span>"
	                                );
					if (json.consumption[j].total-json.consumption.average > 0) {
                	                        $('#h'+j).slideDown();
                        	        }
	
	                                $('#l'+j).html(
        	                                "<a href='live.php?hubId="+
                	                        json.consumption[json.consumption.count - (3 - j)].hubId+
                        	                "&sensorId=undefined'>"+
                                	        json.consumption[json.consumption.count - (3 - j)].description+
                                        	"</a> = <span style='font-weight: bold;'>"+
	                                        getUnitsHTML(parseFloat(json.consumption[json.consumption.count - (3 - j)].total), json.consumption.average)+
        	                                "</span>"
                	                );
					if (json.consumption[json.consumption.count - (3 - j)].total-json.consumption.average < 0) {
                                	        $('#l'+j).slideDown();
	                                } 
					j++;
                	        });
	                }
			logAction('league update_complete');
		} else {
			logAction('league update_fail');
		}
	});
}
</script>
<![endif]>

<!--[if gte IE 9]>
<script	type="text/javascript">
function updateTable () {
        logAction('league update_start');
        d3.json("cumul_data.php", function(json) {
                if (json.consumption != null) {
                        var j = 0;
                        var unitHtml;
                        $('#a').slideUp(1000, function() {
                                $('#a').html("Average = <span style='font-weight: bold;'>"+getUnitsHTML(json.consumption.average, json.consumption.average)+"</span>");
                                $(this).slideDown(1000);
                        });
			for (var i = 0; i < 3; i++) {
                                $('#l'+i).slideUp(1000);
                                $('#h'+i).slideUp(1000, function() {
                                        $('#h'+j).html(
                                                "<a href='live.php?hubId="+
                                                json.consumption[j].hubId+
                                                "&sensorId=undefined'>"+
                                                json.consumption[j].description+
                                                "</a> = <span style='font-weight: bold;'>"+
                                                getUnitsHTML(parseFloat(json.consumption[j].total), json.consumption.average)+
                                                "</span>"
                                        );
                                        if (parseFloat(json.consumption[j].total)-json.consumption.average > 0) {
                                                $('#h'+j).slideDown();
                                        }

                                        $('#l'+j).html(
                                                "<a href='live.php?hubId="+
                                                json.consumption[json.consumption.count - (3 - j)].hubId+
                                                "&sensorId=undefined'>"+
                                                json.consumption[json.consumption.count - (3 - j)].description+
                                                "</a> = <span style='font-weight: bold;'>"+
                                                getUnitsHTML(parseFloat(json.consumption[json.consumption.count - (3 - j)].total), json.consumption.average)+
                                                "</span>"
                                        );
                                        if (parseFloat(json.consumption[json.consumption.count - (3 - j)].total)-json.consumption.average < 0) {
                                                $('#l'+j).slideDown();
                                        }
                                        j++;
                                });
                        }
                        logAction('league update_complete');
                } else {
                        logAction('league update_fail');
                }
        });
}
</script>
<![endif]-->	

<script type="text/javascript">
function updateValue () {
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
                setTimeout(updateValue, 5000);
        });
}

var cost = true;

function switchUnits () {
        if (cost) cost = false;
        else cost = true;
        setTimeout(switchUnits, 15000);
}
</script>

<!--[if (IE)&(lt IE 9)]>
<script type="text/javascript">
logAction('limited_view');

function updateTable () {
        logAction('league update_start');
        d3.json("cumul_data.php", function(json) {
                if (json.consumption != null) {
                        var j = 0;
                        var unitHtml;
                        $('#a').slideUp(1000, function() {
                                $('#a').html("Average = <span style='font-weight: bold;'>"+getUnitsHTML(json.consumption.average, json.consumption.average)+"</span>");
                                $(this).slideDown(1000);
                        });
                        for (var i = 0; i < 3; i++) {
                                $('#l'+i).slideUp(1000);
                         	$('#h'+i).slideUp(1000, function() {
                                        $('#h'+j).html(
                                                "<a>"+
                                                json.consumption[j].description+
                                                "</a> = <span style='font-weight: bold;'>"+
                                                getUnitsHTML(parseFloat(json.consumption[j].total), json.consumption.average)+
                                                "</span>"
                                        );
                                        if (parseFloat(json.consumption[j].total)-json.consumption.average > 0) {
                                                $('#h'+j).slideDown();
                                        }
                                        $('#l'+j).html(
                                                "<a>"+
                                                json.consumption[json.consumption.count - (3 - j)].description+
                                                "</a> = <span style='font-weight: bold;'>"+
                                                getUnitsHTML(parseFloat(json.consumption[json.consumption.count - (3 - j)].total), json.consumption.average)+
                                                "</span>"
                                        );
                                        if (parseFloat(json.consumption[json.consumption.count - (3 - j)].total)-json.consumption.average < 0) {
                                                $('#l'+j).slideDown();
					}
                                        j++;
                                });
                        }
                        logAction('league update_complete');
                } else {
                        logAction('league update_fail');
                }
        });
}
</script>
<![endif]-->
</head>
<body onclick="void(0)" onload="refreshDropdowns(); updateTimer(); updateTable(); switchUnits(0); updateSpringTimer(); updateValue()">

<div class="about" id="about">
<a title="How do I use this website?" href="about.php?concept=help" target="blank" onclick="logAction('click help')">Help</a>
</div>
<div class="status" id="status">
<a id="statusLink" title="Is my home connected to the Neighbourhood?" href="about.php?concept=status" target="blank" onclick="logAction('click status')">Status</a>
</div>
<div class="logo">
<a title="What is Electric20?" href="about.php?concept=electric20" target="blank" onclick="logAction('click electric20')"><img src="Electric20.png" /></a>
</div>

<div class="tabOn">What's happening right now?</div>

<![if !IE]>
<div class="tabOff"><a href="graphT.php" onclick="logAction('click_tab directory')">Neighbourhood directory</a></div>
<div class="tabOff"><a href="progress.php" onclick="logAction('click_tab progress')">Progress</a></div>
<![endif]>

<!--[if gte IE 9]>
<div class="tabOff"><a href="graphT.php" onclick="logAction('click_tab directory')">Neighbourhood directory</a></div>
<div class="tabOff"><a href="progress.php" onclick="logAction('click_tab progress')">Progress</a></div>
<![endif]-->

<div class="container" style="overflow: hidden;">
<div class="innertext_hor">

<![if !IE]>
<div class="innercontainer" style="margin: 0px 20px 20px 0px; padding: 20px; -moz-border-radius: 20px 0px 0px 20px; border-radius: 20px 0px 0px 20px; background-color: #FECA98;">
<div id="big" style="font-size: 15pt; font-weight: bold; text-align: center;">
<a href="live.php?hubId=<?php echo $myHubId; ?>&sensorId=undefined">Your home is currently using <span id="value">?</span></a>
</div>
</div>

<div class="rightNow">
<div class="springTimer" id="springTimer"></div>
<h1 style="text-align: right; text-transform: uppercase;">Right now
<div style="float: right; border-color: white white white black; margin-left: 10px; border-style: solid; border-width: 10px; width:0; height:0;"></div>
</h1>
<p>The diagram to the right shows all the households in the neighbourhood that have used electricity in the last two minutes.</p>
<ul>
<li id="dd0"></li>
</ul>
<p>The diagram will update every couple of minutes to use the most recent data from the neighbourhood.</p>
<div style="float: left; border-color: white orange white white; margin-right: 10px; border-style: solid; border-width: 10px; width:0; height:0;"></div>
<div><a href="overview.php" style="color: orange;" onclick="logAction('click overview')">Return to the overview</a></div>
</div>
<![endif]>

<!--[if gte IE 9]>
<div class="innercontainer" style="margin: 0px 20px 20px 0px; padding: 20px; -moz-border-radius: 20px 0px 0px 20px; border-radius: 20px 0px 0px 20px; background-color: #FECA98;">
<div id="big" style="font-size: 15pt; font-weight: bold; text-align: center;">
<a href="live.php?hubId=<?php echo $myHubId; ?>&sensorId=undefined">Your home is currently using <span id="value">?</span></a>
</div>
</div>

<div class="rightNow">
<div class="springTimer" id="springTimer"></div>
<h1 style="text-align: right; text-transform: uppercase;">Right now
<div style="float: right; border-color: white white white black; margin-left: 10px; border-style: solid; border-width: 10px; width:0; height:0;"></div>
</h1>                   
<p>The diagram to the right shows the network of all the households in the neighbourhood that have used electricity in the last two minutes.</p>
<ul>
<li id="dd0"></li>
<li id="dd1"></li>              
<li id="dd2"></li>
</ul>                   
<p>The diagram will update every couple of minutes to use the most recent data from the neighbourhood. You can drag any part of the diagram to untangle it or help make 
comparison$
<div style="float: left; border-color: white orange white white; margin-right: 10px; border-style: solid; border-width: 10px; width:0; height:0;"></div>
<div><a href="overview.php" style="color: orange;" onclick="logAction('click overview')">Return to the overview</a></div>
</div>                  
<![endif]-->

<!--[if lt IE 9]>
<div class="rightNow">
<h1 style="text-align: right; text-transform: uppercase;">Right now
<div style="float: right; border-color: black white white white; margin-left: 10px; border-style: solid; border-width: 10px; width:0; height:0;"></div>
</h1>
<p>The amount of electricity being used by your home right now is shown below. This figure will update in real-time to show how your consumption changes.</p>
<div id="big" style="font-size: 60pt; font-weight: bold; text-align: center;">
<span id="value">Please wait</span>
</div>
<div id="small" style="font-size: 20pt; text-align: center;">
<span id="cost"></span>
</div>
</div>
<![endif]-->

<div class="innercontainer" style="margin: 20px 20px 0px 0px; padding: 20px; -moz-border-radius: 20px 0px 0px 20px; border-radius: 20px 0px 0px 20px; background-color: #FECA98;">
<div class="timer"></div>
<h2>
So far today
<div style="float: left; border-color: black #FECA98 #FECA98 #FECA98; margin-right: 10px; border-style: solid; border-width: 10px; width:0; height:0;"></div>
</h2>
<div id="dd3"></div>
<div>
<a id="unitButton0" onclick="switchUnits(0)" class="unitButtonOff">Energy</a>
<a id="unitButton1" onclick="switchUnits(1)" class="unitButtonOff">Cost</a>
<a id="unitButton2" onclick="switchUnits(2)" class="unitButtonOff">Emissions</a>
<a id="unitButton3" onclick="switchUnits(3)" class="unitButtonOff">Percent</a>
</div>
<div id="l2" class="lowExtreme">Please wait</div>
<div id="l1" class="lowExtreme">Please wait</div>
<div id="l0" class="lowExtreme">Please wait</div>
<div id="a" class="averageExtreme">Average</div>
<div id="h2" class="highExtreme">Please wait</div>
<div id="h1" class="highExtreme">Please wait</div>
<div id="h0" class="highExtreme">Please wait</div>
</div>
</div>
<div class="innercontainer_hor" id="chart" style="-moz-border-radius: 0px 20px 20px 0px; border-radius: 0px 20px 20px 0px;">

<![if !IE]>
<script type="text/javascript">
	var w = document.body.clientWidth * 0.60;
	var h = $(window).height() * 0.9 - 30;

	$('#chart').css({
                height: ($(window).height() * 0.9 - 30)+"px"
        });


var vis = d3.select("#chart").append("svg:svg")
                .attr("width", w)
                .attr("height", h);

var force;

var info;

redrawGraph();

function redrawGraph() {
	resetStatus();

	logAction('springy update_start');

	d3.json("graph_data.php", function(json) {
		if (info != null) {
             		info.remove();
              	}

		force = d3.layout.force()
                        .nodes(json.data)
                        .links(json.links)
                        .gravity(.05)
                        .distance(130)
                        .charge(-300)
                        .size([w, h])
                        .start();

		vis.selectAll("line.link").remove();
		vis.selectAll("g.node").remove();

    		var link = vis.selectAll("line.link")
        		.data(json.links, function (d) {return d.source+d.target+d.value;})
        		.enter().append("svg:line")
        		.attr("class", "link")
			.style("stroke-width", function(d) { return Math.sqrt(d.value) + 1; })
        		.attr("x1", function(d) { return d.source.x; })
        		.attr("y1", function(d) { return d.source.y; })
        		.attr("x2", function(d) { return d.target.x; })
        		.attr("y2", function(d) { return d.target.y; });

    		var node = vis.selectAll("g.node")
        		.data(json.data, function(d) {return d.label+d.value;})
      			.enter().append("svg:g")
        		.attr("class", "node")
        		.call(force.drag);

    		node.append("svg:circle")
			.style("fill", function(d) { return d.rgb >= 0 ? /*d3.rgb(d.rgb, 0, 0)*/ "#777" : "#FFA54F"; })
//			.style("fill", "#000")
			.style("stroke", "#fff")
			.style("stroke-width", "2px")
        		.attr("x", "-8px")
        		.attr("y", "-8px")
			.attr("r", function(d) { return Math.max(Math.sqrt(d.value) / 2 + 2, 10); });

    		node.append("svg:text")
        		.attr("class", "nodetext")
			.style("font-size", function(d) { return Math.max(Math.sqrt(d.value) / 1.5, 7)+"px"; })
        		.attr("dx", 12)
        		.attr("dy", ".35em")
        		.text(function(d) { return d.label; });

		node.on("click", function (d) {
			logAction('node_click hubId: '+d.hubId+' sensorId: '+d.sensorId);
			if (d.rgb < 0) {
				self.location = "detailed.php?hubId="+d.hubId+"&sensorId="+d.sensorId;
			} else {
				self.location = "live.php?hubId="+d.hubId+"&sensorId="+d.sensorId;
			}
		});
/*
		node.on("click", function (d) {
			if (navigator.userAgent.match(/iPad/i) != null) {
				logAction('node_click hubId: '+d.hubId+' sensorId: '+d.sensorId);
				if (d.rgb < 0) {
	                                self.location =	"detailed.php?hubId="+d.hubId+"&sensorId="+d.sensorId;
        	                } else {
                	                self.location = "live.php?hubId="+d.hubId+"&sensorId="+d.sensorId;
                        	}
			}
		});*/

		node.on("mouseover", function (d) {
			logAction('node_mouseover hubId: '+d.hubId+' sensorId: '+d.sensorId);

			node.filter(function (e) { return e.hubId == d.hubId && e.sensorId == d.sensorId; }).transition().duration(300).attr("fill","#f00");
			link.filter(function (e) { return e.source.index == d.index; }).transition().duration(300).style("stroke","#f00");

			var content = d.label+": "+d.value+"W ("+Math.round(100*d.value/json.data[0].value)+"% of neighbourhood's current electricity consumption)";

			if (info == null) {
				info = vis.append("svg:text")
                                	.text(content)
					.attr("x", 20)
	                                .attr("y", h - 20);
			} else {
				info.transition().duration(1000).text(content);
			}
		})
		.on("mouseout", function (d) {
			node.filter(function (e) { return e.hubId == d.hubId && e.sensorId == d.sensorId; }).transition().duration(300).attr("fill","#000");
			link.filter(function (e) { return e.source.index == d.index; }).transition().duration(300).style("stroke","#ccc");
		});

    		force.on("tick", function() {
      			link.attr("x1", function(d) { return d.source.x; })
          			.attr("y1", function(d) { return d.source.y; })
          			.attr("x2", function(d) { return d.target.x; })
          			.attr("y2", function(d) { return d.target.y; });

      			node.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });
    		});

		updatedSpringAt = new Date();

		setTimeout("redrawGraph()", 120000);

		logAction('springy update_complete');
	});
}
</script>
<![endif]>

<!--[if gte IE 9]>
<script type="text/javascript">
        var w = document.body.clientWidth * 0.60;
        var h = $(window).height() * 0.9 - 30;

        $('#chart').css({                                                       
                height: ($(window).height() * 0.9 - 30)+"px"
        });


var vis = d3.select("#chart").append("svg:svg")
                .attr("width", w)
                .attr("height", h);

var force;

var info;

redrawGraph();

function redrawGraph() {
        resetStatus();

        logAction('springy update_start');

        d3.json("graph_data.php", function(json) {
                if (info != null) {
                        info.remove();
}
                        
                force = d3.layout.force()
                        .nodes(json.data)
                        .links(json.links)
                        .gravity(.05)
                        .distance(130)
                        .charge(-300)
                        .size([w, h])
                        .start();

                vis.selectAll("line.link").remove();
                vis.selectAll("g.node").remove();
                                
                var link = vis.selectAll("line.link")
                        .data(json.links, function (d) {return d.source+d.target+d.value;})
                        .enter().append("svg:line")
                        .attr("class", "link")
                        .style("stroke-width", function(d) { return Math.sqrt(d.value) + 1; })
                  	.attr("x1", function(d) { return d.source.x; })
                        .attr("y1", function(d) { return d.source.y; })
                        .attr("x2", function(d) { return d.target.x; })
                        .attr("y2", function(d) { return d.target.y; });
                        
                var node = vis.selectAll("g.node")
                        .data(json.data, function(d) {return d.label+d.value;})
                        .enter().append("svg:g")
                        .attr("class", "node")
                        .call(force.drag);

                node.append("svg:circle")  
                        .style("fill", function(d) { return d.rgb >= 0 ? /*d3.rgb(d.rgb, 0, 0)*/ "#777" : "#FFA54F"; })
//                      .style("fill", "#000")
                        .style("stroke", "#fff")
                        .style("stroke-width", "2px")
                        .attr("x", "-8px")
                        .attr("y", "-8px")
                        .attr("r", function(d) { return Math.sqrt(d.value) / 2 + 2; });
                  
                node.append("svg:text")
                        .attr("class", "nodetext")
                        .style("font-size", function(d) { return Math.max(Math.sqrt(d.value) / 1.5, 7)+"px"; })
                        .attr("dx", 12)
                        .attr("dy", ".35em")
                        .text(function(d) { return d.label; });
                        
                node.on("click", function (d) {
                        logAction('node_click hubId: '+d.hubId+' sensorId: '+d.sensorId);
                        if (d.rgb < 0) {
          	                self.location = "detailed.php?hubId="+d.hubId+"&sensorId="+d.sensorId;
                        } else {
                                self.location = "live.php?hubId="+d.hubId+"&sensorId="+d.sensorId;
                        }
                });
                        /*
                node.on("click", function (d) {
                        if (navigator.userAgent.match(/iPad/i) != null) {
                                logAction('node_click hubId: '+d.hubId+' sensorId: '+d.sensorId);
                                if (d.rgb < 0) {
                                        self.location = "detailed.php?hubId="+d.hubId+"&sensorId="+d.sensorId;
                                } else {
                                        self.location = "live.php?hubId="+d.hubId+"&sensorId="+d.sensorId;
                                }
                  	}
                });*/
        
                node.on("mouseover", function (d) {
			logAction('node_mouseover hubId: '+d.hubId+' sensorId: '+d.sensorId);
        
                        node.filter(function (e) { return e.hubId == d.hubId && e.sensorId == d.sensorId; }).transition().duration(300).attr("fill","#f00");
                        link.filter(function (e) { return e.source.index == d.index; }).transition().duration(300).style("stroke","#f00");
                
          	        var content = d.label+": "+d.value+"W ("+Math.round(100*d.value/json.data[0].value)+"% of neighbourhood's current electricity consumption)";

                        if (info == null) {
                                info = vis.append("svg:text")
                                        .text(content)
                                        .attr("x", 20)
                                        .attr("y", h - 20);
                        } else {
                                info.transition().duration(1000).text(content);
                        }
                })      
                .on("mouseout", function (d) {
                        node.filter(function (e) { return e.hubId == d.hubId && e.sensorId == d.sensorId; }).transition().duration(300).attr("fill","#000");
                        link.filter(function (e) { return e.source.index == d.index; }).transition().duration(300).style("stroke","#ccc");
                });
                        
                force.on("tick", function() {
                        link.attr("x1", function(d) { return d.source.x; })
                                .attr("y1", function(d) { return d.source.y; })
                                .attr("x2", function(d) { return d.target.x; })
				.attr("y2", function(d) { return d.target.y; });
                
                        node.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });
                });
                        
                updatedSpringAt = new Date();
                        
                setTimeout("redrawGraph()", 120000);
                        
                logAction('springy update_complete');
        });
}
</script>                       
<![endif]-->

<!--[if (IE)&(lt IE 9)]>
<div style="padding: 30px; color: black;">
<h1>You could see a lot more ...</h1>
<p>Web browsing applications (like Windows Internet Explorer that you are using to view this web-page) have come a long way since the early days of the 
Web. The Electric20 web-site uses some of the latest features of these applications to give you dynamic, interactive visualisations of the Neighbourhood's electricity 
data.</p>
<p>Unfortunately, the version of Windows Internet Explorer that you are using is relatively old and does not support these 
features. This means that you can access only a <span style="font-style: italic;">very</span> limited set of the visualisations Electric20 offer. Fortunately, Microsoft 
provides a 
free upgrade to 
the latest version of Windows Internet Explorer that has much better support for Electric20 (and many other modern web-sites). On the other hand there is also a choice of 
other great free web browsers out there, all of which support the latest web features that Electric20 makes use of (and all of which are happily compatible with Windows 
PCs or Apple Macs). For your convenience 
we've provided the links to the 
latest version of Windows Internet Explorer, as well as the other web browsers recommended by Electric20:
</p>
<ul>
<li><a class="dark" href="http://windows.microsoft.com/en-GB/internet-explorer/downloads/ie-9/worldwide-languages" onclick="logAction('click get_ie9')">Microsoft 
Windows Internet Explorer 9</a></li>
<li><a class="dark" href="http://www.mozilla.com/" onclick="logAction('click get_mozilla')">Mozilla Firefox</a></li>
<li><a class="dark" href="http://www.google.com/chrome" onclick="logAction('click get_chrome')">Google Chrome</a></li>
<li><a class="dark" href="http://www.apple.com/safari/" onclick="logAction('click get_safari')">Apple Safari</a></li>
</ul>
<div style="float: left; border-color: white orange white white; margin-right: 10px; border-style: solid; border-width: 10px; width:0; height:0;"></div>
<div><a href="overview.php" style="color: orange;" onclick="logAction('click overview')">Return to the overview</a></div>
</div>
<script type="text/javascript">
resetStatus();
</script>
<![endif]-->
</div>
</div>
</body>
</html>
