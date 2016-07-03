<?php
class WeatherService {
	public static $tbl = "weather";
	private $config;
	
	function __construct() {
		HookHandler::getInstance()->add("onJanitorDbCheck", "WeatherService.janitorDbCheck");
		$this->config = Session::getConfig();
	}
	
	/**
	 * Save the object to the database
	 * @param Weather $object
	 * @return Weather
	 */
	public function save(Weather $object) {
		$bObject = ($object->id > 0) ? R::load(self::$tbl, $object->id) : R::dispense(self::$tbl);
		$bObject = $this->toBean($object, $bObject);
		$object->id = R::store($bObject);
		return $object;
	}
	
	/**
	 * Load an object from the database
	 * @param int $id
	 * @return Weather
	 */
	public function load($id) {
		$bObject = R::load(self::$tbl, $id);
		if ($bObject->id > 0) {
			$object = $this->toObject($bObject);
		}
		return isset($object) ? $object : new Weather();
	}
	
	public function PVoutputWeatherData($time){
		$utils = new Util();
		// get the bean 10 min. before and after the given time.
		$parameters = array( ':timeBefore' => ($time-600),':timeAfter'=>($time+600));
		
		$beans =  R::find( 'weather', ' time >= :timeBefore AND time <= :timeAfter ', $parameters);
		
		if(count($beans)>0){
			$closest = $utils->findClosestBeanBasedOnDate($beans,$time);
			return $closest['closestBean']['temp'];
		}else{
			return "0";
		}
	}
	
	/**
	 * Retrieve all values for an device
	 * @param Device $device
	 * @return array of Weather
	 */
	public function getArrayByDevice(Device $device) {
		$bObjects = R::find( self::$tbl, ' deviceId = :deviceId ORDER BY time ', array("deviceId"=>$device->id));
		$objects = array();
		foreach ($bObjects as $bObject) {
			$objects[] = $this->toObject($bObject);
		}
		return $objects;
	}
	
	/**
	 * Retrieve the last object
	 * @param Device $device
	 * @return Weather
	 */
	public function getLastWeather(Device $device) {
		$bean = R::findOne('weather',' deviceId = :deviceId ORDER BY time DESC LIMIT 1',array(':deviceId'=>$device->id));
		return $this->toObject($bean);
	}
	
	/**
	 * Retrieve all objects for the given device and for the given date (default today)
	 * @param Device $device
	 * @param string $date
	 * @return array of Weather
	 */
	public function getWeatherForDate(Device $device, $date=null) {
		(!$date)? $date = date('d-m-Y') : $date = $date;
		$beginEndDate = Util::getBeginEndDate('day', 1,$date);
	
		$beans =  R::find( 'weather', ' where deviceId = :deviceId AND time > :beginDate AND time < :endDate ORDER BY time',
				array(':deviceId'=>$device->id,':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate'])
		);
		
		$objects = array();
		foreach ($beans as $bean) {
			$objects[] = $this->toObject($bean);
		}
	
