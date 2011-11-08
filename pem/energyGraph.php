<?php
    ini_set('display_errors',1);
	error_reporting(E_ALL|E_STRICT);
    include '/var/www/dataStore/dataAccess/db_connect.php';
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
    mysql_select_db($database);

	$vid = $_GET['vid'];
	$start = $_GET['start'];
	$end = $_GET['end'];
	$description = $_GET['description'];
	$data = array();

		
			$results = mysql_query("SELECT UNIX_TIMESTAMP(timeStamp) AS time, SUM(loadAvg) as loadAvg
				from tblVenue 
				left join tblGroupVenues using(venueId) 
				left join tblHubGroup using(groupId) 
				left join tblLoadAgg using(hubId)
				where venueId = $vid
				and timeStamp between '$start' and '$end'
				group by timeStamp
				order by timeStamp asc;");
	
	while ($row = mysql_fetch_assoc($results)) 
	{
			$data[] = array("x" => $row['time'],"y" => $row['loadAvg']);
    }
?>

<html>
<head>
    <script type="text/javascript" src="js/protovis-d3.2.js"></script>
<link href="graphStyle.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div class="container">
<h1>Energy data from <?php echo($description . " between " . $start . " and " .$end)?></h1>
<p>These graphs show detailed data for the above venue at the time you present within it. The <span style="font-weight: bold;">upper larger graph</span> gives a detailed view of energy use in a particular time window; the <span style="font-weight: bold;">lower smaller graph</span> gives a less detailed view of <span style="font-style: italic;">all</span> energy use since the hub/sensor was commissioned. Drag the pink shaded area in the lower smaller graph to shift the time window, or click and drag elsewhere within the lower graph to create a new time window.
</p>
<p><a href="locationTrail.html">Back to energy location visualization</a></p>
<div class="innercontainer">
<script type="text/javascript+protovis">

var data = <?php echo json_encode($data); ?>;
for(var i in data) {
	date = new Date();
	date.setTime(data[i]['x']*1000);
	data[i]['x'] = date;
}
var start = data[0]['x'];
var end = data[data.length - 1]['x'];

var i = -1;

/* Scales and sizing. */
var w = document.body.clientWidth * 0.9,
    h1 = document.body.clientHeight * 0.70 - 30,
    h2 = 30,
    x = pv.Scale.linear(start, end).range(0, w),
    y = pv.Scale.linear(0, pv.max(data, function(d) d.y) * 1.05).range(0, h2);


/* Interaction state. Focus scales will have domain set on-render. */
var i = {x:document.body.clientWidth * 0.9 - 100, dx:100},
    fx = pv.Scale.linear().range(0, w),
    fy = pv.Scale.linear().range(0, h1);

/* Root panel. */
var vis = new pv.Panel()
    .width(w)
    .height(h1 + 20 + h2)
    .bottom(20)
    .left(40)
    .right(20)
    .top(5);

/* Focus panel (zoomed in). */
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

	  
