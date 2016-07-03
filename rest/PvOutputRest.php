<?php
class PvOutputRest {
	
	/**
	 * Constructor
	 */
	function __construct() {
		$this->PvOutputAddon = new PvOutputAddon();
	}
	
	/**
	 * Destructor
	 */
	function __destruct() {
		$this->PvOutputAddon = null;
	}

	/**
	 * Rest functions 
	 */
	public function GET($request, $options) {
		$id = -1;
		if (count($options) > 0) {
			$date = (trim($options[0]) != "") ? strtolower($options[0]) : $type;
		}
		if (count($options) > 0) {
			$deviceId = (trim($options[1]) != "") ? strtolower($options[1]) : $type;
		}
		if ($date > 0) {
			return $this->PvOutputAddon->getPvOutputDayData($date,$deviceId);
		}
	}
	
	/**
	 * Returns devices with just a few fields
	 * @param unknown $options
	 * @return multitype:multitype:NULL
	 */
	public function getShortList($options) {
		$result = array();
		
		$devices = Session::getConfig()->devices; // Active devices
		if ( $options[1] == 'true' || $options[1] == 1) {
			$devices = Session::getConfig()->allDevices;
		}
		
		foreach ($devices as $device) {
			$result[] = array("id"=>$device->id, "name"=>$device->name,"type"=>$device->type);
		}
		return $result;
	}
	
	/**
	 * 
	 * @param unknown $request
	 * @param unknown $options
	 */
	public function DELETE($request, $options){
		return $this->deviceService->delete($options[0]);
	}
}
?>