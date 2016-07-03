<?php
Class SoladinSolget implements DeviceApi {
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
            echo("GetAlarms SoladinSolget\n");
    		return $this->execute('-A -Y 10');
    	}

    }

    public function getData() {
        if ($this->DEBUG) {
            //return $this->execute('-b -c -T ' . $this->COMOPTION . ' -d0 -e 2>'. Util::getErrorFile($this->INVTNUM));
            return "55,9 0,24 49,97 231 10 24 74,5 813,87 382,51 16481:9 3,87 1,81 16481:9 ERROR description";
        } else {
            //echo("GetData SoladinSolget\n");
        	return trim($this->execute());
        }
    }
    
    public function getLiveData() {
    	$data = $this->getData();
        //echo("GetLiveData SoladinSolget\n");
    	return SoladinSolgetConverter::toLive($data);
    }

    public function getInfo() {
        if ($this->DEBUG) {
            return "PowerOne XXXXXX.XXXXXXXX";
        } else {
            return "SoladinSolget";
            //echo("GetInfo SoladinSolget\n");
        //	return $this->execute('-p -n -f -g -m -v -Y 10');
        }
    }

    public function getHistoryData() {
    	// Try to retrieve the data of the last 366 days
        echo("GetHistoryData SoladinSolget\n");
        return "No History Data"; 
    }

    public function syncTime() {
        //echo("syncTime SoladinSolget\n");
    	//return $this->execute('-L');
        return null;
    }
    
    public function doCommunicationTest() {
		$result = false;
    	$data = $this->getData();
    	if ($data) {
    		$result = true;
    	}
    	return array("result"=>$result, "testData"=>$data);
    }

    private function execute($options) {
        $cmd = "";
        if ($this->useCommunication === true) {
        	$cmd = $this->communication->uri . ' ' . $this->device->comAddress . ' ' . $this->communication->optional . ' ' . $options . ' ' . $this->communication->port;
        } else {
        	$cmd = $this->PATH . ' ' . $this->ADR . ' ' . $options;
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