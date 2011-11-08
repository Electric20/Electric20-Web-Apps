 <?php

require_once('../dataStore/utility.php');
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
function request ($params) { 

	$response = array();
	if (isset($params['action'])) {
		if ($params['action'] == 'currentNetData') {
			$response['action'] = $params['action'];
			$response['data'] = currentNetData();
		} else if ($params['action'] == 'currentNetLoad') {
			$response['action'] = $params['action'];
			$response['data'] = currentNetLoad();
		} else if ($params['action'] == 'currentHubData') {
			$response['action'] = $params['action'];
			if (isset($params['hubId'])) {
				$response['hubId'] = $params['hubId'];
				$response['data'] = currentHubData($params['hubId']);
			} else {
				$response['hubId'] = 'undefined';				
			}
		} else if ($params['action'] == 'currentHubLoad') {
			$response['action'] = $params['action'];
			if (isset($params['hubId'])) {
				$response['hubId'] = $params['hubId'];
				$response['data'] = currentHubLoad($params['hubId']);
			} else {
				$response['hubId'] = 'undefined';				
			}
		} else if ($params['action'] == 'currentHubStatus') {
                        $response['action'] = $params['action'];
                        if (isset($params['hubId'])) {
                                $response['hubId'] = $params['hubId'];
                                $response['data'] = currentHubStatus($params['hubId']);
                        } else {
                                $response['hubId'] = 'undefined';
                        }
                } else if ($params['action'] == 'getDescription') {
                        $response['action'] = $params['action'];
                        if (isset($params['hubId'])) {
                                $response['hubId'] = $params['hubId'];
				if (isset($params['sensorId'])) {
                                	$response['sensorId'] = $params['sensorId'];
                                	$response['data'] = getSensorDescription($params['hubId'], $params['sensorId']);
                        	} else {
                                	$response['sensorId'] = 'undefined';
					$response['data'] = getHubDescription($params['hubId']);
                        	}
                        } else {
                                $response['hubId'] = 'undefined';
                        }
                } else if ($params['action'] == 'prev2') {
			$response['action'] = $params['action'];
			$response['data'] = prev2DayNetData();
		} else if ($params['action'] == 'currentNetDirectoryAuth') {
			$response['action'] = $params['action'];
			$response['data'] = currentNetDirectoryAuth();
		} else if ($params['action'] == 'gridData') {
			$response['action'] = $params['action'];
			$response['data'] = gridData();
		} else if ($params['action'] == 'getTotalConsumptionToday') {
                        $response['action'] = $params['action'];
                        $response['data'] = getTotalConsumptionToday();
                } else if ($params['action'] == 'getTotalConsumptionLast2Days') {
                        $response['action'] = $params['action'];
                        $response['data'] = getTotalConsumptionLast2Days();
                } else if ($params['action'] == 'getTotalConsumptionLast2Weeks') {
                        $response['action'] = $params['action'];
                        $response['data'] = getTotalConsumptionLast2Weeks();
                } else if ($params['action'] == 'hubHistory') {
                        $response['action'] = $params['action'];
                        if (isset($params['hubId'])) {
                                $response['hubId'] = $params['hubId'];
				if (isset($params['sensorId'])) {
	                                $response['sensorId'] = $params['sensorId'];
					$response['data'] = hubHistory($params['hubId'], $params['sensorId']);
				} else {
					$response['sensorId'] = 'undefined';
                                	$response['data'] = hubHistory($params['hubId'], null);
				}
                        } else {
                                $response['hubId'] = 'undefined';
				$response['sensorId'] = 'undefined';
				$response['data'] = hubHistory(null, null);
                        }
                } else if ($params['action'] == 'log') {
			if (isset ($params['user']) && isset($params['url']) && isset($params['loggedAct']) && isset($params['agent'])) {
				logAction($params['user'], $params['url'], $params['loggedAct'], $params['agent']);
			}
		} else {
			$response['action'] = 'bad';
		}
	} else {
		$response['action'] = 'undefined'; 
	}

	return $response;

}

function currentHubStatus ($hubId) {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
        mysql_select_db($database);
        $results = mysql_query("SELECT * FROM (SELECT status.hubId, status.sensorId, status.sDescription, UNIX_TIMESTAMP(status.timeStamp) AS time, tblHubDir.description FROM (SELECT tDir.hubId, tDir.sensorId, tDir.description AS
sDescription, tLoad.timeStamp FROM (SELECT * FROM tblSensorDir WHERE hubId = ".$hubId.") AS tDir LEFT JOIN (SELECT * FROM tblLoad ORDER BY timeStamp DESC) AS tLoad
ON tDir.hubId = tLoad.hubId
AND tDir.sensorId = tLoad.sensorId GROUP BY tDir.hubId, tDir.sensorId) AS status JOIN tblHubDir ON status.hubId = tblHubDir.hubId ORDER BY status.timeStamp ASC) AS time LEFT JOIN (SELECT hubId, sensorId, COUNT(tblLoad.load) AS amount FROM tblLoad WHERE 
hubId=".$hubId." GROUP 
BY 
sensorId) AS samples ON time.hubId=samples.hubId AND time.sensorId=samples.sensorId");
	$status = array(); 
	$status['code'] = -1;
	while ($row = mysql_fetch_assoc($results)) {
		$status['description'] = $row['description'];
/*		if (isset($row['amount'])) {
			$d = date("i");
			if ($d > 17) {
				$status['delay'] = ((($d - 17) * 60) / $row['amount']); 
			} else {
				$status['delay'] = ((($d + 43) * 60) / $row['amount']);
			}
		}*/
		if (isset($row['time'])) {
			if ((time() - $row['time']) < 300) {
				$status['code'] = 2;
			} else {
				$status['code'] = 1;
			}		
		} else {
			$status['code'] = 0;
		}
	}
	mysql_free_result($results);
        unset($results);
        mysql_close();
	return $status;
}

