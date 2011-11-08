<?php
	session_start();

	require_once('../dataStore/utility.php');

	logAction('arrive');

	if (!isset($_SESSION['loggedIn'])) {
		logAction('noauth');
		header('Location: http://www.electric20.com/bzb/login.php?to=detailed');
	} else {
		require_once("../dataStore/query.php");

		$data = request(array('action'=>"hubHistory", 'hubId'=>$_GET['hubId'], 'sensorId'=>$_GET['sensorId']));
	}
?>

<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7" >
    	<script type="text/javascript" src="protovis-d3.2.js"></script>
	<link href="style.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript">

function updateTimer () {
        var x = new Date();
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
	if (y == 0) {
		location.reload(true);
	}
}

function setDescription () {
	resetStatus();
	if (<?php echo "\"".$_GET['hubId']."\""; ?> != "undefined") {
        	$.getJSON('desc_data.php?hubId='+<?php echo $_GET['hubId']; ?>+'&sensorId='+<?php echo $_GET['sensorId']; ?>, function (data) {
                	$('#homeName').html(data.data.hDescription);
	                $('#titleHomeName').html(data.data.hDescription);
        	        if (<?php echo $_GET['sensorId']; ?> != undefined) {
                	        $('#sensorName').html(": "+data.data.sDescription);
                        	$('#titleSensorName').html(": "+data.data.sDescription);
	                }
        	});
	} else {
		$('#homeName').html("the Neighbourhood");
                $('#titleHomeName').html("the Neighbourhood");
		$('#tabOff').html("Live (not available)");
		$('#tabOff').css("background-color", "#ccc");
	}
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
</head>
<body onclick="void(0)" onload="updateTimer(); setDescription()">
<div class="about" id="about">
<a title="How do I use this website?" href="about.php?concept=help" target="blank">Help</a>
</div>
<div class="status" id="status">
<a id="statusLink" title="Is my home connected to the Neighbourhood?" href="about.php?concept=status" target="blank" onclick="logAction('click status')">Status</a>
</div>
<div class="logo">
<a title="What is Electric20?" href="about.php?concept=electric20" target="blank"><img src="Electric20.png" /></a>
</div>

<div class="tabOff" id="tabOff"><a href="live.php?hubId=<?php echo $_GET['hubId']; ?>&sensorId=<?php echo $_GET['sensorId']; ?>">Live</a></div>
<div class="tabOn">Last 7 days</div>
<div class="container">
<h1>Last 7 days (<span id="titleHomeName"></span><span id="titleSensorName"></span>)</h1>
<p>These graphs show detailed data about <span id="homeName"></span><span id="sensorName"></span> for the 
last 7 days. 
The 
<span 
style="font-weight: bold;">upper larger graph</span> 
gives a detailed 
view of 
energy use in a particular 
time window; the <span style="font-weight: 
bold;">lower smaller graph</span> allows you to move that time window to any position within the last 7 days.
Try dragging the pink shaded area in the lower smaller graph 
to shift the 
time window, or click and drag elsewhere within the lower graph to create a new time window.</p>
<p>
        <div style="float: left; border-color: #FFA54F white #FFA54F #FFA54F; margin-right: 10px; border-style: solid; border-width: 10px; width:0; height:0;"></div>
        <a href="graph.php">Return to the neighbourhood</a></p>
<div class="innercontainer" style="padding: 20px;">
<div class="timer" style="float: left;"></div>
<script type="text/javascript+protovis">

var data = <?php echo json_encode($data['data']); ?>;
for(var i in data) {
	date = new Date();
	date.setTime(data[i]['x']*1000);
	data[i]['x'] = date;
}
var start = data[0]['x'];
var end = data[data.length - 1]['x'];

var i = -1;

var w = document.body.clientWidth * 0.875,
    h1 = document.body.clientHeight * 0.65 - 60,
    h2 = 30,
    x = pv.Scale.linear(start, end).range(0, w),
    y = pv.Scale.linear(0, pv.max(data, function(d) d.y) * 1.05).range(0, h2);

var i = {x:document.body.clientWidth * 0.875 - 100, dx:100},
    fx = pv.Scale.linear().range(0, w),
    fy = pv.Scale.linear().range(0, h1);

var vis = new pv.Panel()
    .width(w)
    .height(h1 + 20 + h2)
    .bottom(20)
    .left(35)
    .right(0)
    .top(0);

var focus = vis.add(pv.Panel)
    .def("init", function() {
        var d1 = x.invert(i.x),
            d2 = x.invert(i.x + i.dx),
            dd = data.slice(
                Math.max(0, pv.search.index(data, d1, function(d) d.x) - 1),
                pv.search.index(data, d2, function(d) d.x) + 1);
        fx.domain(d1, d2);
        fy.domain(false ? [0, pv.max(dd, function(d) d.y)] : y.domain());
        return dd;
      })
    .top(0)
    .height(h1);

/* X-axis ticks. */
focus.add(pv.Rule)
    .data(function() fx.ticks())
    .left(fx)
    .strokeStyle("#eee")
  .anchor("bottom").add(pv.Label)
    .text(fx.tickFormat);

/* Y-axis ticks. */
focus.add(pv.Rule)
    .data(function() fy.ticks(7))
    .bottom(fy)
    .strokeStyle(function(d) d ? "#aaa" : "#000")
  .anchor("left").add(pv.Label)
    .text(fy.tickFormat);

/* Focus area chart. */
focus.add(pv.Panel)
    .overflow("hidden")
  .add(pv.Area)
    .data(function() focus.init())
    .left(function(d) fx(d.x))
    .bottom(1)
    .height(function(d) fy(d.y))
    .fillStyle("lightsteelblue")
  .anchor("top").add(pv.Line)
    .fillStyle(null)
    .strokeStyle("steelblue")
    .lineWidth(2);

/* Context panel (zoomed out). */
var context = vis.add(pv.Panel)
    .bottom(0)
    .height(h2);

/* X-axis ticks. */
context.add(pv.Rule)
    .data(x.ticks())
    .left(x)
    .strokeStyle("#eee")
  .anchor("bottom").add(pv.Label)
    .text(x.tickFormat);

/* Y-axis ticks. */
context.add(pv.Rule)
    .bottom(0);

/* Context area chart. */
context.add(pv.Area)
    .data(data)
    .left(function(d) x(d.x))
    .bottom(1)
    .height(function(d) y(d.y))
    .fillStyle("lightsteelblue")
  .anchor("top").add(pv.Line)
    .strokeStyle("steelblue")
    .lineWidth(2);

/* The selectable, draggable focus region. */
context.add(pv.Panel)
    .data([i])
    .cursor("crosshair")
    .events("all")
    .event("mousedown", pv.Behavior.select())
    .event("select", focus)
  .add(pv.Bar)
    .left(function(d) d.x)
    .width(function(d) d.dx)
    .fillStyle("rgba(255, 128, 128, .4)")
    .cursor("move")
    .event("mousedown", pv.Behavior.drag())
    .event("drag", focus);

vis.render();
</script>

</div>
</div>
</body>
</html>
