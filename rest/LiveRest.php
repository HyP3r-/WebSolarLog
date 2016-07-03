<?php
class LiveRest {
	private $weatherService;
	private $liveService;
	private $liveSmartMeterService;
	
	/**
	 * Constructor
	 */
	function __construct() {
		$this->weatherService = new WeatherService();
		$this->liveService = new LiveService();
		$this->liveSmartMeterService = new LiveSmartMeterService();
	}
	
	/**
	 * Destructor
	 */
	function __destruct() {
		$this->liveSmartMeterService = null;
		$this->liveService = null;
		$this->weatherService = null;
	}

	/**
	 * Rest functions 
	 */
	
	public function GET($request, $options) {
		$result = array();
		
		$totalsProduction = array("devices"=>0,"GP"=>0,"GP2"=>0,"GP3"=>0);
		$totalsMetering = array("devices"=>0,"liveEnergy"=>0);
		foreach (Session::getConfig()->devices as $device) {
			$type = $device->type;
			$live = null;
			switch ($type) {
				case "production":
					$live = $this->liveService->getLiveByDevice($device);
					$totalsProduction["devices"] = $totalsProduction["devices"] + 1;
					$totalsProduction["GP"] = $totalsProduction["GP"] + $live->GP;
					$totalsProduction["GP2"] = $totalsProduction["GP2"] + $live->GP2;
					$totalsProduction["GP3"] = $totalsProduction["GP3"] + $live->GP3;
					$overallProduction = $overallProduction + $live->GP + $live->GP2 + $live->GP3;
					break;
				case "metering":
					$live = $this->liveSmartMeterService->getLiveByDevice($device);
					$totalsMetering["devices"] = $totalsMetering["devices"] + 1;
					$totalsMetering["liveEnergy"] = $totalsMetering["liveEnergy"] + $live->liveEnergy;
					$overallMetering = $overallMetering +$totalsMetering["liveEnergy"]; 
					break;
				case "weather":
					$live = $this->weatherService->getLastWeather($device);					
					break;
			}
			$result[] = array("type"=>$type, "id"=>$device->id, "name"=>$device->name, "data"=>$live);
		}
		
		$result["totals"] = array("production"=>$totalsProduction, "metering"=>$totalsMetering, "overallUsage" =>($overallProduction+$overallMetering));
		return $result;
	}
	
	public function getCategory() {
		return new CategoryRest();
	}

	/**
	 * Non rest functions
	 */
	public function loadProduct($id) {
		return $this->productService->get($id);
	}
}
?>