function getSensorDescription ($hubId, $sensorId) {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
        mysql_select_db($database);
        $results = mysql_query("SELECT tblSensorDir.description AS sDescription, tblHubDir.description AS hDescription FROM tblSensorDir JOIN tblHubDir ON tblSensorDir.hubId = tblHubDir.hubId WHERE tblSensorDir.hubId = ".$hubId." AND tblSensorDir.sensorId = 
".$sensorId);

        while ($row = mysql_fetch_assoc($results)) {
                $data = array('hDescription'=>$row['hDescription'], 'sDescription'=>$row['sDescription']);
        }

        mysql_free_result($results);
        mysql_close();

	return $data;
}

function getHubDescription ($hubId) {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
        mysql_select_db($database);
        $results = mysql_query("SELECT description FROM tblHubDir WHERE tblHubDir.hubId = ".$hubId);

        while ($row = mysql_fetch_assoc($results)) {
                $data = array('hDescription'=>$row['description']);
        }

        mysql_free_result($results);
        mysql_close();

        return $data;
}

function getTotalConsumptionLast2Weeks() {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $consumption = array();

        $rawC = getConsumptionLast7Days();

        if ($rawC == null) {
                return null;
        }

        $average = 0;

        foreach ($rawC as $hubId => $category) {
                if ($hubId != 26) {
                        $categoryTotal = array();
                        foreach ($rawC[$hubId] as $sensorC) {
                                if (isset($categoryTotal[$sensorC['category']])) {
                                        $categoryTotal[$sensorC['category']] += $sensorC['total'];
                                } else {
                                        $categoryTotal[$sensorC['category']] = $sensorC['total'];
                                }
                                if (!isset($consumption['last7'][$hubId])) {
                                        $consumption['last7'][$hubId] = array('hubId'=>$hubId, 'description'=>$sensorC['hubDescription']);
                                }
                        }
                        if ($categoryTotal[1] > 0) {
                                $consumption['last7'][$hubId]['total'] = $categoryTotal[1];
                        } else if ($categoryTotal[2] > 0) {
                                $consumption['last7'][$hubId]['total'] = $categoryTotal[2];
                        } else if ($categoryTotal[3] > 0) {
                                $consumption['last7'][$hubId]['total'] = $categoryTotal[3];
                        }
                        $average += $consumption['last7'][$hubId]['total'];
                }
        }

        usort($consumption['last7'], function ($a, $b) {
		return ($a['total'] < $b['total']);
        });
                                
        $average /= count($consumption['last7']);
        $consumption['last7']['average'] = $average;
                                        
        $consumption['last7']['count'] = count($consumption['last7']) - 1;

        $rawC = getConsumptionPenultimate7Days();

        if ($rawC == null) {
                return null;
        }

        $average = 0;

        foreach ($rawC as $hubId => $category) {
                if ($hubId != 26) {
                        $categoryTotal = array();
                        foreach ($rawC[$hubId] as $sensorC) {
                                if (isset($categoryTotal[$sensorC['category']])) {
                                        $categoryTotal[$sensorC['category']] += $sensorC['total'];
                                } else {
                                        $categoryTotal[$sensorC['category']] = $sensorC['total'];

                                }
                                if (!isset($consumption['prev7'][$hubId])) {
                                        $consumption['prev7'][$hubId] = array('hubId'=>$hubId, 'description'=>$sensorC['hubDescription']);
                                }
                        }
                        if ($categoryTotal[1] > 0) {
                                $consumption['prev7'][$hubId]['total'] = $categoryTotal[1];
			} else if ($categoryTotal[2] > 0) {
                                $consumption['prev7'][$hubId]['total'] = $categoryTotal[2];
                        } else if ($categoryTotal[3] > 0) {
                                $consumption['prev7'][$hubId]['total'] = $categoryTotal[3];
                        }
                        $average += $consumption['prev7'][$hubId]['total'];
                }
        }

        usort($consumption['prev7'], function ($a, $b) {
                return ($a['total'] < $b['total']);
        });

	$average /= count($consumption['prev7']);
        $consumption['prev7']['average'] = $average;

        $consumption['prev7']['count'] = count($consumption['prev7']) - 1;

        return $consumption;

}


function getTotalConsumptionLast2Days() {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';	
	$consumption = array();

	$rawC = getConsumptionToday();

        if ($rawC == null) {
                return null;
        }

        $average = 0;

        foreach ($rawC as $hubId => $category) {
                if ($hubId != 26) {
                        $categoryTotal = array();
                        foreach ($rawC[$hubId] as $sensorC) {
				if (isset($categoryTotal[$sensorC['category']])) {
	                                $categoryTotal[$sensorC['category']] += $sensorC['total'];
        			} else {
					$categoryTotal[$sensorC['category']] = $sensorC['total'];
				}
	                        if (!isset($consumption['today'][$hubId])) {
                                        $consumption['today'][$hubId] = array('hubId'=>$hubId, 'description'=>$sensorC['hubDescription']);
                                }
                        }
                        if ($categoryTotal[1] > 0) {
                                $consumption['today'][$hubId]['total'] = $categoryTotal[1];
                        } else if ($categoryTotal[2] > 0) {
                                $consumption['today'][$hubId]['total'] = $categoryTotal[2];
                        } else if ($categoryTotal[3] > 0) {
                                $consumption['today'][$hubId]['total'] = $categoryTotal[3];
                        }
                        $average += $consumption['today'][$hubId]['total'];
                }
        }

        usort($consumption['today'], function ($a, $b) {
		return ($a['total'] < $b['total']);
 	});

        $average /= count($consumption['today']);
        $consumption['today']['average'] = $average;
                         
        $consumption['today']['count'] = count($consumption['today']) - 1;
               
	$rawC = getConsumptionYesterday();

        if ($rawC == null) {
                return null;
        }

        $average = 0;

        foreach ($rawC as $hubId => $category) {
                if ($hubId != 26) {
                        $categoryTotal = array();
                        foreach ($rawC[$hubId] as $sensorC) {
				if (isset($categoryTotal[$sensorC['category']])) {
	                                $categoryTotal[$sensorC['category']] += $sensorC['total'];
        			} else {
					$categoryTotal[$sensorC['category']] = $sensorC['total'];

				}
	                        if (!isset($consumption['yesterday'][$hubId])) {
                                        $consumption['yesterday'][$hubId] = array('hubId'=>$hubId, 'description'=>$sensorC['hubDescription']);
                                }
                        }
                        if ($categoryTotal[1] > 0) {
                                $consumption['yesterday'][$hubId]['total'] = $categoryTotal[1];
                        } else if ($categoryTotal[2] > 0) {
                                $consumption['yesterday'][$hubId]['total'] = $categoryTotal[2];
                        } else if ($categoryTotal[3] > 0) {
                                $consumption['yesterday'][$hubId]['total'] = $categoryTotal[3];
                        }
                        $average += $consumption['yesterday'][$hubId]['total'];
                }
        }

        usort($consumption['yesterday'], function ($a, $b) {
                return ($a['total'] < $b['total']);
        });

	$average /= count($consumption['yesterday']);
        $consumption['yesterday']['average'] = $average;

        $consumption['yesterday']['count'] = count($consumption['yesterday']) - 1;

	$rawC = getConsumptionYesterYesterday();

        if ($rawC == null) {
                return null;
        }

        $average = 0;

        foreach ($rawC as $hubId => $category) {
                if ($hubId != 26) {
                        $categoryTotal = array();
                        foreach ($rawC[$hubId] as $sensorC) {
                                if (isset($categoryTotal[$sensorC['category']])) {
                                        $categoryTotal[$sensorC['category']] += $sensorC['total'];
                                } else {
                                        $categoryTotal[$sensorC['category']] = $sensorC['total'];
                                }
                                if (!isset($consumption['yesteryesterday'][$hubId])) {
                                        $consumption['yesteryesterday'][$hubId] = array('hubId'=>$hubId, 'description'=>$sensorC['hubDescription']);
                                }
                        }
                        if ($categoryTotal[1] > 0) {
                                $consumption['yesteryesterday'][$hubId]['total'] = $categoryTotal[1];
                        } else if ($categoryTotal[2] > 0) {
                                $consumption['yesteryesterday'][$hubId]['total'] = $categoryTotal[2];
                        } else if ($categoryTotal[3] > 0) {
                                $consumption['yesteryesterday'][$hubId]['total'] = $categoryTotal[3];
                        }
                        $average += $consumption['yesteryesterday'][$hubId]['total'];
                }
        }
	
	usort($consumption['yesteryesterday'], function ($a, $b) {
                return ($a['total'] < $b['total']);
        });
                                        
        $average /= count($consumption['yesteryesterday']);
        $consumption['yesteryesterday']['average'] = $average;

        $consumption['yesteryesterday']['count'] = count($consumption['yesteryesterday']) - 1;

        return $consumption;

}

function getTotalConsumptionToday () {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';	
	$rawC = getConsumptionToday();

	if ($rawC == null) {
		return null;
	}

	$average = 0;

	foreach ($rawC as $hubId => $category) {
		if ($hubId != 26) {
	                $categoryTotal = array();
			$accuracy = array();
			if (isset($rawC[$hubId])) {
        	        	foreach ($rawC[$hubId] as $sensorC) {
					if (isset($categoryTotal[$sensorC['category']])) {
	                	        	$categoryTotal[$sensorC['category']] += $sensorC['total'];
					} else {
						$categoryTotal[$sensorC['category']] = $sensorC['total'];
					}
					if (!isset($consumption[$hubId])) {
						$consumption[$hubId] = array('hubId'=>$hubId, 'description'=>$sensorC['hubDescription']);
	        			}
					if (isset($accuracy[$sensorC['category']]) && $sensorC['accuracy'] > $accuracy[$sensorC['category']]
						|| !isset($accuracy[$sensorC['category']])) {
						$accuracy[$sensorC['category']] = $sensorC['accuracy'];
					}
		        	}
			}
                	if (isset($categoryTotal[1]) && $categoryTotal[1] > 0) {
                        	$consumption[$hubId]['total'] = $categoryTotal[1];
				$consumption[$hubId]['accuracy'] = $accuracy[1];
	                } else if (isset($categoryTotal[2]) && $categoryTotal[2] > 0) {
        	                $consumption[$hubId]['total'] = $categoryTotal[2];
				$consumption[$hubId]['accuracy'] = $accuracy[2];
                	} else if (isset($categoryTotal[3]) && $categoryTotal[3] > 0) {
                        	$consumption[$hubId]['total'] = $categoryTotal[3];
				$consumption[$hubId]['accuracy'] = $accuracy[3];
	                } 
			$average += $consumption[$hubId]['total'];
		}
        }

	usort($consumption, function ($a, $b) {
                return ($a['total'] < $b['total']);
  	});

	$average /= count($consumption);
	$consumption['average'] = $average;

	$consumption['count'] = count($consumption) - 1;

	return $consumption;
}

function getConsumptionPenultimate7Days () {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
        mysql_select_db($database);
        $results = mysql_query("SELECT description, hubId, sensorId, category, hubDesc, acc, kWh FROM (SELECT *, COUNT(loadAvg) / (7 * 24 * 12) AS acc, 7 * 24 * 
AVG(loadAvg)/1000 AS kWh FROM
(SELECT tblSensorDir.description, tblSensorDir.hubId, tblSensorDir.sensorId, tblSensorDir.category, tblLoadAgg.loadAvg, tblHubDir.description AS hubDesc
FROM tblLoadAgg, tblSensorDir, tblHubDir
WHERE pilot = 1
AND timeStamp <= DATE_SUB(CURRENT_DATE(), INTERVAL 8 DAY)
AND timeStamp > DATE_SUB(CURRENT_DATE(), INTERVAL 15 DAY)
AND tblLoadAgg.hubId = tblSensorDir.hubId
AND tblLoadAgg.sensorId = tblSensorDir.sensorId AND tblSensorDir.hubId = tblHubDir.hubId ORDER BY timeStamp DESC) AS t0 GROUP BY t0.hubId, t0.sensorId) AS t1 ORDER BY hubId,
category, sensorId");

        while ($row = mysql_fetch_assoc($results)) {
                $data[$row['hubId']][$row['sensorId']] = array('description'=>$row['description'], 'hubDescription'=>$row['hubDesc'], 'category'=>$row['category'], 
'accuracy'=>$row['acc'], 'total'=>$row['kWh']);
        }

        mysql_free_result($results);
        unset($results);
        mysql_close();

        return $data;
}

function getConsumptionLast7Days () {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
        mysql_select_db($database);
        $results = mysql_query("SELECT description, hubId, sensorId, category, hubDesc, acc, kWh FROM (SELECT *, COUNT(loadAvg) / (7 * 24 * 12) AS acc, 7 * 24 * 
AVG(loadAvg)/1000 AS kWh FROM
(SELECT tblSensorDir.description, tblSensorDir.hubId, tblSensorDir.sensorId, tblSensorDir.category, tblLoadAgg.loadAvg, tblHubDir.description AS hubDesc
FROM tblLoadAgg, tblSensorDir, tblHubDir
WHERE pilot = 1
AND timeStamp <= DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY)
AND timeStamp > DATE_SUB(CURRENT_DATE(), INTERVAL 8 DAY)
AND tblLoadAgg.hubId = tblSensorDir.hubId
AND tblLoadAgg.sensorId = tblSensorDir.sensorId AND tblSensorDir.hubId = tblHubDir.hubId ORDER BY timeStamp DESC) AS t0 GROUP BY t0.hubId, t0.sensorId) AS t1 ORDER BY hubId,
category, sensorId");

        while ($row = mysql_fetch_assoc($results)) {
                $data[$row['hubId']][$row['sensorId']] = array('description'=>$row['description'], 'hubDescription'=>$row['hubDesc'], 'category'=>$row['category'], 
'accuracy'=>$row['acc'], 'total'=>$row['kWh']);
        }

        mysql_free_result($results);
        unset($results);
        mysql_close();

        return $data;
}

