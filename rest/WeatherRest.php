<?php
class WeatherRest {
	private $deviceService;
	private $weatherService;
	
	/**
	 * Constructor
	 */
	function __construct() {
		$this->weatherService = new WeatherService();
		$this->deviceService = new DeviceService();
	}
	
	/**
	 * Destructor
	 */
	function __destruct() {
		$this->deviceService = null;
		$this->weatherService = null;
	}

	/**
	 * Rest functions 
	 */
	
	public function GET($request, $options) {
		$type = "today"; // Default
		$deviceId = -1; // All
		if (count($options) > 0) {
			$type = (trim($options[0]) != "") ? strtolower($options[0]) : $type;
		}
		if (count($options) > 1) {
			$deviceId = (trim($options[1]) != "") ? strtolower($options[1]) : $type;
		}
		
		
		$result = array();
		$result['type'] = $type;
		$result['data'] = null;
		switch ($type) {
			case "today":
				if ($deviceId > 0) {
					$device = $this->deviceService->load($deviceId);
					$result['data'] =array(array( "deviceId"=>$deviceId, "data"=>$this->weatherService->getWeatherForDate($device)));
				} else {
					$weathers = array();
					foreach (Session::getConfig()->devices as $device) {
						if ($device->type == "weather") {
							$weathers[] = array("deviceId"=>$device->id, "data"=>$this->weatherService->getWeatherForDate($device));
						}
					}
					$result['data'] = $weathers;
				}
				break;
			case "live":
				if ($deviceId > 0) {
					$device = $this->deviceService->load($deviceId);
					$result['data'] =array(array( "deviceId"=>$deviceId, "data"=>$this->weatherService->getLastWeather($device)));
				} else {
					$weathers = array();
					foreach (Session::getConfig()->devices as $device) {
						if ($device->type == "weather") {
							$weathers[] = array("deviceId"=>$device->id, "data"=>$this->weatherService->getLastWeather($device));
						}
					}
					$result['data'] = $weathers;
				}
				break;
			default:
				$result['message'] = "not supported";
		}
		
		return $result;
	}	
}
?>