<?php
class ProductionDeviceHandler {
	
	public static function handleLive(QueueItem $item, Device $device) {
		// Get the api we need to use
		$api = $device->getApi(Session::getConfig());
		
		$live = null;
		try {
			/*
			 * prevent polling on sunset
			* This stops polling 30 min. after sunset and start polling again 30 min before sunrise
			*/
			// Retrieve the device data
			$live = (Util::isSunDown(-1800)==false || $device->testMode==true) ? $api->getLiveData() : null;
			
			if ($live == null) {
				if ($device->state == 1) {
					// No valid live data returned
					HookHandler::getInstance()->fire("onNoLiveData", $device);					
				}
				return null;
			}
		} catch (ConverterException $e) {
			if ($device->state == 1) {
				HookHandler::getInstance()->fire("onInverterError", $device, $e->getMessage());
			}
			return null;
		}
		
		// Set some variables
		$live->deviceId = $device->id;
		
		// TODO 
		// Needs to be replaced with deviceId in future
		$live->INV = $device->id;
		 
		$live->time = $item->time;
		$live->SDTE = date("Ymd-H:i:s", $item->time);
		
		// Calculate IP
		$live->IP = $live->I1P+$live->I2P;
		if (!empty($live->IP)) {
			$live->I1Ratio = round(($live->I1P/$live->IP)*100,3);
			$live->I2Ratio = round(($live->I2P/$live->IP)*100,3);
			$live->I3Ratio = round(($live->I3P/$live->IP)*100,3);
		}
		
		// Round some variables
		$live->EFF = round($live->EFF,3);
		
		// Fire the hook
		HookHandler::getInstance()->fire("onLiveData", $device, $live);
	}
	
	public static function handleHistory(QueueItem $item, Device $device) {
		$liveService = new LiveService();

		// Only create history when the device is online
		if ($device->state == 1 ) {
			$live = $liveService->getLiveByDevice($device);
			
			if(isset($live->time)){
				if ( time() - $live->time > 30 * 60 ) {
					// We don't want to create an history item for an live record older then 30 minutes
					HookHandler::getInstance()->fire("onDebug", "History blocked: live record to old! live->time = " . date("Y/m/d H:i:s", $live->time ));
				} else {
					// Set the time to this job time
					$live->time = $item->time;
					$live->SDTE = date("Ymd-H:i:s", $item->time);
					HookHandler::getInstance()->fire("onHistory", $device, $live, $item->time);
				}
			}else{
				HookHandler::getInstance()->fire("onDebug", "No Live object from getLiveByDevice");
			}
		}
	}
	
	public static function handleDeviceHistory(QueueItem $item, Device $device) {
		$deviceHistoryService = new DeviceHistoryService();
		
		// Get the api we need to use
		$api = $device->getApi(Session::getConfig());
	
		// Retrieve the device data
		$deviceHistoryList = $api->getHistoryData();
		
		// Did we get an result?
		if ($deviceHistoryList == null) {
			if (Session::getConfig()->debugmode) {
				HookHandler::getInstance()->fire("onDebug", "DeviceHistory nothing returned by device");
			}
			return; // Nothing to do
		}
		
		// Save all objects if they do not exist
		$notProcessed = 0;
		foreach ($deviceHistoryList as $deviceHistory) {
			$deviceHistory->deviceId = $device->id;
			$newDeviceHistory = $deviceHistoryService->addOrUpdateDeviceHistoryByDeviceAndTime($deviceHistory);
			
			if ($newDeviceHistory != null && !$newDeviceHistory->processed) {
				$notProcessed++;
			}
		}
		HookHandler::getInstance()->fire("onInfo", "Retrieved " . count($deviceHistoryList) . 
			" records from history off device " . $device->name . ". There are " . $notProcessed .  " new unprocessed lines.\n");
	}

	public static function handleEnergy(QueueItem $item, Device $device) {
		HookHandler::getInstance()->fire("onEnergy", $device, $item->time);
	}

	public static function handleInfo(QueueItem $item, Device $device) {
		$info = trim($device->getApi(Session::getConfig())->getInfo());
		if ($info != "") {
			HookHandler::getInstance()->fire("onInverterInfo", $device, $info);
		}
	}
	
	public static function handleAlarm(QueueItem $item, Device $device) {
		$alarm = trim($device->getApi(Session::getConfig())->getAlarms());
		if ($alarm != "") {
			$event = new Event($device->id, time(), 'Alarm', Util::formatEvent($alarm));
			if (self::isAlarmDetected($event)) {
				HookHandler::getInstance()->fire("onInverterAlarm", $device, $event);
			}
		}
	}
	
	/**
	 * Check if the line is filled with an real alarm
	 * TODO :: This should move to the device api section
	 * @param Event $event
	 * @return boolean
	 */
	private static function isAlarmDetected($event) {
		$event_text = trim($event->event);
		$event_lines = explode("\n", $event_text);
	
		$alarmFound = false;
		foreach ($event_lines as $line) {
			// Aurora error
			$parts = explode(":", $line);
			if (count($parts) > 1 && trim($parts[1]) != "No Alarm") {
				$alarmFound = true;
				break;
			}
		}
	
		// SMA
		if (trim($event->event) == "Fehler -------") {
			$alarmFound = false;
		}
	
		return $alarmFound;
	}
}
?>