function getConsumptionYesterYesterday () {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
        mysql_select_db($database);
        $results = mysql_query("SELECT description, hubId, sensorId, category, hubDesc, acc, kWh FROM (SELECT *, COUNT(loadAvg) / (24 * 12) AS acc, 24 * 
AVG(loadAvg)/1000 AS kWh FROM
(SELECT tblSensorDir.description, tblSensorDir.hubId, tblSensorDir.sensorId, tblSensorDir.category, tblLoadAgg.loadAvg, tblHubDir.description AS hubDesc
FROM tblLoadAgg, tblSensorDir, tblHubDir
WHERE pilot = 1
AND timeStamp >= DATE_SUB(DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY), INTERVAL 1 DAY)
AND timeStamp < DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY)
AND tblLoadAgg.hubId = tblSensorDir.hubId
AND tblLoadAgg.sensorId = tblSensorDir.sensorId AND tblSensorDir.hubId = tblHubDir.hubId ORDER BY timeStamp DESC) AS t0 GROUP BY t0.hubId, t0.sensorId) AS t1 ORDER BY hubId,
category, sensorId");

        while ($row = mysql_fetch_assoc($results)) {
                $data[$row['hubId']][$row['sensorId']] = array('description'=>$row['description'], 'hubDescription'=>$row['hubDesc'], 'category'=>$row['category'], 
'accuracy'=>$row['acc'], 'total'=>$row['kWh']);
        }

        mysql_free_result($results);
        unset($results);
        mysql_close();

        return $data;
}


