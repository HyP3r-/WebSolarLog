<?php
Class Diehl implements DeviceApi {
    private $ADR;
    private $DEBUG;
    private $PATH;

    private static $curlCh;
    
    private $device;
    private $communication;
    private $useCommunication = false;
    
    function setCommunication(Communication $communication, Device $device) {
    	$this->communication = $communication;
    	$this->device = $device;
    	$this->useCommunication = true;
    }
    
    public static function getCurlCh() { 	
    	if (gettype(self::$curlCh) != 'resource') {
    		//HookHandler::getInstance()->fire("onDebug", "Diehl :: Creating curl resource");
    		
    		$cookieFile = Common::getRootPath() . "/log/diehl_cookies.txt";
    		
    		self::$curlCh = curl_init();
    		curl_setopt(self::$curlCh, CURLOPT_HEADER, 0);
    		curl_setopt(self::$curlCh, CURLOPT_CONNECTTIMEOUT, 30);
    		curl_setopt(self::$curlCh, CURLOPT_TIMEOUT, 60);
    		curl_setopt(self::$curlCh, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
    		curl_setopt(self::$curlCh, CURLOPT_RETURNTRANSFER, 1);
    		curl_setopt(self::$curlCh, CURLOPT_SSL_VERIFYPEER, 0);
    		curl_setopt(self::$curlCh, CURLOPT_FOLLOWLOCATION, 1);
    		curl_setopt(self::$curlCh, CURLOPT_COOKIEJAR, $cookieFile);
    		curl_setopt(self::$curlCh, CURLOPT_COOKIEFILE, $cookieFile);
    	}
    	
    	return self::$curlCh;
    }
    
    /**
     * 
     * Alot of JSON requests and Responses from the Diehl inverter.
     * 
     **/
    
    
    /**
		***************
		Page:
			Statistics
		Form Data:
			{"jsonrpc":"2.0","method":"GetPowerLog","params":[1,17,"2013-02-14 00:00:00","kWh","1h",168],"id":0}
		response:
			{
			"jsonrpc":	"2.0",
			"result":	{
				"TimeDate":	"2013-02-14 00:00:00",
				"Unit":	"kWh",
				"Resolution":	"1h",
				"Values":	[0, 0, 0, 0, 0, 0, 0, 0, 0, 0.001000, 0.039000, 0.042000, 0.045000, 0.046000, 0.041000, 0.044000, 0.015000, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0.029000, 0.035000, 0.087000, 0.111000, 0.076000, 0.062000, 0.062000, 0.058000, 0.031000, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0.020000, 0.110000, 0.137000, 0.142000, 0.334000, 0.345000, 0.287000, 0.179000, 0.069000, 0.026000, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0.047000, 0.272000, 0.405000, 0.667000, 1.027000, 1.181000, 0.546000, 0.427000, 0.214000, 0.039000, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0.026000, 0.189000, 0.424000, 0.825000, 1.295000, 0.959000, 0.379000, 0.390000, 0.091000, 0.020000, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0.023000, 0.087000, 0.263000, 0.271000, 0.216000, 0.238000, 0.191000, 0.071000, 0.098000, 0.024000, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0.036000, 0.074000, 0.322000, 0.526000, 1.285000, 1.694000, 0.505000, 0.400000, 0.192000, 0.067000, 0.022000, 0, 0, 0, 0, 0],
				"Index":	2182,
				"Total":	17.056999,
				"Saving":	166,
				"CO2Cut":	11
				},
				"Id":	0
			}
		
		***************
		Page:
			Statistics
		Form Data:
			{"jsonrpc":"2.0","method":"GetPowerLog","params":[1,17,"2012-03-01 00:00:00","kWh","1mnd",12],"id":0}
		response:
			{
			"jsonrpc":	"2.0",
			"result":	{
				"TimeDate":	"2012-03-01 00:00:00",
				"Unit":	"kWh",
				"Resolution":	"1mnd",
				"Values":	[0, 0, 0, 0, 0, 0, 0, 0, 0, 13.898000, 17.695999, 34.641998],
				"Index":	5,
				"Total":	66.236000,
				"Saving":	660,
				"CO2Cut":	46
				},
				"Id":	0
			}
			
		
		***************
		Page:
			Statistics; auto updater?!
		Form Data:
			{"jsonrpc":"2.0","method":"GetPowerLog","params":[1,17,"2013-02-21 00:00:00","kWh","15min",34.6],"id":0}
		response:	
			{
				"jsonrpc":	"2.0",
				"result":	{
					"TimeDate":	"2013-02-21 00:00:00",
					"Unit":	"kWh",
					"Resolution":	"15min",
					"Values":	[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0.001000, 0.029000, 0.046000],
					"Index":	2183,
					"Total":	0,
					"Saving":	0,
					"CO2Cut":	0
				},
				"Id":	0
			}
		
		***************
		Page:
			Eventlog
		Form Data:
			{"jsonrpc":"2.0","method":"GetEventPage","params":[1,"2013-02-21 23:59:59",1,14],"id":0}
			***Other date
			{"jsonrpc":"2.0","method":"GetEventPage","params":[1,"2013-02-01 23:59:59",1,14],"id":0}
			***Other page 3 selected in page selector:
			{"jsonrpc":"2.0","method":"GetEventPage","params":[1,"2013-02-01 23:59:59",3,14],"id":0}
		response:	
			{
				"jsonrpc":	"2.0",
				"result":	{
					"Items":	[{
							"EventNo":	1,
							"TimeStamp":	"2013-02-20 09:23:39",
							"Desc":	"System Reset",
							"Event":	"Info:On "
						},
						********** CUT **********
						 {
							"EventNo":	14,
							"TimeStamp":	"2012-12-11 11:28:49",
							"Desc":	"System Reset",
							"Event":	"Info:On "
						}]
				},
				"Id":	0
			}
		
		***************
		Page:
			Status->Plant
		Form Data:
			{"jsonrpc":"2.0","method":"GeteNexusData","params":[{"path":"eNEXUS_0056","datatype":"INT16U"}],"id":0}
		response:	
			{
				"jsonrpc":	"2.0",
				"result":	[{
						"path":	"eNEXUS_0056",
						"value":	"1"
					}],
				"Id":	0
			}
			
			
		***************
		Page:
			Status->Plant (this is a second request on the same page)
		Form Data:
			{"jsonrpc":"2.0","method":"GeteNexusData","params":[{"path":"eNEXUS_0061[s:1,t:17]","datatype":"INT8U"},{"path":"eNEXUS_0035[s:1,t:17]","datatype":"SERIALNO"},{"path":"eNEXUS_0060[s:1]","datatype":"STRING32"},{"path":"eNEXUS_0002[s:1,t:17]","datatype":"INT8U"},{"path":"eNEXUS_0001[s:1,t:17]","datatype":"INT8U"},{"path":"eNEXUS_0010[s:1,t:17]","datatype":"INT16U"},{"path":"eNEXUS_0040[s:1,t:17,u:4]","datatype":"INT32U"},{"path":"eNEXUS_0041[s:1,t:17,u:4]","datatype":"INT32U"},{"path":"eNEXUS_0043[s:1,t:17,u:4]","datatype":"INT32U"}],"id":0}
		response:	
			{
				"jsonrpc":	"2.0",
				"result":	[{
						"path":	"eNEXUS_0061[s:1,t:17]",
						"value":	"1"
					}, {
						"path":	"eNEXUS_0035[s:1,t:17]",
						"value":	"122671110228"
					}, {
						"path":	"eNEXUS_0060[s:1]",
						"value":	"Home"
					}, {
						"path":	"eNEXUS_0002[s:1,t:17]",
						"value":	"3"
					}, {
						"path":	"eNEXUS_0001[s:1,t:17]",
						"value":	"1"
					}, {
						"path":	"eNEXUS_0010[s:1,t:17]",
						"value":	"167"
					}, {
						"path":	"eNEXUS_0040[s:1,t:17,u:4]",
						"value":	"123"
					}, {
						"path":	"eNEXUS_0041[s:1,t:17,u:4]",
						"value":	"34765"
					}, {
						"path":	"eNEXUS_0043[s:1,t:17,u:4]",
						"value":	"66359"
					}],
				"Id":	0
			}
		
		***************
		Page:
			Home
		Form Data:
			{"jsonrpc":"2.0","method":"GetPowerLog","params":[1,17,"2013-02-21 00:00:00","kWh","15min",37.93333333333333],"id":0}
		response:
			{
				"jsonrpc":	"2.0",
				"result":	{
					"TimeDate":	"2013-02-21 00:00:00",
					"Unit":	"kWh",
					"Resolution":	"15min",
					"Values":	[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0.001000, 0.029000, 0.046000, 0.078000, 0.122000, 0.161000],
					"Index":	2186,
					"Total":	0,
					"Saving":	0,
					"CO2Cut":	0
				},
				"Id":	0
			}
			
			
		***************
		Page:
			Home
		Form Data:
			{"jsonrpc":"2.0","method":"GetPowerLog","params":[1,17,"2013-02-01 00:00:00","kWh","1day",21],"id":0}
		response:
			{
				"jsonrpc":	"2.0",
				"result":	{
					"TimeDate":	"2013-02-01 00:00:00",
					"Unit":	"kWh",
					"Resolution":	"1day",
					"Values":	[0.908000, 2.482000, 1.427000, 3.093000, 2.238000, 1.013000, 2.309000, 2.953000, 0.717000, 0.028000, 0.075000, 0.137000, 0.205000, 0.049000, 0.580000, 1.755000, 4.278000, 4.483000, 1.338000, 4.574000, 0.096000],
					"Index":	76,
					"Total":	34.737999,
					"Saving":	337,
					"CO2Cut":	24
				},
				"Id":	0
			}
		
		
		Page:
			Home
		Form Data:
			{"jsonrpc":"2.0","method":"GetPowerLog","params":[1,17,"2013-01-01 00:00:00","kWh","1mnd",2],"id":0}
		response:
			{
				"jsonrpc":	"2.0",
				"result":	{
					"TimeDate":	"2013-01-01 00:00:00",
					"Unit":	"kWh",
					"Resolution":	"1mnd",
					"Values":	[17.695999, 34.737999],
					"Index":	5,
					"Total":	52.433998,
					"Saving":	523,
					"CO2Cut":	36
				},
				"Id":	0
			}
		
			
		Page:
			Home
		Form Data:
			{"jsonrpc":"2.0","method":"GeteNexusData","params":[{"path":"eNEXUS_0001[s:17,t:1]","datatype":"INT8U"},{"path":"eNEXUS_0002[s:17,t:1]","datatype":"INT8U"},{"path":"eNEXUS_0003[s:17,t:1]","datatype":"INT32U"},{"path":"eNEXUS_0004[s:17,t:1]","datatype":"INT32U"},{"path":"eNEXUS_0064[s:17,t:1]","datatype":"INT32U"},{"path":"eNEXUS_0013[s:17,t:1]","datatype":"INT32S"},{"path":"eNEXUS_0014[s:17,t:1]","datatype":"INT32S"},{"path":"eNEXUS_0015[s:17,t:1]","datatype":"INT32S"},{"path":"eNEXUS_0015[s:17,t:1]","datatype":"INT32S"},{"path":"eNEXUS_0016","datatype":"Date"},{"path":"eNEXUS_0017","datatype":"Time"},{"path":"eNEXUS_0018","datatype":"STRING32"}],"id":0}
		response:
			{
				"jsonrpc":	"2.0",
				"result":	[{
						"path":	"eNEXUS_0001[s:17,t:1]",
						"value":	"1"
					}, {
						"path":	"eNEXUS_0002[s:17,t:1]",
						"value":	"3"
					}, {
						"path":	"eNEXUS_0003[s:17,t:1]",
						"value":	"0"
					}, {
						"path":	"eNEXUS_0004[s:17,t:1]",
						"value":	"0"
					}, {
						"path":	"eNEXUS_0064[s:17,t:1]",
						"value":	"182"
					}, {
						"path":	"eNEXUS_0013[s:17,t:1]",
						"value":	"129"
					}, {
						"path":	"eNEXUS_0014[s:17,t:1]",
						"value":	"34771"
					}, {
						"path":	"eNEXUS_0015[s:17,t:1]",
						"value":	"52467"
					}, {
						"path":	"eNEXUS_0015[s:17,t:1]",
						"value":	"52467"
					}, {
						"path":	"eNEXUS_0016",
						"value":	{
							"day":	21,
							"month":	2,
							"year":	2013
						}
					}, {
						"path":	"eNEXUS_0017",
						"value":	{
							"hour":	9,
							"minute":	14
						}
					}, {
						"path":	"eNEXUS_0018",
						"value":	"Almere"
					}],
				"Id":	0
			}
		
		
		Page:
			Status>Overview>Plant from dropdown
		Form Data:
			{"jsonrpc":"2.0","method":"GeteNexusData","params":[{"path":"eNEXUS_0005[s:1,t:17]","datatype":"INT16U"},{"path":"eNEXUS_0006[s:1,t:17]","datatype":"INT16U"},{"path":"eNEXUS_0007[s:1,t:17]","datatype":"INT16U"},{"path":"eNEXUS_0008[s:1,t:17]","datatype":"INT16U"},{"path":"eNEXUS_0009[s:1,t:17]","datatype":"INT16U"},{"path":"eNEXUS_0046[s:1,t:17]","datatype":"INT16U"},{"path":"eNEXUS_0012[s:1,t:17]","datatype":"INT16U"},{"path":"eNEXUS_0010[s:1,t:17]","datatype":"INT16U"},{"path":"eNEXUS_0040[s:1,t:17,n:4]","datatype":"INT32U"},{"path":"eNEXUS_0041[s:1,t:17,n:4]","datatype":"INT32U"},{"path":"eNEXUS_0042[s:1,t:17,n:4]","datatype":"INT32U"},{"path":"eNEXUS_0043[s:1,t:17,n:4]","datatype":"INT32U"},{"path":"eNEXUS_0082[s:1,t:17]","datatype":"INT16U"},{"path":"eNEXUS_0045[s:1,t:17]","datatype":"INT16S"},{"path":"eNEXUS_0011[s:1,t:17]","datatype":"INT32U"},{"path":"eNEXUS_0040[s:1,t:17,n:5]","datatype":"INT32U"},{"path":"eNEXUS_0041[s:1,t:17,n:5]","datatype":"INT32U"},{"path":"eNEXUS_0042[s:1,t:17,n:5]","datatype":"INT32U"},{"path":"eNEXUS_0043[s:1,t:17,n:5]","datatype":"INT32U"},{"path":"eNEXUS_0044[s:1,t:17]","datatype":"STRING"},{"path":"eNEXUS_0040[s:1,t:17,n:6]","datatype":"INT32U"},{"path":"eNEXUS_0041[s:1,t:17,n:6]","datatype":"INT32U"},{"path":"eNEXUS_0042[s:1,t:17,n:6]","datatype":"INT32U"},{"path":"eNEXUS_0043[s:1,t:17,n:6]","datatype":"INT32U"},{"path":"eNEXUS_0040[s:1,t:17,n:9]","datatype":"INT32U"},{"path":"eNEXUS_0041[s:1,t:17,n:9]","datatype":"INT32U"},{"path":"eNEXUS_0042[s:1,t:17,n:9]","datatype":"INT32U"},{"path":"eNEXUS_0043[s:1,t:17,n:9]","datatype":"INT32U"}],"id":0}:
		Response:
		
		
		
		
		
		
		
		
		Page:
			Home
		Form Data:
			{"jsonrpc":"2.0","method":"GeteNexusData","params":[{"path":"eNEXUS_0063[s:17,t:1]","datatype":"INT32U"},{"path":"eNEXUS_0006[s:17,t:1]","datatype":"INT16U"},{"path":"eNEXUS_0064[s:17,t:1]","datatype":"INT32U"},{"path":"eNEXUS_0065[s:17,t:1]","datatype":"INT32U"},{"path":"eNEXUS_0011[s:17,t:1]","datatype":"INT32U"}],"id":0}
		response:
			{
			"jsonrpc":	"2.0",
			"result":	[{
				"path":	"eNEXUS_0063[s:17,t:1]",
				"value":	"509"
			}, {
				"path":	"eNEXUS_0006[s:17,t:1]",
				"value":	"3373"
			}, {
				"path":	"eNEXUS_0064[s:17,t:1]",
				"value":	"182"
			}, {
				"path":	"eNEXUS_0065[s:17,t:1]",
				"value":	"172"
			}, {
				"path":	"eNEXUS_0011[s:17,t:1]",
				"value":	"na",
				"error":	"No responce"
			}],
		"Id":	0
	}
	
			
			
			
			
			
     */
    /**
     * 
     * @param unknown $path
     * @param unknown $address
     * @param unknown $debug
     */
    function __construct($path, $address, $debug) {
        $this->ADR = $address;
        $this->DEBUG = $debug;
        $this->PATH = $path;
    }
    
    /**
     * We use the getState to get the Diehl operation mode (online,offline,stanby,etc)
     * @return Device specific Convertered Mode array
     */
    public function getState(){
    	$jsonRequest = '{"jsonrpc":"2.0","method":"GeteNexusData","params":[{"path":"eNEXUS_0002[s:1,t:17]","datatype":"INT8U"}],"id":0}:';
    	$data =trim($this->execute($jsonRequest));
    	return DiehlConverter::toMode($data);
    }

    public function getAlarms() {
    	if ($this->DEBUG) {
    		return "";
    	} else {
    		// At this moment we can't process the Event response from the Diehl
    		//$jsonRequest = '{"jsonrpc":"2.0","method":"GetEventPage","params":[1,"2013-02-21 23:59:59",1,14],"id":0}:';
    		//return $this->execute($jsonRequest);
    		return "";    		
    	}
    }

    public function getData() {
        if ($this->DEBUG) {
        	// JSON return
            return '{"jsonrpc":"2.0","result":[{"path":"eNEXUS_0043[s:17,t:1]","value":"11394"}, 
            {"path":"eNEXUS_0063[s:17,t:1]","value":"394"}, {"path":"eNEXUS_0049[s:17,t:1]","value":"394"}, 
            {"path":"eNEXUS_0050[s:17,t:1]","value":"3537"}, {"path":"eNEXUS_0065[s:17,t:1]","value":"139"}, 
            {"path":"eNEXUS_0051[s:17,t:1]","value":"139"}, {"path":"eNEXUS_0052","value":"0"}, 
            {"path":"eNEXUS_0053","value":"0"}, {"path":"eNEXUS_0066[s:17,t:1]","value":"493"},
            {"path":"eNEXUS_0055[s:17,t:1]","value":"2333"},{"path":"eNEXUS_0064[s:17,t:1]","value":"115"},
            {"path":"eNEXUS_0056","value":"1"},{"path":"eNEXUS_0057","value":"1"},
            {"path":"eNEXUS_0058","value":"2000"},{"path":"eNEXUS_0066[s:17,t:1,p:1]","value":"493"},
            {"path":"eNEXUS_0066[s:17,t:1,p:2]","value":"0"}, {"path":"eNEXUS_0066[s:17,t:1,p:3]","value":"0"},
            {"path":"eNEXUS_0064[s:17,t:1,p:1]","value":"115"},{"path":"eNEXUS_0064[s:17,t:1,p:2]","value":"0"},
            {"path":"eNEXUS_0064[s:17,t:1,p:3]","value":"0"},{"path":"eNEXUS_0009[s:17,t:1,p:1]","value":"2333"},
            {"path":"eNEXUS_0009[s:17,t:1,p:2]","value":"0"},{"path":"eNEXUS_0009[s:17,t:1,p:3]","value":"0"}],"Id":0}';
        } else {
        	$jsonRequest = '{"jsonrpc":"2.0","method":"GeteNexusData","params":[
			{"path":"eNEXUS_0006[s:1,t:17]","datatype":"INT16U"},{"path":"eNEXUS_0007[s:1,t:17]","datatype":"INT16U"},
			{"path":"eNEXUS_0005[s:1,t:17]","datatype":"INT16U"},{"path":"eNEXUS_0008[s:1,t:17]","datatype":"INT16U"},
			{"path":"eNEXUS_0009[s:1,t:17]","datatype":"INT16U"},{"path":"eNEXUS_0011[s:1,t:17]","datatype":"INT32U"},
			{"path":"eNEXUS_0010[s:1,t:17]","datatype":"INT16U"},{"path":"eNEXUS_0043[s:1,t:17,n:4]","datatype":"INT32U"},
			{"path":"eNEXUS_0046[s:1,t:17]","datatype":"INT16U"},{"path":"eNEXUS_0045[s:1,t:17]","datatype":"INT16S"}],"id":0}:';
            return trim($this->execute($jsonRequest));
        }
    }


    /**
     * 
     * @return Ambigous <Live, NULL>
     */
    public function getLiveData() {
    	$data = $this->getData();
    	return DiehlConverter::toLive($data);
    }

    public function getInfo() {
        if ($this->DEBUG) {
           return "PowerOne XXXXXX.XXXXXXXX";
        } else {
           return $this->execute('-p -n -f -g -m -v -Y 10');
        }
    }

    public function getHistoryData() {
    	// Try to retrieve the data of the last 366 days
        //return $this->execute('-k 366 -Y 60');
    }

    public function syncTime() {
        return $this->execute('-L');
    }
    
    public function doCommunicationTest() {
    	$result = false;
    	$data = $this->getInfo();
    	if ($data) {
    		$result = true;
    	}
    	return array("result"=>$result, "testData"=>$data);
    }

    private function execute($options) {
    	$url = "";
    	if ($this->useCommunication === true) {
    		$url = $this->communication->uri . "/rpc/GetPowerLog";
    	} else {
    		$url = "http://".$this->ADR."/rpc/GetPowerLog";
    	}
    	
    	$ch = Diehl::getCurlCh();
		curl_setopt($ch, CURLOPT_URL, "http://".$this->ADR."/rpc/GetPowerLog");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    	if ($this->useCommunication === true) {
			curl_setopt($ch, CURLOPT_TIMEOUT, $this->communication->timeout);
    	}
		curl_setopt($ch, CURLOPT_POSTFIELDS, $options);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    'Content-Type: application/json',
		    'Content-Length: ' . strlen($options))
		);
		$output = curl_exec($ch);
		//$info = curl_getinfo($ch);
		//curl_close($ch);
		//echo $output;
        return trim($output);
    }
}
?>