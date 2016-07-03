<?php
Class SMASpotWSL implements DeviceApi {
    private $ADR;
    private $DEBUG;
    private $PATH;

    private $device;
    private $communication;
    private $useCommunication = false;
    
    function __construct($path, $address, $debug) {
        $this->ADR = $address;
        $this->DEBUG = $debug;
        $this->PATH = $path;
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
    	return 0; // Try to detect, as it will die when offline
    }
    
    public function getAlarms() {
    	if ($this->DEBUG) {
    		return "W2223424".rand(0,9);
    	} else {
    		return $this->execute('');
    	}

    }

    public function getData() {
        if ($this->DEBUG) {
        	//var_dump(explode(";","WSL_START;datetime:01/05/2013 22:05:47;Pdc1:123,000;Pdc2:234,000;Idc1:1,2300;Idc2:1,2300;Udc1:345,000;Udc2:456,000;Pac1:375,000;Pac2:0,000;Pac3:0,000;Iac1:3,000;Iac2:0,000;Iac3:0,000;Uac1:233,000;Uac2:0,000;Uac3:0,000;PdcTot:357,000;PacTot:340,000;Efficiency:95,600;EToday:21,738;ETotal:9039,226;Frequency:49,990;OperatingTime:9904,874;FeedInTime:9590,518;BT_Signal:64,314;Condition:OK;GridRelay:?;WSL_END"));
            return "WSL_START;29/05/2013 13:54:08;321,000;0,000;1,142;0,000;281,340;0,000;266,000;0,000;0,000;1,148;0,000;0,000;232,410;0,000;0,000;321,000;266,000;82,866;1,577;633,778;49,970;1245,957;1154,092;65,098;OK;Closed;WSL_END";
            //      WSL_START;DateTime;           Pdc1;   Pdc2;   Idc1;  Idc2;  Udc1;   Udc2;   Pac1;   Pac2; Pac3; Iac1; Iac2; Iac3; Uac1;   Uac2; Uac3; PdcTot; PacTot; Efficiency;EToday;ETotal;Frequency;OperatingTime;FeedInTime;BT_Signal;Condition;GridRelay;WSL_END
        } else {
			 /*
 SMAspot V2.0.6-RC2
Yet another tool to read power production of SMA solar inverters
(c) 2012-2013, SBF (http://code.google.com/p/sma-spot)

Commandline Args: -?
SMAspot V2.0.6-RC2
Yet another tool to read power production of SMA solar inverters
(c) 2012-2013, SBF (http://code.google.com/p/sma-spot)

SMAspot [-scan] [-d#] [-v#] [-ad#] [-am#] [-cfgX.Y] [-u] [-finq] [-q] [-nocsv]
 -scan   Scan for bluetooth enabled SMA inverters.
 -d#     Set debug level: 0-5 (0=none, default=2)
 -v#     Set verbose output level: 0-5 (0=none, default=2)
 -ad#    Set #days for archived daydata: 0-90
         0=disabled, 1=today (default), ...
 -am#    Set #months for archived monthdata: 0-60
         0=disabled, 1=current month (default), ...
 -cfgX.Y Set alternative config file to X.Y (multiple inverters)
 -u      Upload to online monitoring system (see config file)
 -finq   Force Inquiry (Inquire inverter also during the night)
 -q      Quiet (No output)
 -nocsv  Disables CSV export (Overrules CSV_Export in config)
			 
			  */
        	
			 $result =  trim($this->execute('-finq -q -wsl -nocsv'));
        	//HookHandler::getInstance()->fire("onDebuggingExtreem", "Device::SMAspotWSL execute:".print_r($result,true));
        	return $result;
        }
    }
    
    public function getLiveData() {
    	$data = $this->getData();
    	return SMASpotWSLConverter::toLive($data);
    }

    public function getInfo() {
        if ($this->DEBUG) {
            return "SMA XXXXXX.XXXXXXXX";
        } else {
           return $this->execute('-i');
        }
    }

    public function getHistoryData() {
        //return $this->execute('-k');
        // not supported
    }

    public function syncTime() {
        //return $this->execute('-L');
        // not supported
    }
    
    public function doCommunicationTest() {
    	$result = false;
    	$data = $this->execute('-finq -q -wsl -nocsv');
    	if ($data) {
    		$result = true;
    	}
    	return array("result"=>$result, "testData"=>$data);
    }

    private function execute($options) {
    	// TODO :: Convert to the new communication
    	
    	// this make multi inverter possible for SMAspot users
    	// if we have a "adress"/configfile, then we add "-cfg/adress/to/config-file" to the options.
    	
    	// "dirty fix"
    	// TODO
    	/*
    	 * onInverterError - 
    	 * SMA SB2000HF30 - SMAspot V2.0.6<br /> 
    	 * Yet another tool to read power production of SMA solar inverters<br /> 
    	 * (c) 2012-2013, SBF (http://code.google.com/p/sma-spot)<br /> <br /> 
    	 * Commandline Args: -cfg2<br /> Error! Could not open file 2 
    	 */ 
    	$SMAspotDevices = 0;
    	$config = Session::getConfig();
    	// check if we have already a SMA-BT-WSL
    	foreach($config->allDevices as $device){
    		if($device->deviceApi == "SMA-BT-WSL"){
    			$SMAspotDevices++;
    		}
    	}

    	$multioptions="";
   		if($SMAspotDevices > 0){
	    	if ($this->useCommunication === true) {
    			$multioptions = ($this->device->comAddress) ? ' -cfg'. $this->device->comAddress : "";
	    	} else {
		    	$multioptions = ($this->ADR) ? ' -cfg'.$this->ADR : "";
	    	}
   		}
    	
    	
    	$cmd = "";
    	if ($this->useCommunication === true) {
    		$cmd = $this->communication->uri . ' ' . $options . ' ' . $multioptions . ' ' . $this->communication->port .  ' ' . $this->communication->optional;
    	} else {
    		$cmd = $this->PATH . ' ' .$options . ' ' . $multioptions;
    	}
    	
    	try{
    		$result = shell_exec($cmd);
    	} catch (Exception $e) {
			HookHandler::getInstance()->fire("onDebug", "Device::SMAspotWSL generated:".$e->getMessage());
    	}
        return $result;
    }

}
?>