function getConsumptionYesterday () {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
        mysql_select_db($database);
        $results = mysql_query("SELECT description, hubId, sensorId, category, hubDesc, acc, kWh FROM (SELECT *, COUNT(loadAvg) / (24 * 12) AS acc, 24 * AVG(loadAvg)/1000 
AS kWh FROM
(SELECT tblSensorDir.description, tblSensorDir.hubId, tblSensorDir.sensorId, tblSensorDir.category, tblLoadAgg.loadAvg, tblHubDir.description AS hubDesc
FROM tblLoadAgg, tblSensorDir, tblHubDir
WHERE pilot = 1
AND timeStamp >= DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY)
AND timeStamp < CURRENT_DATE()
AND tblLoadAgg.hubId = tblSensorDir.hubId
AND tblLoadAgg.sensorId = tblSensorDir.sensorId AND tblSensorDir.hubId = tblHubDir.hubId ORDER BY timeStamp DESC) AS t0 GROUP BY t0.hubId, t0.sensorId) AS t1 ORDER BY hubId,
category, sensorId");
        
        while ($row = mysql_fetch_assoc($results)) {
                $data[$row['hubId']][$row['sensorId']] = array('description'=>$row['description'], 'hubDescription'=>$row['hubDesc'], 'category'=>$row['category'], 
'accuracy'=>$row['acc'], 'total'=>$row['kWh']);
        }

        mysql_free_result($results);
        unset($results);
        mysql_close();

        return $data;
}

