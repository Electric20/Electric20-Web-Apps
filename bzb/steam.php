<?php
    include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
    mysql_select_db($database);

	$hubId = $_GET['hubId'];

	
	$sensorData = array();
	if ($hubId == "undefined") {
		$results = mysql_query("SELECT hubId, sensorId FROM tblSensorDir");
	} else {
		$results = mysql_query("SELECT hubId, sensorId FROM tblSensorDir WHERE hubId=".$hubId);
	}
	while ($row = mysql_fetch_assoc($results)) {
		$sensorData[] = array("hubId" => $row['hubId'], "sensorId" => $row['sensorId']);
    }
//	var_dump($sensorData);

	$data = array();
	if ($hubId == "undefined") {
		$results = mysql_query("SELECT * FROM (SELECT hubId, sensorId, loadAvg, UNIX_TIMESTAMP(timeStamp) AS time FROM tblLoadAgg ORDER BY time DESC LIMIT 2000) AS reverse ORDER BY time ASC");
		
		while ($row = mysql_fetch_assoc($results)) {
			$data[$row['time']][$row['hubId']] = $data[$row['time']][$row['hubId']] + (double)$row['loadAvg'];
	    }
		$final = array();
		$max = 0;
		foreach ($data as $i => $value) {
			$final[] = array();
			foreach ($sensorData as $j => $value) {
				if (isset($data[$i][$value['hubId']])) {
					$final[count($final)-1][] = $data[$i][$value['hubId']];
					if ($data[$i][$value['hubId']] > $max) $max = $data[$i][$value['hubId']];
				} else {
					if ($i > 0) {
						$data[$i][$value['hubId']] = $data[$i - 1][$value['hubId']];
						$final[count($final)-1][] = $data[$i - 1][$value['hubId']];
					} else {
						$data[$i][$value['hubId']] = 0.0;
						$final[count($final)-1][] = 0.0;
					}
				}
			}
		}
	} else {
		if ($hubId == 14) {
			$results = mysql_query("SELECT * FROM (SELECT sensorId, loadAvg, UNIX_TIMESTAMP(timeStamp) AS time FROM tblLoadAgg WHERE hubId=".$hubId." && sensorId!=0 ORDER BY time DESC LIMIT 2000) AS reverse ORDER BY time ASC");
		} else {
			$results = mysql_query("SELECT * FROM (SELECT sensorId, loadAvg, UNIX_TIMESTAMP(timeStamp) AS time FROM tblLoadAgg WHERE hubId=".$hubId." ORDER BY time DESC LIMIT 2000) AS reverse ORDER BY time ASC");
		}
		
		while ($row = mysql_fetch_assoc($results)) {
			$data[$row['time']][$row['sensorId']] = (double)$row['loadAvg'];
	    }
		$final = array();
		$max = 0;
		foreach ($data as $i => $value) {
			$final[] = array();
			foreach ($sensorData as $j => $value) {
				if (isset($data[$i][$value['sensorId']])) {
					$final[count($final)-1][] = $data[$i][$value['sensorId']];
					if ($data[$i][$value['sensorId']] > $max) $max = $data[$i][$value['sensorId']];
				} else {
					if ($i > 0) {
						$data[$i][$value['sensorId']] = $data[$i - 1][$value['sensorId']];
						$final[count($final)-1][] = $data[$i - 1][$value['sensorId']];
					} else {
						$data[$i][$value['sensorId']] = 0.0;
						$final[count($final)-1][] = 0.0;
					}
				}
			}
		}
	}
	//$results = mysql_query("SELECT UNIX_TIMESTAMP(timeStamp) AS time, loadAvg FROM tblLoadAgg WHERE hubId=".$hubId." ORDER BY time ASC");
	
	
	$layers = count(reset($data));
	
	$final2 = array();
	foreach ($final as $time => $sensors) {
		foreach($sensors as $i => $load) {
			$final2[$i][$time] = $load / $max * $layers;			
		}
	}
	
	//var_dump($data);
	
?>

<html>
<head>
    <script type="text/javascript" src="protovis-d3.2.js"></script>
<link href="style.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<div class="container">
	<h1>Detailed data</h1>
	<p>This graph shows energy use data for the different sensors comprising the chosen hub or group stacked upon one another. <a href="steam.php?hubId=<?php echo $_GET['hubId']; ?>&sensorId=<?php echo $_GET['sensorId']; ?>&offset=expand">Show expanded stacks</a>; <a href="steam.php?hubId=<?php echo $_GET['hubId']; ?>&sensorId=<?php echo $_GET['sensorId']; ?>&offset=wiggle">show wiggly stacks</a>; <a href="steam.php?hubId=<?php echo $_GET['hubId']; ?>&sensorId=<?php echo $_GET['sensorId']; ?>&offset=zero">show grounded stacks</a>; <a href="steam.php?hubId=<?php echo $_GET['hubId']; ?>&sensorId=<?php echo $_GET['sensorId']; ?>&offset=silohouette">show centred stacks</a>.</p>
	<p><a href="graph.php">Back to the energy monitor network</a></p>
	<div class="innercontainer">
<script type="text/javascript+protovis">

var n = <?php echo $layers; ?>, // number of layers
    m = <?php echo count($data); ?>, // number of samples per layer
    data = <?php echo json_encode($final2); ?>;

var w = document.body.clientWidth * 0.95,
    h = document.body.clientHeight * 0.7,
    x = pv.Scale.linear(0, m - 1).range(0, w),
    y = pv.Scale.linear(0, 2 * n).range(0, h * 0.9);

var vis = new pv.Panel()
    .width(w)
    .height(h);

vis.add(pv.Layout.Stack)
    .layers(data)
    .order("inside-out")
    .offset('<?php echo $_GET['offset']; ?>')
    .x(x.by(pv.index))
    .y(y)
  .layer.add(pv.Area)
    .strokeStyle(function() this.fillStyle().alpha(.3));

vis.render();

</script>
</div>
</div>
</body>

	  
