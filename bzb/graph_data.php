<?php
	error_reporting(0);
	session_start();

	require_once('../dataStore/utility.php');
	require_once('../dataStore/query.php');

	$raw = currentNetDataAuth();

	$data = array();
	$links = array();

	$data[] = array('label' => '', 'rgb' => -1);

	$index = 1;
	$netLoad = 0;
	$maxLoad = 0;
	$totalLoad = 0;
	foreach ($raw as $hubId => $hubData) {
			$thisIndex = $index;
			$data[$thisIndex] = array('label' => 'Hub '.$hubId, 'hubId' => $hubId);
			$links[$thisIndex - 1] = array('source' => 0, 'target' => $thisIndex);
			if (isset($_SESSION['loggedIn'])) {
				$data[$thisIndex]['label'] = $hubData['description'];
			}
			$catLoads = array();
			$level = 4;
			foreach ($hubData as $sensorData) {
				if ($sensorData['category'] <= $level && intval($sensorData['load']) > 0) {
					$level = $sensorData['category'];
					if (count($hubData)>2) {
						$index++;
						$data[] = array('label' => 'Sensor '.$sensorData['sensorId'], 'rgb' => 0, 'hubId' => $hubId, 'sensorId' => $sensorData['sensorId'], 'value' => intval($sensorData['load']));
						if (isset($_SESSION['loggedIn'])) {
        	             				$data[$index]['label'] = $sensorData['description'];
                				}
						$links[] = array('source' => $thisIndex, 'target' => $index);
						$links[$index - 1]['value'] = min(intval($sensorData['load']), 500);
					}
					$totalLoad += intval($sensorData['load']);
					if ($sensorData['load'] > $maxLoad) {
						$maxLoad = $sensorData['load'];
					}
				}
			}
			$links[$thisIndex - 1]['value'] = max(1, min(intval($totalLoad), 500));
			$data[$thisIndex]['value'] = intval($totalLoad);
			if ($totalLoad > $maxLoad) {
               			$maxLoad = $totalLoad;
                	}
			if ($totalLoad > 0) {
				$index++;
			}
			$netLoad += intval($totalLoad);
			$totalLoad = 0;
	}

	$data[0]['value'] = $netLoad;

	foreach ($data as $index => $datum) {
		if ($datum['value'] != NULL && $index > 0) {
			$data[$index]['rgb'] = min(255, intval(255 * $datum['value'] / $maxLoad));
		}
	}

	echo json_encode(array('data'=>$data, 'links'=>$links));

	unset($data);
	unset($raw);
	unset($links);

?>	  