function getConsumptionYesterdayGap ($gap) {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
        mysql_select_db($database);
        $results = mysql_query("SELECT description, hubId, sensorId, category, hubDesc, acc, kWh FROM (SELECT *, COUNT(loadAvg) / (24 * 12) AS acc, 24 * AVG(loadAvg)/1000 
AS kWh FROM
(SELECT tblSensorDir.description, tblSensorDir.hubId, tblSensorDir.sensorId, tblSensorDir.category, tblLoadAgg.loadAvg, tblHubDir.description AS hubDesc
FROM tblLoadAgg, tblSensorDir, tblHubDir
WHERE pilot = 1
AND timeStamp >= DATE_SUB('$gap', INTERVAL 1 DAY)
AND timeStamp < '$gap'
AND tblLoadAgg.hubId = tblSensorDir.hubId
AND tblLoadAgg.sensorId = tblSensorDir.sensorId AND tblSensorDir.hubId = tblHubDir.hubId ORDER BY timeStamp DESC) AS t0 GROUP BY t0.hubId, t0.sensorId) AS t1 ORDER BY hubId,
category, sensorId");
        
        while ($row = mysql_fetch_assoc($results)) {
                $data[$row['hubId']][$row['sensorId']] = array('description'=>$row['description'], 'hubDescription'=>$row['hubDesc'], 'category'=>$row['category'], 
'accuracy'=>$row['acc'], 'total'=>$row['kWh']);
        }

        mysql_free_result($results);
        unset($results);
        mysql_close();

        return $data;
}

function getConsumptionToday () {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
        mysql_select_db($database);
	$results = mysql_query("SELECT description, hubId, sensorId, category, hubDesc, acc, kWh, estkWh FROM (SELECT *, COUNT(loadAvg) / (24 * 12) AS acc, 
COUNT(loadAvg)/12 * 
AVG(loadAvg)/1000 AS kWh, 24 * AVG(loadAvg)/1000 AS estkWh FROM
(SELECT tblSensorDir.description, tblSensorDir.hubId, tblSensorDir.sensorId, tblSensorDir.category, tblLoadAgg.loadAvg, tblHubDir.description AS hubDesc
FROM tblLoadAgg, tblSensorDir, tblHubDir
WHERE pilot = 1
AND timeStamp >= FROM_DAYS(TO_DAYS(NOW()))
AND tblLoadAgg.hubId = tblSensorDir.hubId
AND tblLoadAgg.sensorId = tblSensorDir.sensorId AND tblSensorDir.hubId = tblHubDir.hubId ORDER BY timeStamp DESC) AS t0 GROUP BY t0.hubId, t0.sensorId) AS t1 ORDER BY hubId,
category, sensorId");
	
	while ($row = mysql_fetch_assoc($results)) {
		$data[$row['hubId']][$row['sensorId']] = array('description'=>$row['description'], 'hubDescription'=>$row['hubDesc'], 'category'=>$row['category'], 
'accuracy'=>$row['acc'], 'total'=>$row['kWh'], 'estTotal'=>$row['estkWh']);
	}

	mysql_free_result($results);
        unset($results);
        mysql_close();

        return $data;
}

