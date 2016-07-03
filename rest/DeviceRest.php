<?php
class DeviceRest {
	private $deviceService;
	
	/**
	 * Constructor
	 */
	function __construct() {
		$this->deviceService = new DeviceService();
	}
	
	/**
	 * Destructor
	 */
	function __destruct() {
		$this->deviceService = null;
	}

	/**
	 * Rest functions 
	 */
	public function GET($request, $options) {
		$id = -1;
		if (count($options) > 0) {
			$id = (trim($options[0]) != "") ? strtolower($options[0]) : $type;
		}
		if ($id > 0) {
			return $this->deviceService->load($id);
		}
		
		$result = array();
		foreach (Session::getConfig()->devices as $device) {
			$result[] = $this->deviceService->load($device->id);
		}
		return $result;
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