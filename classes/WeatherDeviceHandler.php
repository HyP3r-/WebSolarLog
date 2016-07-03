<?php
class WeatherDeviceHandler {
	
	function __construct() {
		// Initialize services
	}
	
	function __destruct() {
		// Destroy services
	}
	
	public static function handleLive(QueueItem $item, Device $device) {
		// Not supported
	}

	public static function handleHistory(QueueItem $item, Device $device) {
		$weather = $device->getApi(Session::getConfig())->getData();
		if ($weather != null) {
			$weather->deviceId = $device->id;
			$weather->time = $item->time;
	
			$weatherService = new WeatherService();
			$weatherService->save($weather);
		}
	}

	public static function handleDeviceHistory(QueueItem $item, Device $device) {
		// Not supported
	}
	
	public static function handleEnergy(QueueItem $item, Device $device) {
		// Not supported
	}

	public static function handleInfo(QueueItem $item, Device $device) {
		$info = trim($device->getApi(Session::getConfig())->getInfo());
		if ($info != "") {
			HookHandler::getInstance()->fire("onInverterInfo", $device, $info);
		}
	}
	
	
	public static function handleAlarm(QueueItem $item, Device $device) {
		// Not supported
	}
}
?>