function hubHistory ($hubId, $sensorId) {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
        mysql_select_db($database);

	$where = '';

	if (isset($hubId) && $hubId != "undefined") {
		$where .= "(hubId=".$hubId;
		if (isset($sensorId) && $sensorId != "undefined") {
			$where .= " AND sensorId=".$sensorId;
		} else {
			$results = mysql_query("SELECT * FROM tblSensorDir WHERE hubId=".$hubId." ORDER BY category ASC, sensorId ASC");
			$category = 1;
			$found = false;
			$where .= " AND	(";
		        while ($row = mysql_fetch_assoc($results)) {
				if ($row['category'] > $category && $found) {
					break;
				} else {
					if (!$found) {
						$found = true;
						$category = $row['category'];
					} else {
						$where .= " OR ";
					}
				}
				$where .= "sensorId=".$row['sensorId'];
		        }
			$where .= ")";
		}
		$where .= ") AND";
	} else {
		$results = mysql_query("SELECT * FROM tblSensorDir JOIN tblHubDir ON tblSensorDir.hubId = tblHubDir.hubId WHERE pilot = 1 ORDER BY hubId ASC, category ASC, sensorId ASC");
		$hubId = 0;
                $category = 1;
                $found = false;
                $where .= " (";
                while ($row = mysql_fetch_assoc($results)) {
                	if ($found && $row['hubId'] == $hubId && $row['category'] > $category) {
                                $category = 1;
		                $found = false;
				$hubId++;
				$where .= ")) OR";
                        } else if ($hubId <= $row['hubId']) {
				if ($found && $row['hubId'] > $hubId) {
					$category = 1;
	                                $found = false;
        	                        $hubId++;
                	                $where .= ")) OR";
				}
                                if (!$found) {
					$where .= " (hubId=".$row['hubId']." AND (sensorId=".$row['sensorId'];
					$hubId = $row['hubId'];
                                	$found = true;
                                        $category = $row['category'];
                                } else {
                                        $where .= " OR sensorId=".$row['sensorId'];
                                }
                        }
         	}
                $where .= "))) AND";

	}
        
	$query = "SELECT * FROM (SELECT UNIX_TIMESTAMP(timeStamp) AS time, SUM(loadAvg) as loadAvg FROM tblLoadAgg WHERE ".$where." timeStamp >= (NOW() - INTERVAL 8 DAY) GROUP BY timeStamp ORDER BY time) as reverse ORDER BY time ASC";

	$results = mysql_query($query);
        while ($row = mysql_fetch_assoc($results)) {
		$data[] = array("x" => $row['time'],"y" => $row['loadAvg']);          
        }

	mysql_free_result($results);
	unset($results);
	mysql_close();

	return $data;
}

function gridData () {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
	$data = array();

//	$freqData = json_decode(file_get_contents("http://caniturniton.com/api/json"), true);
//	$freqRec = $freqData['decision']['recommendation'];
	$data['freqRec'] = true;
//	if ($freqRec == "No") {
//		$data['freqRec'] = false;
//	}

//	$co2Data = file_get_contents("http://www.realtimecarbon.org/ws/electricity/services/co2profile/5minute/item");
//	$domObj = new xmlToArrayParser($co2Data);
//	$co2Array = $domObj->array;
	$data['co2Rec'] = true;
//	$data['current'] = floatval($co2Array['electricityCO2Item']['amount']);
//	$data['mean'] = floatval($co2Array['electricityCO2Item']['hhRollingMean']['value']);
//	if ($data['current'] > $data['mean']) {
//		$data['co2Rec'] = false;
//	}

	return $data;
}

function currentHubData ($hubId) {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';	
	$data = currentNetData();
	return $data[$hubId];
}

function currentHubLoad ($hubId) {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';	
	$data = currentHubData($hubId);
	return currentHubLoadFromLocal($data);
}

function currentHubLoadFromLocal ($data) {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';	
	$categoryTotal = array();
        foreach ($data as $hubData) {
		if (isset($categoryTotal[$hubData['category']])) {
                	$categoryTotal[$hubData['category']] += $hubData['load'];
		} else {
			$categoryTotal[$hubData['category']] = $hubData['load'];
		}
        }
        if (isset($categoryTotal[1]) && $categoryTotal[1] > 0) {
                return $categoryTotal[1];
        } else if (isset($categoryTotal[2]) && $categoryTotal[2] > 0) {
                return $categoryTotal[2];
        } else if (isset($categoryTotal[3]) && $categoryTotal[3] > 0) {
                return $categoryTotal[3];
        }
}

function currentNetLoad () {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';	
	$data = currentNetData();
	$load = 0.0;
	foreach ($data as $hubId => $hubData) {
		$load += currentHubLoadFromLocal($hubData);
	}
	return $load;
}

