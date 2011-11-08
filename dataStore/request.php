<?php

require_once('utility.php');
require_once('query.php');

if(verifyUser($_GET['u'], $_GET['p']) == true) {
	echo trim(json_encode(request($_GET)));
}

?>
	  
