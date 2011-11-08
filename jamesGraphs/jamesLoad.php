<?php
	include '/var/www/dataStore/dataAccess/db_connect.php';
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
    mysql_select_db($database);
	$results = mysql_query("select UNIX_TIMESTAMP(timeStamp)*1000 as 
time, tblLoad.load from tblLoad where hubId=7 and sensorId=1260 and timeStamp >(CURRENT_TIMESTAMP - interval 60 minute) order by timeStamp desc");
	$dataset1 = array();
	while ($row = mysql_fetch_assoc($results)) 
	{
			$dataset1[] = array($row['time'],$row['load']);	
    }	
?>

<html>
<head>
    <script language="javascript" type="text/javascript" src="../flot/jquery.js"></script> 
	<script language="javascript" type="text/javascript" src="../flot/jquery.flot.js"></script>
	<script language="javascript" type="text/javascript" src="../flot/jquery.flot.selection.js"></script> 
	<script id="source" language="javascript" type="text/javascript"></script>
		
</head>
<body>
<p>
Load Graph for last 24h
<br>
</p>
<div id="placeholder" style="width:900px;height:600px;"></div>
<input class="dataUpdate" type="button" value="Turn realtime on"> 
<script id="source" language="javascript" type="text/javascript"> 

$(function () 
{
    var data =	
				[
				{	
					label: "Load in watts vs Time",
			 		data: <?php echo preg_replace('/"(-?\d+\.?\d*)"/', '$1', json_encode($dataset1)) ?>
				}	
				];	
			var optionsNorm = 
			{
			        series: 
					{
						lines: 
						{
							show: true
						},
						points: 
						{
							show: true
						}
			        },
			        legend:
					{
						noColumns: 2
					},
			        grid: 
					{
						hoverable: true,
					},
					xaxis: 
					{ 
						tickDecimals: 0,
						mode: "time" 
					},
			        selection: 
					{ 
						mode: "x" 
					}
			};
			var optionsZoom =
			{
				        series: 
						{
							lines: 
							{
								show: true
							},
							points: 
							{
								show: true
							}
				        },
				        legend:
						{
							noColumns: 2
						},
				        grid: 
						{
							hoverable: true,
						},
						xaxis: 
						{ 
							tickDecimals: 0,
							mode: "time",
						},
				        selection: 
						{ 
							mode: "x" 
						}
				    };
			var placeholder = $("#placeholder");
		    var plot = $.plot(placeholder, data, optionsNorm);
			$("input.dataUpdate").click(function () {
			        function fetchData() {
			            function onDataReceived(series) {
			                data = [ series ];
			                $.plot($("#placeholder"), data, optionsNorm);
			            }
			            $.ajax({
			                url: "ajaxJames.php",
			                method: 'GET',
			                dataType: 'json',
			                success: onDataReceived
			            });    
			        }
			        setInterval(fetchData, 3000);
			    });   
	});
</script>
</body>
