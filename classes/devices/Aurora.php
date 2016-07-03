<?php
Class Aurora implements DeviceApi {
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
        $this->useCommunication = false;
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
    		return $this->execute('-A -Y 10');
    	}

    }

    public function getData() {
        if ($this->DEBUG) {
            //return $this->execute('-b -c -T ' . $this->COMOPTION . ' -d0 -e 2>'. Util::getErrorFile($this->INVTNUM));
            return date("Ymd")."-11:11:11 233.188904 6.021501 1404.147217 234.981598 5.776632 1357.402222 242.095657 10.767704 2585.816406 59.966419 93.636436 68.472496 41.846001 3.230 8441.378 0.000 8384.237 12519.938 14584.0 84 236.659 OK";
        } else {
            return trim($this->execute('-c -T -d0 -e'));
        }
    }
    
    public function getLiveData() {
    	$data = $this->getData();
    	return AuroraConverter::toLive($data);
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
    	$result = $this->execute('-k366 -Y100'); // -K10 is not yet supported by aurora
        
        if ($result) {
        	HookHandler::getInstance()->fire("onDebug", "getHistoryData :: start processing the result");
        	$deviceHistoryList = array();
        	$lines = explode("\n", $result);
        	foreach ($lines as $line) {
        		$deviceHistory = AuroraConverter::toDeviceHistory($line);
        		if ($deviceHistory != null) {
        			$deviceHistory->amount = $deviceHistory->amount * 10; // Remove this line when -K10 is supported
        			$deviceHistoryList[] = $deviceHistory;
        		}
        	}
        	return $deviceHistoryList;
        } else {
        	if (Session::getConfig()->debugmode) {
        		HookHandler::getInstance()->fire("onDebug", "getHistoryData :: nothing returned by inverter result=" + $result);
        	}
        }
        
        return null;
    }

    public function syncTime() {
        return $this->execute('-L');
    }
    
    public function doCommunicationTest() {
    	$result = false;
    	$data = $this->execute('-se -Y10');
    	if ($data) {
    		$result = true;
    	}
    	 
    	return array("result"=>$result, "testData"=>$data);
    }

    private function execute($options) {
        $cmd = "";
        if ($this->useCommunication === true) {
        	// -Y5 -l3 -M3
        	$cmd = $this->communication->uri . ' -a' . $this->device->comAddress . ' ' . $this->communication->optional . ' ' . $options . ' ' . $this->communication->port;	
        } else {
	    	$cmd = $this->PATH . ' -a' . $this->ADR . ' ' . $options;
        }
        
        $proc=proc_open($cmd,
        		array(
        				array("pipe","r"),
        				array("pipe","w"),
        				array("pipe","w")
        		),
        		$pipes);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        
        proc_close($proc);
        return trim($stdout);
    }
}
?>