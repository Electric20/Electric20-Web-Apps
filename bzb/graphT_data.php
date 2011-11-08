<?php
	session_start();

	require_once('../dataStore/utility.php');
	require_once('../dataStore/query.php');

	$raw = currentNetDirectoryAuth();

	$data = array('name' => 'Neighbourhood');

	$index = 0;

	foreach ($raw as $hubId => $hubData) {
		if ($hubData['decommissioned'] == null) {
		$thisIndex = $index;
		$data['children'][] = array('name' => 'Hub '.$hubId, 'hubId' => $hubId);
		if (isset($_SESSION['loggedIn'])) {
			$data['children'][$thisIndex]['name'] = $hubData['description'];
			if ($hubData['decommissioned'] != null) {
				$data['children'][$thisIndex]['decommissioned'] = true;
			}
		}

		$remaining = array();
		$sIndex = 0;
		foreach ($hubData['sensors'] as $sensorId => $sensorData) {
			if ($sensorData['parentId'] == null) {
				$data['children'][$thisIndex]['children'][] = array('name' => 'Sensor '.$sensorId, 'sensorId' => $sensorId, 'hubId' => $hubId);
				if (isset($_SESSION['loggedIn'])) {
		                        $data['children'][$thisIndex]['children'][$sIndex]['name'] = $sensorData['description'];
                		        if ($sensorData['decommissioned'] != null)	{
                                		$data['children'][$thisIndex]['children'][$sIndex]['decommissioned'] = true;
                  		      	}       
                		}
				$sIndex++;
			} else {
				$remaining[$sensorId] = $sensorData;
			}
		}

		foreach ($remaining as $sensorId => $sensorData) {
			foreach ($data['children'][$thisIndex]['children'] as $childIndex => $child) {
				if ($child['sensorId'] == $sensorData['parentId']) {
					$data['children'][$thisIndex]['children'][$childIndex]['children'][] = array('name' => 
'Sensor '.$sensorId, 'sensorId' => $sensorId, 'hubId' => $hubId);
					$sIndex = count($data['children'][$thisIndex]['children'][$childIndex]['children']) - 1;
					if (isset($_SESSION['loggedIn'])) {
 	                                       $data['children'][$thisIndex]['children'][$childIndex]['children'][$sIndex]['name'] = $sensorData['description'];
        	                                if ($sensorData['decommissioned'] != null) {
                	                                $data['children'][$thisIndex]['children'][$childIndex]['children'][$sIndex]['decommissioned'] = true;
                        	                }
                 	 		}
				}
			}
		}

		$index++;
		}
	}

	echo json_encode($data);

	unset($raw);
	unset($data);

?>	  
