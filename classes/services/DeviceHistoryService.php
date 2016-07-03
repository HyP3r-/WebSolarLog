<?php
class DeviceHistoryService {
	public static $tbl = "deviceHistory";
	
	/**
	 * Save the object to the database
	 * @param Panel $object
	 * @return Panel
	 */
	public function save(DeviceHistory $object) {
		$bObject = ($object->id > 0) ? R::load(self::$tbl, $object->id) : R::dispense(self::$tbl);
		$bObject = $this->toBean($object, $bObject);
		$object->id = R::store($bObject);
		return $object;
	}
	
	/**
	 * Load an object from the database
	 * @param int $id
	 * @return Panel
	 */
	public function load($id) {
		$bObject = R::load(self::$tbl, $id);
		if ($bObject->id > 0) {
			$object = $this->toObject($bObject);
		}
		return isset($object) ? $object : new DeviceHistory();
	}
	
	/**
	 * Retrieve all values for an device
	 * @param Device $device
	 * @return array of DeviceHistory
	 */
	public function getArrayByDevice(Device $device) {
		$bObjects = R::find( self::$tbl, ' deviceId = :deviceId ', array("deviceId"=>$device->id));
		$objects = array();
		foreach ($bObjects as $bObject) {
			$objects[] = $this->toObject($bObject);
		}
		return $objects;
	}
	
	/**
	 * add or update the Device History
	 * @param DeviceHistory $deviceHistory
	 */
	public function addOrUpdateDeviceHistoryByDeviceAndTime(DeviceHistory $deviceHistory) {
		$bean =  R::findOne( self::$tbl, ' deviceId = :deviceId AND time = :time ',
				array(':deviceId'=>$deviceHistory->deviceId, ':time' => $deviceHistory->time)
		);
	
		if (!$bean){
			$bean = R::dispense(self::$tbl);
		}
		
		$bean = $this->toBean($deviceHistory, $bean);
		$bean->deviceId = $deviceHistory->deviceId;
		$bean->time = $deviceHistory->time;
		$bean->amount = $deviceHistory->amount;
		$bean->processed = $deviceHistory->processed;
	
		$deviceHistory->id = R::store($bean);
		return $deviceHistory;
	}
	
	private function toBean($object, $bObject) {
		$bObject->deviceId = $object->deviceId;
		$bObject->time = $object->time;
		$bObject->amount = $object->amount;
		$bObject->processed = $object->processed;
		return $bObject;
	}
	
	private function toObject($bObject) {
		$object = new DeviceHistory();
		if (isset($bObject)) {
			$object->id = $bObject->id;
			$object->deviceId = $bObject->deviceId;
			$object->time = $bObject->time;
			$object->amount = $bObject->amount;
			$object->processed = $bObject->processed;
		}
		return $object;
	}
}
?>