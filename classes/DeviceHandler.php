<?php
class DeviceHandler {
	public function handleLive($args) {
		$item = $args[0];
		$device = $this->getFreshDevice($args[1]);

		if ($device->active) {
			switch ($device->type) {
				case "production":
					ProductionDeviceHandler::handleLive($item, $device);
					break;
				case "metering":
					MeteringDeviceHandler::handleLive($item, $device);
					break;
				case "weather":
					WeatherDeviceHandler::handleLive($item, $device);
					break;
				default:
					echo("DeviceType " . $device->type . " does not support handle live \n");
					break;
			}
		}
	}

	public function handleHistory($args) {
		$item = $args[0];
		$device = $this->getFreshDevice($args[1]);
	
		if ($device->active) {
			switch ($device->type) {
				case "production":
					ProductionDeviceHandler::handleHistory($item, $device);
					break;
				case "metering":
					MeteringDeviceHandler::handleHistory($item, $device);
					break;
				case "weather":
					WeatherDeviceHandler::handleHistory($item, $device);
					break;
				default:
					echo("DeviceType " . $device->type . " does not support handle history \n");
					break;
			}
		}
	}
	
	
	public function handleAllDeviceHistory($args) {
		foreach (Session::getConfig()->devices as $device) {
			$queueItem = new QueueItem(time(), "DeviceHandler.handleDeviceHistory", array($device), false, 0);
			QueueServer::getInstance()->add($queueItem);
		}
	}
	
	public function handleDeviceHistory($args) {
		$item = $args[0];
		$device = $this->getFreshDevice($args[1]);
		
		if ($device->active) {
			switch ($device->type) {
				case "production":
					ProductionDeviceHandler::handleDeviceHistory($item, $device);
					break;
				case "metering":
					MeteringDeviceHandler::handleDeviceHistory($item, $device);
					break;
				case "weather":
					WeatherDeviceHandler::handleDeviceHistory($item, $device);
					break;
				default:
					echo("DeviceType " . $device->type . " does not support handle device history \n");
					break;
			}
		}
	}

	public function handleEnergy($args) {
		$item = $args[0];
		$device = $this->getFreshDevice($args[1]);
		
		if ($device->active) {
			switch ($device->type) {
				case "production":
					ProductionDeviceHandler::handleEnergy($item, $device);
					break;
				case "metering":
					MeteringDeviceHandler::handleEnergy($item, $device);
					break;
				case "weather":
					WeatherDeviceHandler::handleEnergy($item, $device);
					break;
				default:
					echo("DeviceType " . $device->type . " does not support handle energy \n");
					break;
			}
		}			
	}

	public function handleInfo($args) {
		$item = $args[0];
		$device = $this->getFreshDevice($args[1]);
		
		if ($device->active) {
			switch ($device->type) {
				case "production":
					ProductionDeviceHandler::handleInfo($item, $device);
					break;
				case "metering":
					MeteringDeviceHandler::handleInfo($item, $device);
					break;
				case "weather":
					WeatherDeviceHandler::handleInfo($item, $device);
					break;
				default:
					echo("DeviceType " . $device->type . " does not support handle info \n");
					break;
			}
		}
	}

	public function handleAlarm($args) {
		$item = $args[0];
		$device = $this->getFreshDevice($args[1]);
		
		if ($device->active) {
			switch ($device->type) {
				case "production":
					ProductionDeviceHandler::handleAlarm($item, $device);
					break;
				case "metering":
					MeteringDeviceHandler::handleAlarm($item, $device);
					break;
				case "weather":
					WeatherDeviceHandler::handleAlarm($item, $device);
					break;
				default:
					echo("DeviceType " . $device->type . " does not support handle alarm \n");
					break;
			}
		}
	}
	
	public function handleTest($args) {
		$keyValues = explode("|", $args[1]);
		$params = array();
		foreach ($keyValues as $keyValue) {
			$pair = explode("=", $keyValue);
			$params[$pair[0]] = $pair[1];
		}
		
		$communicationService = new CommunicationService();
		$communication = $communicationService->load($params["communicationId"]);
		$device = Session::getConfig()->getDeviceConfig($params["deviceId"]);
		
		$api = $device->getApi(Session::getConfig());
		$api->setCommunication($communication, $device);
		$result = $api->doCommunicationTest();
		
		$communication->lastTestTime = time();
		$communication->lastTestResult = $result["result"];
		$communication->lastTestData = $result["testData"];
		//$communication->lastTestSettings = $communication->toJson();
		$communication->lastTestSettings = NULL;
		$communicationService->save($communication);
		
		/*
		 * Set device testMode "off"
		 */
		$deviceService = new DeviceService();
		$deviceService->setTestMode($params["deviceId"],false);
		
	}

	/**
	 * We need to get the device from the config else we keep talking to
	 * the device object set during the start off the queueServer
	 * @param Device $device
	 * @return Device
	 */
	public function getFreshDevice(Device $device) {
		return Session::getConfig()->getDeviceConfig($device->id);
	}
}
?>