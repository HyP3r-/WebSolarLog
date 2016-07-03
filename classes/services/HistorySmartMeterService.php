<?php
class HistorySmartMeterService {
	public static $tbl = "historySmartMeter";
	
	function __construct() {
		HookHandler::getInstance()->add("onJanitorDbCheck", "HistorySmartMeterService.janitorDbCheck");
	}
	
	/**
	 * Save the object to the database
	 * @param HistorySmartMeter $object
	 * @return HistorySmartMeter
	 */
	public function save(HistorySmartMeter $object) {
		$bObject = ($object->id > 0) ? R::load(self::$tbl, $object->id) : R::dispense(self::$tbl);
		$bObject = $this->toBean($object, $bObject);
		$object->id = R::store($bObject);
		return $object;
	}
	
	/**
	 * Load an object from the database
	 * @param int $id
	 * @return HistorySmartMeter
	 */
	public function load($id) {
		$bObject = R::load(self::$tbl, $id);
		if ($bObject->id > 0) {
			$object = $this->toObject($bObject);
		}
		return isset($object) ? $object : new HistorySmartMeter();
	}

	public function PVoutputSmartMeterData($time){
		$utils = new Util();
		// get the bean 10 min. before and after the given time.
		$parameters = array( ':timeBefore' => ($time-900),':timeAfter'=>($time+900));
		//HookHandler::getInstance()->fire("onInfo", "parameters::".print_r($parameters,true));
		
		$beans = R::find( "historySmartMeter"," time >= :timeBefore AND time <=:timeAfter ",$parameters);
		//HookHandler::getInstance()->fire("onInfo", "beans::".print_r($beans,true));
		
		if(count($beans)>0){
			// find closest SmartMeter History Bean.
			$closest = $utils->findClosestBeanBasedOnDate($beans,$time);
			//HookHandler::getInstance()->fire("onInfo", "closest::".print_r($closest,true));
			$energy = $closest['closestBean']['lowUsage']+$closest['closestBean']['highUsage'];
			$power = $closest['closestBean']['liveUsage'];
			return array("energy"=>$energy,"power"=>$power);
		}else{
			return array("energy"=>"0","power"=>"0");
		}
	}

	
	/**
	 * Delete an object from the database
	 * @param int $id
	 * @return bool 
	 */
	public function delete($id) {
		// load bean to delete
		$bObject = R::load(self::$tbl, $id);
		$object = $this->toObject($bObject);
		$bObject = R::load(self::$tbl, $id);
		// trash the bean
		R::trash($bObject);
		// check if bean still there and return result.
		return (R::load(self::$tbl, $id)->id>0) ? false : true;
	}
	
	public function janitorDbCheck() {
		HookHandler::getInstance()->fire("onDebug", "HistorySmartMeterService janitor DB Check");
		// Get an HistorySmartMeter and save it, to make sure al fields are available in the database
		$bObject = R::findOne( self::$tbl, ' 1=1 LIMIT 1');
		if ($bObject) {
			$object = $this->toObject($bObject);
			R::store($this->toBean($object, $bObject));
			HookHandler::getInstance()->fire("onDebug", "Updated HistorySmartMeter");
		} else {
			HookHandler::getInstance()->fire("onDebug", "HistorySmartMeter object not found");			
		}

		R::exec("UPDATE historySmartMeter SET deviceId = invtnum");
	}
	
	private function toBean($object, $bObject) {
		$bObject->time = $object->time;
		$bObject->invtnum = $object->invtnum;
		$bObject->deviceId = $object->deviceId;
		$bObject->gasUsage = $object->gasUsage;
		$bObject->highReturn = $object->highReturn;
		$bObject->lowReturn = $object->lowReturn;
		$bObject->highUsage = $object->highUsage;
		$bObject->lowUsage = $object->lowUsage;
		$bObject->liveReturn = $object->liveReturn;
		$bObject->liveUsage = $object->liveUsage;
		return $bObject;
	}
	
	private function toObject($bObject) {
		$object = new HistorySmartMeter();
		if (isset($bObject)) {
			$object->id = $bObject->id;
			$object->time = $bObject->time;
			$object->invtnum = $bObject->invtnum;
			$object->deviceId = $bObject->deviceId;
			$object->gasUsage = $bObject->gasUsage;
			$object->highReturn = $bObject->highReturn;
			$object->lowReturn = $bObject->lowReturn;
			$object->highUsage = $bObject->highUsage;
			$object->lowUsage = $bObject->lowUsage;
			$object->liveReturn = $bObject->liveReturn;
			$object->liveUsage = $bObject->liveUsage;
		}
		return $object;
	}
}
?>