function currentNetDataAuthFull () {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
        mysql_select_db($database);

        $results = mysql_query("SELECT * FROM (SELECT * FROM
(SELECT tblSensorDir.description, tblSensorDir.hubId, tblSensorDir.sensorId, tblSensorDir.category, tblLoad.load, tblHubDir.description AS hubDesc
FROM tblLoad, tblSensorDir, tblHubDir
WHERE timeStamp >= (NOW() - INTERVAL 120 minute)
AND tblLoad.hubId = tblSensorDir.hubId
AND tblLoad.sensorId = tblSensorDir.sensorId AND tblSensorDir.hubId = tblHubDir.hubId ORDER BY timeStamp DESC) AS t0 GROUP BY t0.hubId, t0.sensorId) AS t1 ORDER BY hubId,
category, sensorId");

        $data = array();

        while ($row = mysql_fetch_assoc($results)) {
                $data[$row['hubId']][] = array('sensorId'=>$row['sensorId'], 'description'=>$row['description'], 'category'=>$row['category'], 'load'=>$row['load']);
                if (!isset($data[$row['hubId']]['description'])) {
                        $data[$row['hubId']]['description'] = $row['hubDesc'];
                }
        }

	mysql_free_result($results);

        $results = mysql_query("SELECT * FROM (SELECT *, AVG(loadAvg) as meanLoad, MAX(loadAvg) as maxLoad, STD(loadAvg) as stdLoad FROM
(SELECT tblSensorDir.description, tblSensorDir.hubId, tblSensorDir.sensorId, tblSensorDir.category, tblLoadAgg.loadAvg, tblHubDir.description AS hubDesc
FROM tblLoadAgg, tblSensorDir, tblHubDir
WHERE timeStamp >= (NOW() - INTERVAL 7 DAY)
AND tblLoadAgg.hubId = tblSensorDir.hubId
AND tblLoadAgg.sensorId = tblSensorDir.sensorId AND tblSensorDir.hubId = tblHubDir.hubId ORDER BY tblLoadAgg.loadAvg DESC) AS t0 GROUP BY t0.hubId, t0.sensorId) AS t1 ORDER BY hubId,
category, sensorId");

        while ($row = mysql_fetch_assoc($results)) {
                if ($row['hubId'] != null && isset($data[$row['hubId']])) {
                        foreach ($data[$row['hubId']] as $index => $sensor) {
                                if ($sensor['sensorId'] == $row['sensorId']) {
                                        $data[$row['hubId']][$index]['max'] = $row['maxLoad'];
                                        $data[$row['hubId']][$index]['mean'] = $row['meanLoad'];
                                        $data[$row['hubId']][$index]['std'] = $row['stdLoad'];
                                        break;
				}
                        }
                }
        }

        mysql_free_result($results);
        unset($results);
        mysql_close();

        return $data;
}

function currentNetDataAuth () {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
        mysql_select_db($database);

        $results = mysql_query("SELECT * FROM (SELECT * FROM
(SELECT tblHubDir.pilot, tblSensorDir.description, tblSensorDir.hubId, tblSensorDir.sensorId, tblSensorDir.category, tblLoad.load, tblHubDir.description AS hubDesc
FROM tblLoad, tblSensorDir, tblHubDir
WHERE pilot = 1
AND timeStamp >= (NOW() - INTERVAL 120 minute)
AND tblLoad.hubId = tblSensorDir.hubId
AND tblLoad.sensorId = tblSensorDir.sensorId AND tblSensorDir.hubId = tblHubDir.hubId ORDER BY timeStamp DESC) AS t0 GROUP BY t0.hubId, t0.sensorId) AS t1 ORDER BY hubId, 
category, sensorId");

        $data = array();

        while ($row = mysql_fetch_assoc($results)) {
                $data[$row['hubId']][] = array('sensorId'=>$row['sensorId'], 'description'=>$row['description'], 'category'=>$row['category'], 'load'=>$row['load']);
		if (!isset($data[$row['hubId']]['description'])) {
			$data[$row['hubId']]['description'] = $row['hubDesc'];
		}
        }

	mysql_free_result($results);

	$results = mysql_query("SELECT * FROM (SELECT *, AVG(loadAvg) as meanLoad, MAX(loadAvg) as maxLoad, STD(loadAvg) as stdLoad FROM
(SELECT tblHubDir.pilot, tblSensorDir.description, tblSensorDir.hubId, tblSensorDir.sensorId, tblSensorDir.category, tblLoadAgg.loadAvg, tblHubDir.description AS hubDesc                             
FROM tblLoadAgg, tblSensorDir, tblHubDir
WHERE pilot = 1
AND timeStamp >= (NOW() - INTERVAL 7 DAY)
AND tblLoadAgg.hubId = tblSensorDir.hubId
AND tblLoadAgg.sensorId = tblSensorDir.sensorId AND tblSensorDir.hubId = tblHubDir.hubId ORDER BY tblLoadAgg.loadAvg DESC) AS t0 GROUP BY t0.hubId, t0.sensorId) AS t1 ORDER BY hubId,
category, sensorId");

	while ($row = mysql_fetch_assoc($results)) {
		if ($row['hubId'] != null && isset($data[$row['hubId']])) {
	                foreach ($data[$row['hubId']] as $index => $sensor) {
				if ($sensor['sensorId'] == $row['sensorId']) {
					$data[$row['hubId']][$index]['max'] = $row['maxLoad'];
					$data[$row['hubId']][$index]['mean'] = $row['meanLoad'];
					$data[$row['hubId']][$index]['std'] = $row['stdLoad'];
					break;
				}
                	}
		}
        }

	mysql_free_result($results);
	unset($results);
	mysql_close();

        return $data;
}