		return $objects;
	}
	
	public function onSummary($args) {
		$device = $args[1];
		$date = $args[2];
		
		if($device->deviceApi == "Open-Weather-Map"){
	
			(!$date)? $date = date('d-m-Y') : $date = $date;
			$beginEndDate = Util::getBeginEndDate('day', 1,$date);
			
			
			// from findAll(return beans) to getAll(return array)
			$beans =  R::findAll('weather', ' WHERE deviceId = :deviceId AND time > :beginDate AND time < :endDate ',
					array(':deviceId'=>$device->id,':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate'])
			);
		
			$i=0;
			$temp = 0;
			$degreeDays = 0;
			$sumWindSpeed = 0;
			$sumTemp = 0;
			
			foreach($beans as $bean){
				$sumTemp += (float)$bean['temp'];
				$sumWindSpeed += (float)$bean['wind_speed'];
				$i++;
			}
			
			$countTemp = count($beans);
			
			
			$degreeDays = round($degreeDays/$i,2);
			
			$avgTemp = round($sumTemp/$i,2);
			$avgWind = round($sumWindSpeed/$i,2);
			
			if($avgTemp<18){
				$degreeDays = (18 - ((float)$avgTemp));
			}else{
				$degreeDays = 0;
			}
			$avgBeaufort = Util::beaufortScale($avgWind);
			$beaufort = Util::beaufortScale((float)$bean['wind_speed']);
			$lastBean = end($beans);

			$currentWindChill = (is_array($lastBean)) ? Util::getWindChill((float)$lastBean['wind_speed'],(float)round($lastBean['temp'],1)) : 0;
			
			$today = Util::getBeginEndDate('today', 1);
			
			
			$return = array(
					"time"=>$lastBean['time'],
					"weatherSamples"=>$i,
					"beaufort"=>$beaufort,
					"avgBeaufort"=>$avgBeaufort,
					"avgTemp"=>round($avgTemp,1),
					"avgWindSpeed"=>round($avgWind,1),
					"avgWindChill"=>Util::getWindChill((float)round($avgWind,1),(float)round($lastBean['temp'],1)),
					"currentTemp"=>round($lastBean['temp'],1),
					"currentWindChill"=>$currentWindChill,
					"countTemp"=>$countTemp,
					"sunInfo"=>date_sun_info($today["beginDate"],$this->config->latitude,$this->config->longitude),
					"minTemp"=>round($lastBean['temp_min'],1),
					"maxTemp"=>round($lastBean['temp_max'],1),
					"degreeDays"=>$degreeDays,
					"windDirection" =>(int)$lastBean['wind_direction'],
					"humidity" =>$lastBean['humidity'],
					"pressure" =>$lastBean['pressure'],
					"conditionId" =>$lastBean['conditionId'],
					"wind_speed" =>$lastBean['wind_speed'],
					"rain1h" =>($lastBean['rain1h']==0)? 0 : $lastBean['rain1h'],
					"rain3h" =>($lastBean['rain3h']==0)? 0 : $lastBean['rain3h'],
					"clouds" =>$lastBean['clouds']
			);
			return $return; 
		}else{
			return;
		}
	}
	
	
	public function janitorDbCheck() {
		HookHandler::getInstance()->fire("onDebug", "DeviceService janitor DB Check");

		// Set indexes
		R::exec("CREATE INDEX weather_deviceId ON 'weather' ( 'deviceId' ) ;");
		R::exec("CREATE INDEX weather_time ON 'weather' ( 'time' ) ;");
	}
	
	private function toBean($object, $bObject) {
		$bObject->deviceId = $object->deviceId;
		$bObject->time = $object->time;
		$bObject->temp = $object->temp;
		$bObject->temp_min = $object->temp_min;
		$bObject->temp_max = $object->temp_max;
		$bObject->pressure = $object->pressure;
		$bObject->humidity = $object->humidity;
		$bObject->conditionId = $object->conditionId;
		$bObject->rain1h = $object->rain1h;
		$bObject->rain3h = $object->rain3h;
		$bObject->clouds = $object->clouds;
		$bObject->wind_speed = $object->wind_speed;
		$bObject->wind_direction = $object->wind_direction;
		return $bObject;
	}
	
	private function toObject($bObject) {
		$object = new Weather();
		if (isset($bObject)) {
			$object->id = $bObject->id;
			$object->deviceId = $bObject->deviceId;
			$object->time = $bObject->time;
			$object->temp = $bObject->temp;
			$object->temp_min = $bObject->temp_min;
			$object->temp_max = $bObject->temp_max;
			$object->pressure = $bObject->pressure;
			$object->humidity = $bObject->humidity;
			$object->conditionId = $bObject->conditionId;
			$object->rain1h = $bObject->rain1h;
			$object->rain3h = $bObject->rain3h;
			$object->clouds = $bObject->clouds;
			$object->wind_speed = $bObject->wind_speed;
			$object->wind_direction = $bObject->wind_direction;
		}
		return $object;
	}
}
?>