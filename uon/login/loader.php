<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
#Set the environment required by uncommenting appropriate line
$basePath="";
$authLocation="";
#$config["dev"]=true; 

#auto config if IPAddress available and 127.0.0.1




$config["live"]=true; 


/*
Loader functionality - no editing required below here.
*/

$basePath = '/usr/local/web/p5auth/';	
$authLocation = 'https://www.nottingham.ac.uk';	
	
# corect environment if running in dev

	require_once($basePath."loader.php");
}
?>