function currentNetData () {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
    	mysql_select_db($database);

	$results = mysql_query("SELECT * FROM (SELECT * FROM (SELECT tblSensorDir.hubId, tblSensorDir.sensorId, tblSensorDir.category, tblLoad.load FROM tblLoad, tblSensorDir JOIN tblHubDir ON tblSensorDir.hubId = tblHubDir.hubId 
WHERE
pilot = 1 AND timeStamp >=
(NOW() - INTERVAL 120 minute) AND tblLoad.hubId = tblSensorDir.hubId AND tblLoad.sensorId = tblSensorDir.sensorId ORDER BY timeStamp DESC) AS t0 GROUP BY t0.hubId, t0.sensorId) AS t1 ORDER BY hubId, 
category, sensorId");

	$data = array();

	while ($row = mysql_fetch_assoc($results)) {
		$data[$row['hubId']][] = array('sensorId'=>$row['sensorId'], 'category'=>$row['category'], 'load'=>$row['load']);
	}

	mysql_free_result($results);
	unset($results);
	mysql_close();

	return $data;
}

function countActive () {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die();
        mysql_select_db($database);

        $results = mysql_query("SELECT COUNT(DISTINCT tblLoad.hubId) AS active FROM tblLoad JOIN tblHubDir ON tblLoad.hubId = tblHubDir.hubId WHERE pilot = 1");

        while ($row = mysql_fetch_assoc($results)) {
                $active = $row['active'];
        }

	mysql_free_result($results);
	unset($results);
	mysql_close();

        return intval($active);

}

function prev2DayNetData () {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
    	mysql_select_db($database);

	$results = mysql_query("SELECT * FROM (SELECT tblSensorDir.hubId, tblSensorDir.sensorId, tblSensorDir.category, tblLoadAgg.loadAvg FROM tblSensorDir, tblLoadAgg 
JOIN tblHubDir ON tblLoadAgg.hubId = tblHubDir.hubId
WHERE 
pilot = 1
AND
((timeStamp > DATE_SUB(NOW(), 
INTERVAL 10095 MINUTE) AND 
timeStamp < DATE_SUB(NOW(), INTERVAL 
10065 
MINUTE)) OR (timeStamp > DATE_SUB(NOW(), INTERVAL 20175 MINUTE) 
AND timeStamp < DATE_SUB(NOW(), INTERVAL 20145 MINUTE))) AND 
tblLoadAgg.hubId = tblSensorDir.hubId AND 
tblLoadAgg.sensorId = tblSensorDir.sensorId ORDER BY timeStamp DESC) AS t1 ORDER BY hubId, category, sensorId");

	$data = array();

        while ($row = mysql_fetch_assoc($results)) {
                $data[$row['hubId']][] = array('sensorId'=>$row['sensorId'], 'category'=>$row['category'], 'load'=>$row['loadAvg']);
        }

	mysql_free_result($results);
	unset($results);
	mysql_close();

	$midData = array();

	foreach ($data as $hubId => $hubData) {
		$subTotal = array();
		foreach ($hubData as $reading) {
			if (isset($subTotal[$reading['category']]['load'])) {
				$subTotal[$reading['category']]['load'] += $reading['load'];
			} else {
				$subTotal[$reading['category']]['load'] = $reading['load'];
			}
			if (isset($subTotal[$reading['category']]['count'])) {
				$subTotal[$reading['category']]['count'] += 1;
			} else {
				$subTotal[$reading['category']]['count'] = 1;
			}
		}
		foreach ($subTotal as $category => $value) {
			$subTotal[$category]['load'] /= $subTotal[$category]['count'] * 2;
		}
		if (isset($subTotal[1]['load']) && $subTotal[1]['load'] > 0) {
	                $midData[$hubId]['avg'] = $subTotal[1]['load'];
        	} else if ($subTotal[2]['load'] && $subTotal[2]['load'] > 0) {
                	$midData[$hubId]['avg'] = $subTotal[2]['load'];
  	      	} else if ($subTotal[3]['load'] && $subTotal[3]['load'] > 0) {
        	        $midData[$hubId]['avg'] = $subTotal[3]['load'];
        	}
	}

	$total = 0.0;
	foreach ($midData as $mid) {
		$total += $mid['avg'];
	}

	unset($midData);

	return array('now' => currentNetLoad(), 'mean' => $total, 'count' => countActive());

}

function currentNetDirectoryAuth () {
include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
        mysql_select_db($database);

        $results = mysql_query("SELECT * FROM tblHubDir WHERE pilot = 1");

        $data = array();

        while ($row = mysql_fetch_assoc($results)) {
                $data[$row['hubId']] = array('description'=>$row['description'], 'commissioned' => $row['commissioned'], 'decommissioned' => $row['decommissioned']);
        }

	$results = mysql_query("SELECT tblSensorDir.* FROM tblSensorDir JOIN tblHubDir ON tblSensorDir.hubId = tblHubDir.hubId WHERE pilot = 1");

	while ($row = mysql_fetch_assoc($results)) {
                $data[$row['hubId']]['sensors'][$row['sensorId']] = array('description'=>$row['description'], 'commissioned' => $row['commissioned'], 'decommissioned' => $row['decommissioned'], 'category' => $row['category'], 'parentId' => $row['parentId']);
        }

	unset($results);
	mysql_close();

	return $data;
}

?>
	  
