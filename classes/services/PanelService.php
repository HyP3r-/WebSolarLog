<?php
class PanelService {
	public static $tbl = "panel";
	
	/**
	 * Save the object to the database
	 * @param Panel $object
	 * @return Panel
	 */
	public function save(Panel $object) {
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
		return isset($object) ? $object : new Panel();
	}
	
	/**
	 * Delete an object from the database
	 * @param int $id
	 * @return bool
	 */
	public function delete($id) {
		// load bean to delete
		$bObject = R::load(self::$tbl, $id);
		// trash the bean
		R::trash($bObject);
		// check if bean still there and return result.
		return (R::load(self::$tbl, $id)->id>0) ? false : true;
	}
	
	
	/**
	 * Retrieve all values for an device
	 * @param Device $device
	 * @return array of Panel
	 */
	public function getArrayByDevice(Device $device) {
		$bObjects = R::find( self::$tbl, ' inverterId = :deviceId ', array("deviceId"=>$device->id));
		$objects = array();
		foreach ($bObjects as $bObject) {
			$objects[] = $this->toObject($bObject);
		}
		return $objects;
	}
	
	private function toBean($object, $bObject) {
		$bObject->inverterId = $object->inverterId;
		$bObject->deviceId = $object->deviceId;
		$bObject->description = $object->description;
		$bObject->roofOrientation = $object->roofOrientation;
		$bObject->roofPitch = $object->roofPitch;
		$bObject->amount = $object->amount;
		$bObject->wp = $object->wp;
		return $bObject;
	}
	
	private function toObject($bObject) {
		$object = new Panel();
		if (isset($bObject)) {
			$object->id = $bObject->id;
			$object->inverterId = $bObject->inverterId;
			$object->deviceId = $bObject->deviceId;
			$object->description = $bObject->description;
			$object->roofOrientation = $bObject->roofOrientation;
			$object->roofPitch = $bObject->roofPitch;
			$object->amount = $bObject->amount;
			$object->wp = $bObject->wp;
		}
		return $object;
	}
}
?>