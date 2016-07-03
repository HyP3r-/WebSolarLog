<?php
class HistoryService {
	public static $tbl = "history";
	public static $config;
	
	function __construct() {
		HookHandler::getInstance()->add("onJanitorDbCheck", "HistoryService.janitorDbCheck");
		self::$config = Session::getConfig(); 
	}
	

	public function janitorDbCheck() {
		HookHandler::getInstance()->fire("onDebug", "HistoryService janitor DB Check");
		// We reasonly discovered a bug which created NULL records in the history table, so we need to delete these records.
		R::exec("DELETE FROM 'history' WHERE deviceId is null");
	}
	
	/**
	 * Save the object to the database
	 * @param History $object
	 * @return History
	 */
	public function save(History $object) {
		$bObject = ($object->id > 0) ? R::load(self::$tbl, $object->id) : R::dispense(self::$tbl);
		$bObject = $this->toBean($object, $bObject);

		if($bObject->deviceId){
			// try to fix a late start with adding a dummy record
			$this->fixLateStartAddDummyRecord($bObject);
			
			// store history object to This table
			$object->id = R::store($bObject);
		}else{
			HookHandler::getInstance()->fire("onDebug","We tried to save a bean with no DeviceId. DebugInfo:".get_parent_class($object));
		}
		return $object;
	}
	
	/**
	 * 
	 * Add a dummy record so the graph and daily figures could be fixed.
	 * 
	 * @param Device $bObject
	 */
	public function fixLateStartAddDummyRecord($bObject){
		// get last saved history item
		$lastHistoryItem = $this->getLastHistory($bObject);
			
		// get today begin/end timestamps
		$todayTimestamp = Util::getBeginEndDate('today',1);
			
		// get today suninfo
		$sunInfo = date_sun_info( $todayTimestamp['beginDate'] , self::$config->latitude, self::$config->longitude );
		
		/*
		 * here we do 2 checks;
		* 1. is current KWHT - last['KWHT'] > 0
		* 2. is sunrise timestamp - last['time'] > 21600 (6 hours) (we
		* 3. check if the day of the last record AND today are different
		* if these checks are good;
		* we are probably started later with logging and so make a dummy record to fix the graph and daily production
		*/
		if((
				($bObject->KWHT - $lastHistoryItem['KWHT']) > 0) AND 
				(($sunInfo['sunrise'] - $lastHistoryItem['time']) > 21600) AND 
				(date('z',$sunInfo['sunrise']) != date('z',$lastHistoryItem['time']))
		){
			//  write to logging that we are trying to "fix" some things
			HookHandler::getInstance()->fire("onDebug","It looks like we started this morning to late, so lets 'fix' that by adding a dummy record");
			
			// get a valid history bean
			$bObjectFix = R::dispense(self::$tbl);
		
			// set the deviceId of the bean to the current deviceId
			$bObjectFix->deviceId = $bObject->deviceId;
			$bObjectFix->INV = $bObject->deviceId;
		
			// set bean->time to sunrise minus half an hour to be sure that its the first records for today
			$bObjectFix->time = ($sunInfo['sunrise']-1800);
		
			// set the KWHT of the bean to the last known KWHT value
			$bObjectFix->KWHT = $lastHistoryItem['KWHT'];
		
			// store the Fix bean/record
			$object->id = R::store($bObjectFix);
		}
	}

	public function getLastHistory($device){

		$bean = R::getAll('select * from '.self::$tbl.' WHERE deviceId = :deviceId ORDER BY id DESC LIMIT 1',array(":deviceId"=>$device->deviceId));
		return $bean[0];
	}
	
	/**
	 * Load an object from the database
	 * @param int $id
	 * @return History
	 */
	public function load($id) {
		$bObject = R::load(self::$tbl, $id);
		if ($bObject->id > 0) {
			$object = $this->toObject($bObject);
		}
		return isset($object) ? $object : new History();
	}
	

	/**
	 * Check to see if this is a PVoutput record
	 * @return 0/1
	 */
	public function CheckPVoutputSend($deviceId) {
		$bObject = R::getall('select * from '.self::$tbl.' where pvoutputSend = 1 AND deviceId = :deviceId ORDER BY id DESC LIMIT 1',array(":deviceId"=>$deviceId));
		if($bObject[0]['id'] > 0){
			if((time() - $bObject[0]['time']) >= 300){
				return '1';
			}else{
				return '0';
			}
		}else{
			// We do not have a record, so this record needs to be a PVoutput record
			return '1';
		}
	}
	
