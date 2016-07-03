<?php
class BasicChecksAddon {
	private $adapter;
	private $deviceService;
	private $eventService;
	private $liveService;
	private $config;
	
	function __construct() {
		$this->adapter = PDODataAdapter::getInstance();
		$this->config = Session::getConfig();
		$this->deviceService = new DeviceService();
		$this->eventService = new EventService();
		$this->liveService = new LiveService();
	}
	
	function __destruct() {
		$this->liveService = null;
		$this->eventService = null;
		$this->deviceService = null;
		$this->config = null;
		$this->adapter = null;
	}
	
	public function onNewLive($args) {
		$device = $args[1];
		$sessionKey = 'noLiveCounter-' . $device->id;
		
		// Check if the sun is up and the device is down
		if(!Util::isSunDown() && $device->state != 1) {
			if ($this->deviceService->changeDeviceStatus($device, 1)) {
				$event = new Event($device->id, time(), "Notice", "Inverter " . $device->name . " online");
				$this->eventService->save($event);
				
				HookHandler::getInstance()->fire("onInverterStartup", $device);
				$device->state = 1;
			}			
		}
		
		if (isset($_SESSION[$sessionKey])) {
			unset($_SESSION[$sessionKey]);
		}
	}
	
	public function onNoLiveData($args) {
		$device = $args[1];
		$deviceApi = $device->getApi(Session::getConfig());
		
		// detect if the server is down
		$offline = false;
		if ($deviceApi->getState() == 1  ) {
			// we are offline
			$offline = true;
		}		
		if ($deviceApi->getState() == 0  ) { // Auto detect if offline
			$sessionKey = 'noLiveCounter-' . $device->id;
			
			$liveCounter = (integer) (isset($_SESSION[$sessionKey]) ? $_SESSION[$sessionKey] : 0);
			$liveCounter++;
			if ($liveCounter == 30) {
				// Are we seriously down?
				if(Util::isSunDown()) {
					$offline = true;
				} else {
					// Probably temporarely down, check again
					if (PeriodHelper::isPeriodJob("ShutDownJobErrorINV-" . $device->id, 60)) {
						$event = new Event($device->id, time(), "Notice", "Inverter " . $device->name . " offline during day");
						$this->eventService->save($event);
						
						HookHandler::getInstance()->fire("onInverterError", $device, "Inverter seems to be down");
					}
					$liveCounter = 0;
				}		
			}
			
			// Reset the counter if we are still live and the counter > 30
			if ($device->state == 1 && $liveCounter > 30) {
					$liveCounter = 0;			
			}
			
			$_SESSION[$sessionKey] = $liveCounter;
		}
		
		if ($offline) {
			if ($this->deviceService->changeDeviceStatus($device, 0)) {
				if (PeriodHelper::isPeriodJob("ShutDownJobINV-" . $device->id, (2 * 60))) {
					$event = new Event($device->id, time(), "Notice", "Inverter " . $device->name . " offline");
					$this->eventService->save($event);
					
					HookHandler::getInstance()->fire("onInverterShutdown", $device);
					$device->state = 0;
					
				}
			}
		}
	}
	
	public function on10MinJob($args) {
		$this->InactiveCheck();

		// Only in debug mode display all items in queue
		if ($this->config->debugmode) {
			QueueServer::printDebugInfo();
		}
	}

	public function onInActiveJob($args) {
		$this->InactiveCheck();	
	}
	
	private function InactiveCheck() {
		// Only continue when the sun is up!
		// 1800, means start checking 30 min after the sun is up and before it is down
		if (Util::isSunDown(1800)) {
			return true;
		}
	
		// Only check every 30 minutes
		if (PeriodHelper::isPeriodJob("InactiveCheckJob", 30)) {
			foreach ($this->config->devices as $device) {
				// for now we only want Live-record of production device, only the write in the Live table.
				if($device->type == "production"){
					$live = $this->liveService->getLiveByDevice($device);
					if (!empty($live->time) && (time() - $live->time > 3600)) {
						$event = new Event($device->id, time(), 'Check', "Live data for inverter '" . $device->name . "' not updated for more then one hour.");
						$subject = "WSL :: Event message";
						$body  = "Hello, \n\n We have detected an problem on your device.\n\n";
						$body .= $event->event . "\n\n";
						$body .= "Please check if everything is alright.\n\n";
						$body .= "Time of live data : " . date("d-m-Y H:i:s", $live->time) . "\n\n";
						$body .= "Please check if everything is alright.\n\n";
						$body .= "WebSolarLog";
		
						$event->alarmSend = Common::sendMail($subject, $body, $this->config);
						
						$eventService = new EventService();
						$eventService->save($event);
					}
				}
			}
		}
	}
	
}
?>