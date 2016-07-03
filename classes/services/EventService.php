<?php
class EventService {
	public static $tbl = "event";
	
	function __construct() {
		$this->panelService = new PanelService();
		HookHandler::getInstance()->add("onJanitorDbCheck", "EventService.janitorDbCheck");
	}
	
	/**
	 * Save the object to the database
	 * @param Event $object
	 * @return Event
	 */
	public function save(Event $object) {
		$bObject = ($object->id > 0) ? R::load(self::$tbl, $object->id) : R::dispense(self::$tbl);
		$bObject = $this->toBean($object, $bObject);
		$object->id = R::store($bObject);
		return $object;
	}

	/**
	 * Load an object from the database
	 * @param int $id
	 * @return Event
	 */
	public function load($id) {
		$bObject = R::load(self::$tbl, $id);
		if ($bObject->id > 0) {
			$object = $this->toObject($bObject);
		}
		return isset($object) ? $object : new Event();
	}
	
	/**
	 * retrieve events by device and type
	 * @param Device $device
	 * @param string $type
	 * @param number $limit
	 * @return multitype:Event
	 */
	public function getArrayByDeviceAndType($device, $type, $limit=20) {
	
		$bObjects =  R::find( self::$tbl,
				' INV = :deviceId and  lower(Type) =  lower(:type) ORDER BY time DESC LIMIT :limit',
				array(':deviceId'=>$device->id,':type'=>$type, ':limit'=>$limit)
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
		$bObject->Type = $object->type;
		$bObject->Event = $object->event;
		$bObject->alarmSend = $object->alarmSend;
		return $bObject;
	}
	
	private function toObject($bObject) {
		$object = new Event($bObject->deviceId, $bObject->time, $bObject->Type, $bObject->Event);
		if (!isset($bObject)) {
			return $object;
		}
		$object->id = $bObject->id;
		$object->INV = $bObject->INV;
		$object->deviceId = $bObject->deviceId;
		$object->SDTE = $bObject->SDTE;
		$object->time = $bObject->time;
		$object->type = $bObject->Type;
		$object->event = $bObject->Event;
		$object->alarmSend = $bObject->alarmSend;
		
		
		// Remove all enters
		$order   = array("\r\n", "\n", "\r");
		// Processes \r\n's first so they aren't converted twice.
		$eventHTML = str_replace($order, ' ', $object->event);
		$eventHTML = preg_replace('!\s+!', ' ', $eventHTML);
		
		// Remove duplicate alarms		
		$eventHTML = preg_split('/(Alarm)/', $eventHTML, -1, PREG_SPLIT_DELIM_CAPTURE);
		if(is_array($eventHTML) && count($eventHTML)>1){
			$eventHTML = $eventHTML[1].' '.substr($eventHTML[2],4,strlen($eventHTML[2]));
		}else{
			$eventHTML = trim($eventHTML[0]);
		}
		
		$object->eventHTML = $eventHTML;
		
		
		return $object;
	}
	
	public function janitorDbCheck() {
		HookHandler::getInstance()->fire("onDebug", "EventService janitor DB Check");
		
		// Delete Info events older the 1 week;
		R::exec('DELETE FROM Event WHERE Type="Info" AND time < ' . strtotime("-1 week"));
	}
}
?>