	/**
	 * Read the history file
	 * @param Device $device
	 * @param string $date
	 * @return array of History
	 */
	public function getArrayByDeviceAndTime(Device $device, $date) {
		(!$date)? $date = date('d-m-Y') : $date = $date;
		$beginEndDate = Util::getBeginEndDate('day', 1,$date);
	
		$bObjects =  R::find( self::$tbl,
				' INV = :deviceId AND time > :beginDate AND  time < :endDate ORDER BY time',
				array(':deviceId'=>$device->id,':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate'])
		);
		
		$objects = array();
		foreach ($bObjects as $bObject) {
			$objects[] = $this->toObject($bObject);
		}
		return $objects;
	}
	
	private function toBean($object, $bObject) {
		$bObject->INV = $object->INV;
		$bObject->deviceId = $object->deviceId;
		$bObject->SDTE = $object->SDTE;
		$bObject->time = $object->time;
		$bObject->dayNum = $object->dayNum;
		
		$bObject->I1V = round($object->I1V,3);
		$bObject->I1A = round($object->I1A,3);
		$bObject->I1P = round($object->I1P,3);
		$bObject->I1Ratio = round($object->I1Ratio,3);
		
		$bObject->I2V = round($object->I2V,3);
		$bObject->I2A = round($object->I2A,3);
		$bObject->I2P = round($object->I2P,3);
		$bObject->I2Ratio = round($object->I2Ratio,3);
		
		$bObject->I3V = round($object->I3V,3);
		$bObject->I3A = round($object->I3A,3);
		$bObject->I3P = round($object->I3P,3);
		$bObject->I3Ratio = round($object->I3Ratio,3);
		
		$bObject->GV = round($object->GV,3);
		$bObject->GA = round($object->GA,3);
		$bObject->GP = round($object->GP,3);
		
		$bObject->GV2 = round($object->GV2,3);
		$bObject->GA2 = round($object->GA2,3);
		$bObject->GP2 = round($object->GP2,3);
		
		$bObject->GV3 = round($object->GV3,3);
		$bObject->GA3 = round($object->GA3,3);
		$bObject->GP3 = round($object->GP3,3);
		
		$bObject->IP = round($object->IP);
		$bObject->ACP = round($object->ACP,3);
		
		$bObject->FRQ = round($object->FRQ,3);
		$bObject->EFF = round($object->EFF,3);
		$bObject->INVT = round($object->INVT,3);
		$bObject->BOOT = round($object->BOOT,3);
		$bObject->KWHT = round($object->KWHT,3);
		$bObject->pvoutput = $object->pvoutput;
		$bObject->pvoutputErrorMessage = $object->pvoutputErrorMessage;
		$bObject->pvoutputSend = $object->pvoutputSend;
		$bObject->pvoutputSendTime = $object->pvoutputSendTime;
		return $bObject;
	}
	
	private function toObject($bObject) {
		$object = new History();
		if (!isset($bObject)) {
			return $object;
		}
		$object->id = $bObject->id;
		$object->INV = $bObject->INV;
		$object->deviceId = $bObject->deviceId;
		$object->SDTE = $bObject->SDTE;
		$object->time = $bObject->time;
		$object->dayNum = $bObject->dayNum;
		
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
		
		$object->GV = $bObject->GV;
		$object->GA = $bObject->GA;
		$object->GP = $bObject->GP;
		
		$object->GV2 = $bObject->GV2;
		$object->GA2 = $bObject->GA2;
		$object->GP2 = $bObject->GP2;
		
		$object->GV3 = $bObject->GV3;
		$object->GA3 = $bObject->GA3;
		$object->GP3 = $bObject->GP3;
		
		$object->IP = $bObject->IP;
		$object->ACP = $bObject->ACP;
		
		$object->FRQ = $bObject->FRQ;
		$object->EFF = $bObject->EFF;
		$object->INVT = $bObject->INVT;
		$object->BOOT = $bObject->BOOT;
		$object->KWHT = $bObject->KWHT;
		$object->pvoutput = $bObject->pvoutput;
		$object->pvoutputErrorMessage = $bObject->pvoutputErrorMessage;
		$object->pvoutputSend = $bObject->pvoutputSend;
		$object->pvoutputSendTime = $bObject->pvoutputSendTime;
		return $object;
	}
}
?>