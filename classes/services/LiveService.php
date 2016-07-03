<?php
class LiveService {
	public static $tbl = "live";
	
	function __construct() {
		HookHandler::getInstance()->add("onJanitorDbCheck", "LiveService.janitorDbCheck");
	}
	
	/**
	 * Save the object to the database
	 * @param Live $object
	 * @return Live
	 */
	public function save(Live $object) {
		$bObject = ($object->id > 0) ? R::load(self::$tbl, $object->id) : R::dispense(self::$tbl);
		$bObject = $this->toBean($object, $bObject);
		$object->id = R::store($bObject);
		return $object;
	}

	/**
	 * Load an object from the database
	 * @param int $id
	 * @return Live
	 */
	public function load($id) {
		$bObject = R::load(self::$tbl, $id);
		if ($bObject->id > 0) {
			$object = $this->toObject($bObject);
		}
		return isset($object) ? $object : new Live();
	}
	
	/**
	 * Truncate live table
	 * @return Live
	 */
	public function truncateTable() {
		R::wipe(self::$tbl);
	}
	
	/**
	 * Retrieve object for an device
	 * @param Device $device
	 * @return Live
	 */
	public function getLiveByDevice(Device $device) {
		$bObject = R::findOne( self::$tbl, ' INV = :deviceId ORDER BY time DESC LIMIT 1', array("deviceId"=>$device->id));
		if (!$bObject) {
			HookHandler::getInstance()->fire("onDebug", "Could not find live record for device id=" . $device->id);
			return null;
		}
		return $this->toObject($bObject);
	}
	
	public function janitorDbCheck() {
		HookHandler::getInstance()->fire("onDebug", "LiveService janitor DB Check");
		R::wipe(self::$tbl);
	}
	
	private function toBean($object, $bObject) {
		$bObject->INV = $object->INV;
		$bObject->deviceId = $object->deviceId;
		$bObject->I1V = $object->I1V;
		$bObject->I1A = $object->I1A;
		$bObject->I1P = $object->I1P;
		$bObject->I1Ratio = $object->I1Ratio;

		$bObject->I2V = $object->I2V;
		$bObject->I2A = $object->I2A;
		$bObject->I2P = $object->I2P;
		$bObject->I2Ratio = $object->I2Ratio;

		$bObject->I3V = $object->I3V;
		$bObject->I3A = $object->I3A;
		$bObject->I3P = $object->I3P;
		$bObject->I3Ratio = $object->I3Ratio;

		$bObject->GA = $object->GA;
		$bObject->GP = $object->GP;
		$bObject->GV = $object->GV;

		$bObject->GA2 = $object->GA2;
		$bObject->GP2 = $object->GP2;
		$bObject->GV2 = $object->GV2;

		$bObject->GA3 = $object->GA3;
		$bObject->GP3 = $object->GP3;
		$bObject->GV3 = $object->GV3;
		
		$bObject->FRQ = $object->FRQ;
		$bObject->EFF = $object->EFF;
		$bObject->INVT = $object->INVT;
		
		$bObject->time = $object->time;
		$bObject->SDTE = $object->SDTE;
		$bObject->BOOT = $object->BOOT;
		$bObject->KWHT = $object->KWHT;
		$bObject->IP = $object->IP;
		$bObject->ACP = $object->ACP;
		return $bObject;
	}
	
	private function toObject($bObject) {
		$object = new Live();
		if (!isset($bObject)) {
			return $object;
		}
		$object->id = $bObject->id;
		$object->INV = $bObject->INV;
		$object->deviceId = $bObject->deviceId;
		$object->I1V = $bObject->I1V;
		$object->I1A = $bObject->I1A;
		$object->I1P = $bObject->I1P;
		$object->I1Ratio = $bObject->I1Ratio;
		
		$object->I2V = $bObject->I2V;
		$object->I2A = $bObject->I2A;
		$object->I2P = $bObject->I2P;
		$object->I2Ratio = $bObject->I2Ratio;
		
		$object->I3V = $bObject->I3V;
		$object->I3A = $bObject->I3A;
		$object->I3P = $bObject->I3P;
		$object->I3Ratio = $bObject->I3Ratio;

		$object->GA = $bObject->GA;
		$object->GP = $bObject->GP;
		$object->GV = $bObject->GV;

		$object->GA2 = $bObject->GA2;
		$object->GP2 = $bObject->GP2;
		$object->GV2 = $bObject->GV2;

		$object->GA3 = $bObject->GA3;
		$object->GP3 = $bObject->GP3;
		$object->GV3 = $bObject->GV3;
		
		$object->FRQ = $bObject->FRQ;
		$object->EFF = $bObject->EFF;
		$object->INVT = $bObject->INVT;
		
		$object->time = $bObject->time;
		$object->BOOT = $bObject->BOOT;
		$object->KWHT = $bObject->KWHT;
		$object->ACP = $bObject->ACP;
		return $object;
	}
}
?>