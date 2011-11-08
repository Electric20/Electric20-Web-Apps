<?php
error_reporting(0);
session_start();

require_once('../dataStore/query.php');

$data = request(array('action'=>prev2));

$response = array();
$response['count'] = $data['data']['count'];

$response['valueStyle'] = "green";
if ($data['data']['now'] > max($data['data']['mean'] * 1.2, $data['data']['mean'] + 250)) {
        $response['valueStyle'] = "red";
} else if ($data['data']['now'] > min($data['data']['mean'] * 0.8, $data['data']['mean'] - 250)) {
        $response['valueStyle'] = "orange";
}

if ($data['data']['now'] < 2000) {
        $response['comparison'] = "turning on ".ceil($data['data']['now'] / 60)." lightbulbs";
} else if ($data['data']['now'] < 18000) {
        $response['comparison'] = "boiling ".ceil($data['data']['now'] / 1500)." kettles";
} else {
        $response['comparison'] = "running ".ceil($data['data']['now'] / 8000)." electric showers";
}

$gridData = request(array('action'=>'gridData'));

$response['freqStyle'] = "green";
if (!$gridData['data']['freqRec']) {
        $response['freqStyle'] = "red";
}

        include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
        mysql_select_db($database);
        $results = mysql_query("SELECT twitterUser FROM tblUserInfo WHERE userId = ".$_SESSION['loggedIn']);

        while ($row = mysql_fetch_assoc($results)) {
		$response['twitterUser'] = $row['twitterUser'];
        }

        mysql_free_result($results);
        unset($results);
        mysql_close();

echo json_encode($response);

unset($data);
unset($response);

?>
