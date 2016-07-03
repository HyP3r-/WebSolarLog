<?php 

Class WeatherOWM implements DeviceApi {
	private $weatherUrl = "http://api.openweathermap.org/data/2.5/find?units=metrics&cnt=1&mode=json";
	private $lat;
	private $lon;
	
	private $device;
	private $communication;
	private $useCommunication = false;
	
	function __construct($lat, $lon) {
		$this->lat = $lat;
		$this->lon = $lon;
	}
	
    function setCommunication(Communication $communication, Device $device) {
    	$this->communication = $communication;
    	$this->device = $device;
    	$this->useCommunication = true;
    }
    
    /**
     * @see DeviceApi::getState()
     */
    public function getState() {
    	return 0; // TODO :: maybe check if we can connect to the uri
    }
	
	public function getAlarms() {
		// not supported
	}
	
	public function getData() {
		HookHandler::getInstance()->fire("onDebug",__METHOD__."::Lets start with gathering weather data from OWM");
		
		$url = "";
		if ($this->useCommunication === true) {
			$url = $this->communication->uri;
		} else {
			$url = $this->weatherUrl;
		}
		
		$latlng = "&lat=" . $this->lat . "&lon=" . $this->lon;
		$result = json_decode($this->call($url . $latlng));
		// lets check if we have received data
		if(count($result->list)>0){
			
			$weather = new Weather();
			$weather->deviceId = -1;
			$weather->time = time();
			$weather->temp = $result->list[0]->main->temp - 273.15;
			$weather->temp_min = $result->list[0]->main->temp_min - 273.15;
			$weather->temp_max = $result->list[0]->main->temp_max - 273.15;
			$weather->pressure = $result->list[0]->main->pressure;
			$weather->humidity = $result->list[0]->main->humidity;
			$weather->conditionId = $result->list[0]->weather[0]->id;
			if(isset($result->list[0]->rain)){
				if(isset($result->list[0]->rain->{'1h'})){
					$weather->rain1h = $result->list[0]->rain->{'1h'};
				}
				if(isset($result->list[0]->rain->{'3h'})){
					$weather->rain3h = $result->list[0]->rain->{'3h'};
				}
			}
			if(isset($result->list[0]->clouds)){
				$weather->clouds = $result->list[0]->clouds->all;
			}
			$weather->wind_speed = $result->list[0]->wind->speed;
			$weather->wind_direction = $result->list[0]->wind->deg;
			
			if ($weather->temp == -273.15) {
				// probably invalid response, return null
				return null;
			}
			
			return $weather;
		}else{
			HookHandler::getInstance()->fire("onDebug",__METHOD__."::Looks like we didn't receive useful weather data::".print_r($result,true));
			return null;
		}
	}
	
	public function getLiveData() {
		return $this->getData();
	}
	
	public function getInfo() {
		return "www.openweathermap.org";	
	}
	
	public function syncTime() {
		// not supported
	}
	
	public function doCommunicationTest() {
    	$result = false;
    	$data = $this->getData();
    	//check to see if we have a integer value for data->time and its bigger then 1

    	if (is_array($data->list)) {
    		$result = true;
    	}
    	
    	return array("result"=>$result, "testData"=>print_r($data, true));
    }
	
	public function getHistoryData() {
		// not supported
	}
	
	// Communicate
	private function call($url) {
		try {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$result = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			if ($httpCode == "200") {
				return $result;
			}
		} catch (Exception $e) {
			HookHandler::getInstance()->fire("onError", $e->getMessage());
		}
		return false;
	}
}
?>