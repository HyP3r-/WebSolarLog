<?php
class CommunicationService {
	public static $tbl = "communication";
	
	/**
	 * Save the object to the database
	 * @param Communication $object
	 * @return Communication
	 */
	public function save(Communication $object) {
		$bObject = ($object->id > 0) ? R::load(self::$tbl, $object->id) : R::dispense(self::$tbl);
		$bObject = $this->toBean($object, $bObject);
		$object->id = R::store($bObject);
		return $object;
	}
	
	/**
	 * Load an object from the database
	 * @param int $id
	 * @return Communication
	 */
	public function load($id) {
		$bObject = R::load(self::$tbl, $id);
		if ($bObject->id > 0) {
			$object = $this->toObject($bObject);
		}
		return isset($object) ? $object : new Communication();
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
	 * Retrieves all Communications
	 * @return Array of Communications
	 */
	public function getList() {
		$bObjects = R::find( self::$tbl);
		$objects = array();
		foreach ($bObjects as $bObject) {
			$objects[] = $this->toObject($bObject);
		}
		return $objects;
	}
	
	private function toBean($object, $bObject) {
		$bObject->type = $object->type;
		$bObject->name = $object->name;
		$bObject->uri = $object->uri;
		$bObject->port = $object->port;
		$bObject->timeout = $object->timeout;
		$bObject->optional = $object->optional;
		$bObject->lastTestTime  = $object->lastTestTime;
		$bObject->lastTestSettings = $object->lastTestSettings;
		$bObject->lastTestResult = $object->lastTestResult;
		$bObject->lastTestData = $object->lastTestData;
		return $bObject;
	}
	
	private function toObject($bObject) {
		$object = new Communication();
		if (isset($bObject)) {
			$object->id = $bObject->id;
			$object->type = $bObject->type;
			$object->name = $bObject->name;
			$object->uri = $bObject->uri;
			$object->port = $bObject->port;
			$object->timeout = $bObject->timeout;
			$object->optional = $bObject->optional;
			$object->lastTestTime  = $bObject->lastTestTime;
			$object->lastTestTimeFormatted  = ($object->lastTestTime) ? date("Y-m-d H:i:s", $bObject->lastTestTime) : null;
			$object->lastTestSettings = $bObject->lastTestSettings;
			$object->lastTestResult = $bObject->lastTestResult;
			$object->lastTestData = $bObject->lastTestData;
		}
		return $object;
	}
}
?>