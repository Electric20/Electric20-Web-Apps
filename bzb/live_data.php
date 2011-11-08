 <?php
	session_start();

	require_once("../dataStore/query.php");

	$raw = currentNetDataAuthFull();

		$raw = $raw[$_GET['hubId']];

		$data = array();
		$max = 0;
		foreach ($raw as $index => $sensorData) {
			if (count($sensorData) > 1) {
				if (
					(isset($_GET['sensorId']) && $_GET['sensorId'] != "undefined" && $sensorData['sensorId'] != $_GET['sensorId']) ||
					($sensorData['max'] == 0 && $sensorData['load'] == 0)
				) {} 
				else {
					$data[] = array(
						'title' => getTitle($sensorData['category'])." ".$sensorData['sensorId'],
						'subtitle' => $sensorData['description'],
						'hubId' => $_GET['hubId'],
						'sensorId' => $sensorData['sensorId'],
						'ranges' => array(
							0,
							max(0, intval($sensorData['mean']) - intval($sensorData['std'])),
							max(0, intval($sensorData['mean']) + intval($sensorData['std'])),
							max(intval($sensorData['max']), $sensorData['load'])
						),
						'measures' => array(intval($sensorData['load'])),
						'markers' => array(intval($sensorData['load']))
					);
					if (intval($sensorData['max']) > $max) {
						$max = intval($sensorData['max']);
					}
					if (intval($sensorData['load']) > $max) {
                	                	$max = intval($sensorData['load']);
                        		}
				}
			}
		}

		for ($i = 0; $i < count($data); $i++) {
			$data[$i]['ranges'][] = $max;
			$data[$i]['ranges'][] = $max * 1.1;
		}

		echo(json_encode($data));

		unset($max);
		unset($data);
	unset($raw);

	function getTitle ($category) {
		if ($category == 1) {
			return "Hub";
		} else if ($category == 2) {
			return "Circuit";
		} else if ($category == 3) {
			return "Appliance";
		}
	}
?>
