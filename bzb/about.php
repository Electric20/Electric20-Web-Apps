<?php

session_start();

require_once('../dataStore/query.php');

if ($_GET['concept'] == 'overview') {
	$bodyTitle = "The Big Picture";
	$bodyText = "<p>The 'Big Picture' page aims to give you a sense of how the behaviour of the Neighbourhood compares with that of the UK as a whole.</p>
<h2>Electricity and emissions</h2>
<p>Most UK residents are in an enviable position of having electricity available all day, every day at the flick of a switch. This availability means that most of us have
little cause to consider where our electricity comes from and how its consumption affects the country.</p>
<h3>A stressed Grid</h3>
Consider how many kettles, electric ovens and hobs, and TVs are switched on at the same time in the evening. In order to 
compensate for the peaks in electricity consumption at these times of day the country's power stations must produce a great deal of electricity. Unfortunately 
power stations 
(particularly Nuclear power stations) cannot 
rapidly increase or reduce power production, so they tend to maintain a higher than necessary level of production in anticipation of our peaks in consumption. 
Overproducing in this manner causes three problems:</p>
<ul>
<li>The physical infrastructure of the Grid degrades more rapidly, increasing maintenance costs which are passed on to consumers</li>
<li>Our non-sustainable fuels are depleted faster than necessary</li>
<li>Unnecessary amounts of greenhouse gases are produced as a result</li>
</ul>
<p>When we consider that supplies of non-renewable fuels are dwindling, and the costs of fuels are rising, these problems become serious.</p>
<h3>How does Electric20 measure demand?</h3>
<p>The standard frequency of <span style='font-style: italic;'>alternating current</span> electricity provided by the National Grid in the UK is 50Hz. In reality the frequency shifts up or down 
by small amounts as electricity is drawn by homes, industry and other consumers, so it is a useful indicator for judging how strained the National
Grid is. The <span style='font-weight: bold;'>UK's level of demand</span> in the 'Big Picture' diagram is drawn from the current frequency of the electricity on the Grid, supplied by <a 
href='http://www.caniturniton.com/' target='new' class='dark'>caniturniton.com</a></p>
<p>To measure the <span style='font-weight: bold;'>Neighbourhood's level of demand</span>, we compare <span style='font-style: italic;'>live data</span> from the neighbours' meters with consumption at the same time over the <span style='font-style: italic;'>last few 
days</span>. This tells us whether the Neighbourhood demand is higher or lower than usual for this time of the day. If the neighbours' demand is higher than 
usual, extra strain is being placed on the National Grid, exacerbating national issues, whereas a lower than usual demand helps to alleviate the issues 
for the rest of the country.</p>
<h2>Understanding the Neighbourhood</h2>
<p>To move on from this page you have the option of viewing your home's Twitter feed, or viewing more detailed information about the Neighbourhood as a 
whole. You can find links to the <span style='font-weight: bold;'>Twitter feed</span> and the <span style='font-weight: bold;'>Neighbourhood electricity data</span> in the panel at the top-right of the page.</p>
<p><a href='#' class='dark' onclick='self.close()'>Close this Help page</a></p>";
} else if ($_GET['concept'] == 'freq') {
	$bodyTitle = "Grid Frequency";
	$textBits = array();
	if ($_GET['rec'] == "no") {
		$textBits[0] = "lower than average";
		$textBits[1] = "higher than average";
		$textBits[2] = "If Horizon participants turn on more appliances now, it might cause problems for the Grid.";
	} else if ($_GET['rec'] == "yes") {
		$textBits[0] = "higher than average";
		$textBits[1] = "lower than average";
		$textBits[2] = "Now is a good time for Horizon participants to use electricity if they need to.";
	}
	$bodyText = "<p>The standard frequency of electricity provided by the National Grid in the UK is 50Hz. In reality the frequency shifts up or down by small amounts depending on the amount of electricity being drawn by 
homes, industry and other consumers, so it is a useful indicator for judging how strained the National 
Grid is.</p><p>Data from <a href='http://www.caniturniton.com/' target='new' class='greenLink'>caniturniton.com</a> 
tells us that the 
frequency is currently <span style='font-weight: bold;'>".$textBits[0]."</span>, meaning that there is <span style='font-weight: bold;'>".$textBits[1]."</span> 
demand 
for electricity across the UK. <span style='font-weight: bold;'>".$textBits[2]."</span></p>";
} else if ($_GET['concept'] ==	'co2') {
	$bodyTitle = "Grid Carbon";
	if ($_GET['rec'] == "no") {
		$textBits[0] = "more Carbon emissions than usual";
		$textBits[1] = "their Carbon footprint will be higher than usual";
	} else if ($_GET['rec'] == "yes") {
		$textBits[0] = "less Carbon emissions than usual";
		$textBits[1] = "their Carbon footprint will be lower than usual";
	}
        $bodyText = "<p>Electricity in the UK is generated in many different ways and from many different sources, ranging from fossil fuels (such as coal) to 
renewables (such as wind). All processes that generate electricity for the National Grid also produce Carbon emissions as a side-effect, but some processes 
generate much more than others. The balance of low and high-emission power will vary as new power sources are added to the grid (e.g. if the UK favours renewable energy sources rather than fossil fuels in the future), but also 
as the weather and 
time of day change (e.g. providing more or less wind and solar power).</p><p>Data 
from 
<a href='http://www.realtimecarbon.org' target='new' class='greenLink'>realtimecarbon.org</a> 
tells us that any electricity 
produced 
for the National Grid right now creates <span style='font-weight: bold;'>".$textBits[0]."</span>. If Horizon participants use 
electricity 
now <span style='font-weight: bold;'>".$textBits[1]."</span>.</p>";
} else if ($_GET['concept'] ==	'load') {
	$bodyTitle = "Horizon Load";
	if ($_GET['rec'] == "no") {
		$textBits[0] = "higher";
	} else {
		$textBits[0] = "lower";
	}
        $bodyText = "<p>This is the total electricity currently being pulled by the project participants from the National Grid. It is currently <span 
style='font-weight: bold;'>".$textBits[0]."</span> 
than usual for this time of day.</p>";
} else if ($_GET['concept'] == 'horizon') {
	$bodyTitle = "Horizon Energy Research";
	$bodyText = "<p>This website was produced at University of Nottingham as part of collaborative research between the Horizon Digital Economy Research Institute and 
the 
Mixed Reality Laboratory into 
technologies to raise public awareness of 
the 
implications of energy consumption. For more information, please visit <a href='http://www.horizon.ac.uk/' target='new' 
class='greenLink'>http://www.horizon.ac.uk/</a></p><h2>Android&trade; app</h2><p>Search for \"Horizon Electricity Monitor\" on the Android&trade; marketplace to find 
our mobile application.</p><p style='text-align: center;'><img src='android_market_logo.png' 
/><img src='mrl_logo.png' style='padding-left: 20px;' /><img src='horizon_logo.png' style='padding-left: 20px;' /><img src='ukrc_ep_logo.png' /></p>";
} else if ($_GET['concept'] == 'help') {
        $bodyTitle = "How do I use this website?";
        $bodyText = "<p>Instructions are on their way - please be patient, and don't be afraid to explore the website (you can't break anything!).</p>";
} else if ($_GET['concept'] == 'electric20') {
	$bodyTitle = "Electric20";
        $bodyText = "<p>This website was produced at University of Nottingham as part of collaborative research between the Horizon Digital Economy Research Institute and
the Mixed Reality Laboratory into technologies to raise public awareness of the implications of energy consumption. The current cohort of (approximately) 20 participants has agreed to live with Current Cost electricity monitoring kit, as well as a 
set of monitoring feedback tools developed by University of Nottingham, for 10 weeks to help the University researchers understand how the public might respond to different representations of domestic electricity consumption data.</p>
<h3>I know someone else who'd like to join the neighbourhood</h3>
<p>
We'd love to hear from people who would like to join our energy monitoring research. There are a few practical requirements, and we have a limited number of places in our 
trials, so we advise that you (or the people you'd like to introduce)
contact us by <a href='mailto:info@electric20.com' class='green'>email</a> to find out about any current
opportunities to get
involved.
</p>
<h3>Android&trade; app</h3>
<p>
Search for \"Horizon Electricity Monitor\" on the Android&trade; marketplace to find our (currently very basic!) mobile application.
</p>
<p style='text-align: center;'>
	<img src='android_market_logo.png' />
	<img src='mrl_logo.png' style='padding-left: 20px;' />
	<img src='horizon_logo.png' style='padding-left: 20px;' />
	<img src='ukrc_ep_logo.png' style='padding-left: 20px;' />
	<img src='de_logo.png' />
</p>";

} else if ($_GET['concept'] == 'status') {
        include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
        mysql_select_db($database);
        $results = mysql_query("SELECT hubId FROM tblUserInfo WHERE userId=".$_SESSION['loggedIn']);
        while ($row = mysql_fetch_assoc($results)) {
                $hubId = $row['hubId'];
        }
        mysql_free_result($results);
        unset($results);
       	mysql_close();
	$data = currentHubStatus($hubId);
	$bodyTitle = "Is ".$data['description']." connected to the Neighbourhood?";
	if ($data['code'] == 0) {
		$bodyText = "<h3>Your home doesn't appear to be connected!</h3>
<p>While your home is disconnected you will not be able to view any live data about your own home. There will also be a gap in your historic data, which means that estimates for cost, 
emissions and so on may also be somewhat inaccurate (depending upon the size of the gaps).</p>
<p>Work through the steps below which should help you identify and fix the problem. After each step, wait 5 minutes then <a href='graph.php' class='dark' target='new'>check the Neighbourhood</a> to see if your home has become connected.</p>
<ol>
<li id='dd0'></li>
<li id='dd1'></li>
<li id='dd2'></li>
<li id='dd3'></li>
<li id='dd4'></li>
<li id='dd5'></li>
</ol>
</p>
<p style='font-weight: bold;'>Please note: your Current Cost monitor and 'Plug' must be switched on and connected to each other and your broadband modem/router for any data to be sent to Electric20. If possible, please keep the kit switched on at all times: we 
have deliberately chosen low power equipment that is designed to be safely left switched on.</p>";
	} else if ($data['code'] == 1)	{
		$bodyText = "<h3>Your home appears to be connected, but is sending data intermittently</h3><p>We usually expect to receive a reading from your electricity meter several times each minute (usually every 5-10 seconds): we have not heard anything 
from your home for more than 5 minutes. While this is longer than expected, we have received readings within the last hour, so this does not indicate a severe problem but could mean several things:
<ul>
<li>You have just unplugged the Current Cost monitor, clamp or 'Sheeva Plug'/'Dream Plug' (the small computer connected to the Current Cost monitor); these devices need to be switched on in order for electricity readings to be sent to Electric20. Of course you may 
switch them off if you need to move them, but leaving them switched off for too long will create larger gaps in your electricity data.</li>
<li>The wireless connection between your small Current Cost monitor and the Current Cost clamp attached to your electricity meter is weak: in this case electricity readings might be infrequent. You could try moving your Current Cost monitor closer to the meter 
if possible.</li>
<li>Your broadband connection is intermittent (or your bandwidth is being used up by other activities). Although the Electric20 kit uses very little of your Internet bandwidth to send data, if your connection is poor (sometimes this may happen in the evenings or at 
weekends) or you are using a lot of the bandwidth for something else (e.g. watching movies or TV online), then the kit might not be able to send any data.</li>
</ul>
</p>
<p>You do not necessarily need to take any action right now, but keep a watch to make sure that the problem does not get any worse. If it does, your status will change and the website will provide you with a set of steps to walk through to fix the problem.</p>";
        } else if ($data['code'] == 2)	{
		$bodyText = "<h3>Your home is connected!</h3><p>All is well with your home's connection to the Neighbourhood. The Electric20 datastore is receiving data from your electricity meter and you should be able to see <a 
href='live.php?hubId=".$hubId."&sensorId=undefined' class='dark'>live data 
from your home on the 
website</a>. Enjoy!</p>";
        }      
}

?>

<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7" >
	<link href="style.css" rel="stylesheet" type="text/css" />
	<title><?php echo $bodyTitle; ?></title>
	<script type="text/javascript" src="jquery.min.js"></script>
        <script type="text/javascript">

function recentre () {
        $('#container').css(
                "top", ($(document).height() - $('#container').height()) / 2 - 20);
        $('#container').css(
                "left", ($(document).width() - $('#container').width()) / 2 - 20);
}

var dropDown = [false, false, false, false, false, false];
var dropDownHTML = [
                [
                        "<p><a onclick='dropDownSwitch(0)' class='dark'>Show step 1</a></p>",
                        "<p>Check the small 'Current Cost' display that was installed in your home: if this appears to be showing live data, <a onclick='dropDownSwitch(1)' class='dark'>skip this step</a>. "+
			"If the display does not appear to be showing data (the display may simply show '--' rather than a figure) then the display has lost its wireless connection to your electricity meter. "+
			"First go to your meter and check that the red 'Current Cost' clamp is still attached to the meter cable. "+
			"If it has become detached, either reattach it, or if you are unsure about which cable to attach it to please <a class='dark' href='mailto:info@electric20.com'>contact the Electric20 team</a>. "+
			"If the clamp is still attached, it is likely that your 'Current Cost' display is too far away from the clamp for the wireless signal to reach: "+
			"if it is possible, move the 'Current Cost' display closer to the clamp and wait a minute or two to see if the monitor starts showing live data.</p>"+
			"<p>If this advice has not helped reconnect your home to the Neighbourhood, <a onclick='dropDownSwitch(1)' class='dark'>move on to step 2</a></p>"
                ],
                [
			"<p><a onclick='dropDownSwitch(1)' class='dark'>Show step 2</a></p>",
                        "<p>If the 'Current Cost' display appears to be showing live data, the problem lies with your small white 'Sheeva Plug' or small black 'Dream Plug' (the small computer installed along with your Current Cost monitor). "+
			"First, confirm that the 'Plug' is switched on: both 'Sheeva Plugs' and 'Dream Plugs' should have at least a couple of lights (blue or green) that should be lit if the 'Plug' is powered up. "+
			"If the 'Plug' appears to be switched on, <a onclick='dropDownSwitch(2)' class='dark'>skip this step</a>. "+
			"If the 'Plug' is not switched on, please check that it is plugged in and that the socket is switched on. "+
			"Sometimes the 'Plug' may simply become loose in the socket and need to be pushed back in. "+
			"If you cannot switch the 'Plug' on, please <a href='mailto:info@electric20.com' class='dark'>contact the Electric20 team</a>.</p>"+
			"<p>If this advice has not helped reconnect your home to the Neighbourhood, <a onclick='dropDownSwitch(2)' class='dark'>move on to step 3</a></p>"
                ],
                [
                        "<p><a onclick='dropDownSwitch(2)' class='dark'>Show step 3</a></p>",
                        "<p>If your 'Plug' is switched on there are a couple more things to check. "+
			"For 'Sheeva Plugs' (small white) only, please check that the memory card is firmly plugged in to the slot in the side of the 'Plug': "+
			"the memory card should easily slide into the slot until it is about half way inside the 'Plug' (do not attempt to force it all the way in). "+
			"Once you have confirmed that the memory card is securely slotted into the 'Plug', <a onclick='dropDownSwitch(3)' class='dark'>continue to step 4</a>.</p>"
                ],
                [
                        "<p><a onclick='dropDownSwitch(3)' class='dark'>Show step 4</a></p>",
                        "<p>Sometimes a problem can be fixed by simply restarting your 'Plug'. "+
			"Restart your 'Plug' by unplugging and replugging the 'Plug's power cable.</p>"+
			"<p>If this advice has not helped reconnect your home to the Neighbourhood, <a onclick='dropDownSwitch(4)' class='dark'>move on to step 5</a></p>"
                ],
		[
                        "<p><a onclick='dropDownSwitch(4)' class='dark'>Show step 5</a></p>",
                        "<p>Finally, your 'Plug' may not be able to connect to the Internet through your broadband connection. "+
			"You may be able to fix this problem by restarting your broadband modem/router. "+
			"Your modem/router will sometimes take several minutes to reconnect your home to the Internet, so wait 10 minutes before checking to see whether your home has reconnected to the Neighbourhood.</p>"+
			"<p>If your home does not appear to be reconnected, <a onclick='dropDownSwitch(5)' class='dark'>move on to step 6</a></p>"
                ],
                [
                        "<p><a onclick='dropDownSwitch(5)' class='dark'>Show step 6</a></p>",
                        "<p>Try once more to restart your 'Plug' by unplugging and replugging the 'Plug's power cable.</p>"+
			"<p>If these steps fail to get your home reconnected, please <a class='dark' href='mailto:info@electric20.com'>contact the Electric20 team</a> and we will arrange a time for one of the team to inspect your kit and fix the problem.</p>"
                ]
        ];

function dropDownSwitch (index) {
	for (var i = 0; i < dropDown.length; i++) {
	        if (i != index) {
        	        dropDown[index] = false;
			$('#dd'+i).html(dropDownHTML[i][0]);
	        } else {
        	        dropDown[index] = true;
			$('#dd'+i).html(dropDownHTML[i][1]);
        	}
	}
	recentre();
}

        </script>
</head>
<body onload="recentre(); dropDownSwitch(-1)">
<div class="container" style="width: 90%; position: absolute;" id="container">
<h1><?php echo $bodyTitle; ?></h1>
<div class="innercontainer" style="padding: 10px; color: black;">
<?php echo $bodyText; ?>
</div>
</div>
</body>
</html>
