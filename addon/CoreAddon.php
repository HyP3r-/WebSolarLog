<?php
class CoreAddon {
	private $adapter;
	private $energyService;
	private $eventService;
	private $historyService;
	private $liveService;
	private $config;

	function __construct() {
		$this->adapter = PDODataAdapter::getInstance();
		$this->config = Session::getConfig();
		$this->energyService = new EnergyService();
		$this->eventService = new EventService();
		$this->historyService = new HistoryService();
		$this->liveService = new LiveService();
	}

	function __destruct() {
		$this->liveService = null;
		$this->historyService = null;
		$this->eventService = null;
		$this->energyService = null;
		$this->config = null;
		$this->adapter = null;
	}

	/**
	 * Handle hook onLiveData
	 * @param unknown $args
	 */
	public function onLiveData($args) {
		$device = $args[1];
		$live = $args[2];
	
		if ($device == null) {
			HookHandler::getInstance()->fire("onError", "CoreAddon::onLiveData() $device == null");
			return;
		}
		
		// Get the current live object
		$dbLive = $this->liveService->getLiveByDevice($device);
		if ($dbLive != null) {
			$live->id = $dbLive->id;
		}
	
		// Save the live information
		$this->liveService->save($live);
		HookHandler::getInstance()->getInstance()->fire("newLiveData", $device, $live);
	
		// Check the Max value
		$this->checkMaxPowerValue($device, $live);
	}
	
	/**
	 * Handle hook onHistory
	 * @param unknown $args
	 */
	public function onHistory($args) {
		$device = $args[1];
		$live = $args[2];
		$timestamp = $args[3];

		// Only add history when the device is live
		if ($device->state == 1) {
			$history = $live->toHistory();
			$history->INV = $device->id;
			$history->deviceId = $device->id;
			$history->time = $timestamp;
			$history->pvoutputSend = $this->historyService->CheckPVoutputSend($device->id);
			$this->historyService->save($history);
			hookHandler::getInstance()->fire("newHistory", $device, $timestamp);
		}
	}
	
	public function onEnergy($args) {
		//echo "CoreAddon onEnergy";
		//var_dump($args);
		$device = $args[1];
		$timestamp = $args[2];
		
		$arHistory = $this->historyService->getArrayByDeviceAndTime($device, null);
		
		if (count($arHistory) < 1) {
			// We can only calculate energy if we have history lines
			return;
		}
	
		$first = reset($arHistory);
		$last = end($arHistory);
	
		$productionStart = $first->KWHT;
		$productionEnd = $last->KWHT;
	
		// Check if we passed 100.000kWh
		if ($productionEnd < $productionStart) {
			$productionEnd += 100000;
		}
		$production = round($productionEnd - $productionStart, 3);
	
		// Set the new values and save it
		$energy = new Energy();
		$energy->SDTE = $first->SDTE;
		$energy->time = $timestamp;
		$energy->INV = $device->id;
		$energy->deviceId = $device->id;
		$energy->KWH = $production;
		$energy->KWHT = $productionEnd;
		$energy->co2 = Formulas::CO2kWh($production, $this->config->co2kwh); // Calculate co2
		$energy = $this->energyService->addOrUpdateEnergyByDeviceAndTime($energy, date('d-m-Y'));
		HookHandler::getInstance()->fire("newEnergy", $device, $energy);
	}
	
	/**
	 * Handle hook onInverterInfo
	 * @param unknown $args
	 */
	public function onInverterInfo($args) {
		$device = $args[1];
		$info = $args[2];
		
		// Write InverterInfo (firmware,model,etc) to DB
		$event = new Event($device->id, time(), 'Info', $info);
		$this->eventService->save($event);
		HookHandler::getInstance()->fire("newInfo", $device, $event);		
	}
	
	/**
	 * Handle hook onInverterAlarm
	 * @param unknown $args
	 */
	public function onInverterAlarm($args) {
		$device = $args[1];
		$alarm = $args[2];
		
		try {
			if (strpos($alarm->event, 'Warning') !== false ) {
				HookHandler::getInstance()->fire("onInverterWarning", $device, nl2br($alarm->event));
			}
			if (strpos($alarm->event, 'Error') !== false ) {
				HookHandler::getInstance()->fire("onInverterError", $device, nl2br($alarm->event));
			}
			$alarm->alarmSend = true;
		} catch (Exception $e) {
			$alarm->alarmSend = false;
			HookHandler::getInstance()->fire("onError", $e->getMessage());
		}
		$this->eventService->save($alarm);
		HookHandler::getInstance()->fire("newAlarm", $device, $alarm);		
	}
	
	/**
	 * Check if the live value is higher then the saved value
	 * if true, then save it
	 * @param Device $device
	 * @param Live $live
	 */
	private function checkMaxPowerValue($device, $live) {
		// Get the highest value off the day
		$currentMPT = $this->adapter->readMaxPowerToday($device->id);
		$COEF=$live->EFF/100;
		$COEF=($COEF > 1) ? 1 : $COEF;
		$GP2 = round($live->GP * $COEF,2);
		if (!isset($currentMPT) || $GP2 > $currentMPT->GP) {
			// Found a new max power of today
			$Ompt = new MaxPowerToday();
			$Ompt->SDTE = $live->SDTE;
			$Ompt->time = Util::getUTCdate($live->SDTE);
			$Ompt->GP = $GP2;
			$this->adapter->writeMaxPowerToday($device->id, $Ompt);
			HookHandler::getInstance()->fire("newMaxPowerToday", $device, $Ompt);
		}
	}
	
	public function touchPidFile(){
		$filename = Session::getBasePath().'/scripts/server.php.pid';
		@touch($filename); // Surpress permission denied errors with @
	}
}