<?php
class EnergyService {
	public static $tbl = "energy";
	private $config;
	
	function __construct() {
		HookHandler::getInstance()->add("onJanitorDbCheck", "EnergyService.janitorDbCheck");
		$this->config = Session::getConfig();
		
	}
	
	/**
	 * Save the object to the database
	 * @param Energy $object
	 * @return Energy
	 */
	public function save(Energy $object) {
		$bObject = ($object->id > 0) ? R::load(self::$tbl, $object->id) : R::dispense(self::$tbl);
		$bObject = $this->toBean($object, $bObject);
		$object->id = R::store($bObject);
		return $object;
	}

	/**
	 * Load an object from the database
	 * @param int $id
	 * @return Energy
	 */
	public function load($id) {
		$bObject = R::load(self::$tbl, $id);
		if ($bObject->id > 0) {
			$object = $this->toObject($bObject);
		}
		return isset($object) ? $object : new Energy();
	}
	
	/**
	 * add or update the Energy
	 * @param Energy $energy
	 * @param $time
	 * @return $energy
	 */
	public function addOrUpdateEnergyByDeviceAndTime(Energy $energy, $time) {
		$beginEndDate = Util::getBeginEndDate('day', 1, $time);
		$bean =  R::findOne( self::$tbl, ' INV = :deviceId AND time > :beginDate AND time < :endDate ',
				array(':deviceId'=>$energy->deviceId, ':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate'])
		);
	
		if (!$bean){
			$bean = R::dispense(self::$tbl);
		}
	
		$bean = $this->toBean($energy, $bean);
		
		// Only save record if there is something
		if (!empty($energy->KWH) && !empty($energy->KWHT)) {
			$energy->id = R::store($bean);
		}
		return $energy;
	}
	
	public function onSummary($args){
		$device = $args[1];
		$date = $args[2];
		
		if($device->type == "production"){

			$energy = self::getEnergyByDeviceAndTime($device,$date);
			$data = array();
			$data['KWH'] 	  =  $energy->KWH;
			$data['costs'] 	  =  ($energy->KWH*$this->config->costkwh)/100;
			$data['CO2avoid'] =  ($energy->KWH*$this->config->co2kwh)/1000;
			$data['trees'] =  $energy->KWH*$this->config->co2CompensationTree;
			

			return $data;
		}else{
			return;
		}
	}
	
	/**
	 * Retrieves all energy lines
	 * @return Array of Energy
	 */
	public function getEnergyList() {
		$bObjects = R::find( self::$tbl, ' ORDER BY time');
		$objects = array();
		foreach ($bObjects as $bObject) {
			$objects[] = $this->toObject($bObject);
		}
		return $objects;
	}

	/**
	 * Retrieves all energy lines by device
	 * @return Array of Energy
	 */
	public function getEnergyListByDevice(Device $device) {
		$bObjects = R::find( self::$tbl, ' INV = :deviceId ORDER BY time', array("deviceId"=>$device->id));
		$objects = array();
		foreach ($bObjects as $bObject) {
			$objects[] = $this->toObject($bObject);
		}
		return $objects;
	}
	
	/**
	 * get the Energy for an device
	 * @param Device $energy
	 * @param $time
	 * @return $energy
	 */
	public function getEnergyByDeviceAndTime(Device $device, $time) {
		$beginEndDate = Util::getBeginEndDate('day', 1,$time);

		
		$bean =  R::findOne( self::$tbl, ' INV = :deviceId AND time > :beginDate AND time < :endDate LIMIT 1 ',
				array(':deviceId'=>$device->id, ':beginDate'=>$beginEndDate['beginDate']+60,':endDate'=>$beginEndDate['endDate']-60)
		);
	

		if (!$bean){
			return null;
		}
	
		return $this->toObject($bean);
	}
	
	public function janitorDbCheck() {
		HookHandler::getInstance()->fire("onDebug", "EnergyService janitor DB Check");
		
		// Delete empty rows
		R::exec("DELETE FROM Energy WHERE kwh=0 AND kwht is null;");
	}
	
	private function toBean($object, $bObject) {
		$bObject->INV = $object->INV;
		$bObject->deviceId = $object->deviceId;
		$bObject->SDTE = $object->SDTE;
		$bObject->time = $object->time;
		$bObject->KWH = $object->KWH;
		$bObject->KWHT = $object->KWHT;
		$bObject->KWHKWP = $object->KWHKWP; // kWh per kWp
		$bObject->co2 = $object->co2;
		return $bObject;
	}
	
	private function toObject($bObject) {
		$object = new Energy();
		if (isset($bObject)) {
			$object->id = $bObject->id;
			$object->INV = $bObject->INV;
			$object->deviceId = $bObject->deviceId;
			$object->SDTE = $bObject->SDTE;
			$object->time = $bObject->time;
			$object->KWH = $bObject->KWH;
			$object->KWHT = $bObject->KWHT;
			$object->KWHKWP = $bObject->KWHKWP;
			$object->co2 = $bObject->co2;
		}
		return $object;
	}
}
?>