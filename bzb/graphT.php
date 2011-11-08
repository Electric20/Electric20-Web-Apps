
<?php

	session_start();

	require_once('../dataStore/utility.php');

	logAction('arrive');

	if (!isset($_SESSION['loggedIn'])) {
		logAction('noauth');
		header('Location: http://www.electric20.com/bzb/login.php?to=graphT');
	}

?>

<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7" >
	<script type="text/javascript" src="sizzle.js"></script>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="d3.js"></script>
	<script type="text/javascript" src="d3.layout.js"></script>
	<script type="text/javascript" src="d3.geom.js"></script>
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
	<style type="text/css">
.node circle {
  fill: #fff;
  stroke: steelblue;
  stroke-width: 1.5px;
}

.decomNode circle {
	fill: #fff;
	stroke: gray;
	opacity: 0.5;
	stroke-width: 1.5px;
}

.node, .decomNode {
  font: 10px sans-serif;
}

.link {
  fill: none;
  stroke: #ccc;
  stroke-width: 1.5px;
}
	</style>
</head>
<body onclick="void(0)">
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
<div class="tabOn">Neighbourhood directory</div>
<div class="tabOff"><a href="progress.php" onclick="logAction('click_tab progress')">Progress</a></div>
<div class="container" style="overflow: hidden;">
<div class="innertext">
<h1>The Neighbourhood</h1>
<p>This chart shows all neighbours that have ever been connected to the neighbourhood electricity network. Double-clicking on a node will give access to detailed data 
from that home or sensor within the home.</p>
<p>
<div style="float: left; border-color: #FFA54F white #FFA54F #FFA54F; margin-right: 10px; border-style: solid; border-width: 10px; width:0; height:0;"></div>
<div><a href="overview.php" onclick="logAction('click overview')">Return to the overview</a></div>
</p>
</div>
<div class="innercontainer" id="chart">
<script type="text/javascript">

	var w = document.body.clientWidth * 0.95;
	var h = $(window).height() * 2;

	$('#chart').css({
                height: $(window).height()*2+"px"
        });

var cluster = d3.layout.cluster()
	.size([h, w - 270]);

var diagonal = d3.svg.diagonal()
	.projection(function(d) { return [d.y, d.x]; });

var vis = d3.select("#chart").append("svg:svg")
                .attr("width", w)
                .attr("height", h)
		.append("svg:g")
		.attr("transform", "translate(80,0)");

redrawGraph();

function redrawGraph() {
	logAction('directory update_start');
	resetStatus();
	d3.json("graphT_data.php", function(json) {
		var nodes = cluster.nodes(json);

		var link = vis.selectAll("path.link")
			.data(cluster.links(nodes))
			.enter().append("svg:path")
			.attr("class", "link")
			.attr("d", diagonal);

		var node = vis.selectAll("g.node")
			.data(nodes)
			.enter().append("svg:g")
			.attr("class", function (d) { return d.decommissioned ? "decomNode" : "node"; })
			.attr("transform", function(d) { return "translate(" + d.y + "," + d.x + ")"; });

		node.append("svg:circle")
			.attr("r", 4.5);

		node.append("svg:text")
			.attr("dx", function(d) { return d.children ? -8 : 8; })
			.attr("dy", 3)
			.attr("text-anchor", function(d) { return d.children ? "end" : "start"; })
			.text(function(d) { return d.name; });

		node.filter(function (d) { return d.decommissioned; }).attr("fill", "#ccc");

		node.on("dblclick", function (d) {
			logAction('node_click hubId: '+d.hubId+' sensorId: '+d.sensorId);
                        self.location = "live.php?hubId="+d.hubId+"&sensorId="+d.sensorId;
                });

                node.on("click", function (d) {
                        if (navigator.userAgent.match(/iPad/i) != null) {
				logAction('node_click hubId: '+d.hubId+' sensorId: '+d.sensorId);
                                self.location = "live.php?hubId="+d.hubId+"&sensorId="+d.sensorId;
                        }
                });

		node.on("mouseover", function (d) {
			logAction('node_mouseover hubId: '+d.hubId+' sensorId: '+d.sensorId);
                        node.filter(function (e) { return e.hubId == d.hubId && e.sensorId == d.sensorId; }).transition().duration(300).attr("fill","#f00");
                        link.filter(function (e) { return e.source.hubId == d.hubId && e.source.sensorId == d.sensorId; }).transition().duration(300).style("stroke","#f00");
                })
                .on("mouseout", function (d) {
                        node.filter(function (e) { return e.hubId == d.hubId && e.sensorId == d.sensorId; }).transition().duration(300).attr("fill", "#000");
			node.filter(function (e) { return e.hubId == d.hubId && e.sensorId == d.sensorId && e.decommissioned == true; 
}).transition().duration(300).attr("fill", "#ccc");
                        link.filter(function (e) { return e.source.index == d.index; }).transition().duration(300).style("stroke","#ccc");
                });

		setTimeout("redrawGraph()", 3600000);
	
		logAction('directory update_complete');
	});
}
</script>
</div>
</div>
</body>
</html>
