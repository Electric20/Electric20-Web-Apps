<?php

require_once('utility.php');
require_once('query.php');

if (verifyUser($_GET['u'], $_GET['p']) == true) {
	$bits = array(
		array(
			
"http://79.125.20.47/dataStore/request.php?u=".$_GET['u']."&p=".$_GET['p']."&action=currentNetData",
			print_r(
				request(array('action'=>currentNetData)),
				true
			),
			json_encode(
				request(array('action'=>currentNetData))
			)
		),
		array(
			
"http://79.125.20.47/dataStore/request.php?u=".$_GET['u']."&p=".$_GET['p']."&action=currentNetLoad",
			print_r(
				request(array('action'=>currentNetLoad)),
				true
			),
			json_encode(
				request(array('action'=>currentNetLoad))
			)
		)
	);
}

?>

<html>
<body>

<h1>Example requests</h1>

<h2>currentNetData</h2>
<h3>Request</h3>
<p><pre><?php echo $bits[0][0]; ?></pre></p>
<h3>Result</h3>
<p><pre><?php echo $bits[0][1]; ?></pre></p>
<h3>JSON response</h3>
<p><pre><?php echo $bits[0][2]; ?></pre></p>

<h2>currentNetLoad</h2>
<h3>Request</h3>
<p><pre><?php echo $bits[1][0]; ?></pre></p>
<h3>Result</h3>
<p><pre><?php echo $bits[1][1]; ?></pre></p>
<h3>JSON response</h3>
<p><pre><?php echo $bits[1][2]; ?></pre></p>

</body>
</html>
	  
