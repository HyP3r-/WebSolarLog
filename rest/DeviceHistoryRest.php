<?php
class DeviceHistoryRest {
	private $deviceService;
	private $deviceHistoryService;
	private $energyService;
	private $historyService;
	
	/**
	 * Constructor
	 */
	function __construct() {
		$this->deviceService = new DeviceService();
		$this->deviceHistoryService = new DeviceHistoryService();
		$this->energyService = new EnergyService();
		$this->historyService = new HistoryService();
	}
	
	/**
	 * Destructor
	 */
	function __destruct() {
		$this->historyService = null;
		$this->energyService = null;
		$this->deviceHistoryService = null;
		$this->deviceService = null;
	}

	/**
	 * Rest functions 
	 */
	public function GET($request, $options) {
	}
	
	
	public function getCompare($options) {
		$deviation = $options[2];
		$device = $this->deviceService->load($options[1]);
		$arDeviceHistory = $this->deviceHistoryService->getArrayByDevice($device);
		
		$diff = array();
		$errors = array();
		foreach ($arDeviceHistory as $deviceHistory) {
			//echo ($deviceHistory->time);
			//$deviceHistory = new DeviceHistory();
			$energy = $this->energyService->getEnergyByDeviceAndTime($device, $deviceHistory->time);
			if ($energy) {
				if (abs($energy->KWH - $deviceHistory->amount) > $deviation) {
					$diff[] = array ("energy"=>$energy, "deviceHistory"=>$deviceHistory, "diff"=>($energy->KWH - $deviceHistory->amount));
				}
			} else {
				$errors[] = array("deviceHistory"=>$deviceHistory, "error"=>array("code"=>"ENERGYNOTFOUND", "message"=>"Could not find energy"));	
			}
		}
		
		return array("compare"=>$diff, "errors"=>$errors);
	}
	
}
?>