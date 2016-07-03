<?php
class LiveSmartMeterService {
	public static $tbl = "liveSmartMeter";
	
	function __construct() {
		HookHandler::getInstance()->add("onJanitorDbCheck", "LiveSmartMeterService.janitorDbCheck");
	}
	
	/**
	 * Save the object to the database
	 * @param LiveSmartMeter $object
	 * @return LiveSmartMeter
	 */
	public function save(LiveSmartMeter $object) {
		$bObject = ($object->id > 0) ? R::load(self::$tbl, $object->id) : R::dispense(self::$tbl);
		$bObject = $this->toBean($object, $bObject);
		$object->id = R::store($bObject);
		return $object;
	}
	
	/**
	 * Load an object from the database
	 * @param int $id
	 * @return LiveSmartMeter
	 */
	public function load($id) {
		$bObject = R::load(self::$tbl, $id);
		if ($bObject->id > 0) {
			$object = $this->toObject($bObject);
		}
		return isset($object) ? $object : new LiveSmartMeter();
	}
	
	/**
	 * Retrieve object for an device
	 * @param Device $device
	 * @return LiveSmartMeter
	 */
	public function getLiveByDevice(Device $device) {
		$bObject = R::findOne( self::$tbl, ' invtnum = :deviceId ORDER BY time DESC LIMIT 1', array("deviceId"=>$device->id));
		return $this->toObject($bObject);
	}
	
	public function janitorDbCheck() {
		HookHandler::getInstance()->fire("onDebug", "LiveSmartMeterService janitor DB Check");
		R::wipe(self::$tbl);
	}
	
	private function toBean($object, $bObject) {
		$bObject->time = $object->time;
		$bObject->invtnum = $object->invtnum;
		$bObject->deviceId = $object->deviceId;
		$bObject->gasUsage = $object->gasUsage;
		$bObject->liveGas = $object->liveGas;
		$bObject->highReturn = $object->highReturn;
		$bObject->lowReturn = $object->lowReturn;
		$bObject->highUsage = $object->highUsage;
		$bObject->lowUsage = $object->lowUsage;
		$bObject->liveReturn = $object->liveReturn;
		$bObject->liveUsage = $object->liveUsage;
		return $bObject;
	}
	
	private function toObject($bObject) {
		$object = new LiveSmartMeter();
		if (isset($bObject)) {
			$object->id = $bObject->id;
			$object->time = $bObject->time;
			$object->invtnum = $bObject->invtnum;
			$object->deviceId = $bObject->deviceId;
			$object->gasUsage = $bObject->gasUsage;
			$object->liveGas = $bObject->liveGas;
			$object->highReturn = $bObject->highReturn;
			$object->lowReturn = $bObject->lowReturn;
			$object->highUsage = $bObject->highUsage;
			$object->lowUsage = $bObject->lowUsage;
			$object->liveReturn = $bObject->liveReturn;
			$object->liveUsage = $bObject->liveUsage;
			if ($bObject->liveUsage != null && $bObject->liveReturn != null) {
				$object->liveEnergy = $bObject->liveUsage - $bObject->liveReturn;
			}
		}
		return $object;
	}
}
?>