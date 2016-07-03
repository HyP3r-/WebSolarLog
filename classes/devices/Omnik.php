<?php
Class Omnik implements DeviceApi {

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
    		return ""; // Not supported
        }

    }

    public function getData() {
        if ($this->DEBUG) {
            // Id,Temp,VPV1,VPV2,VPV3,IPV1,IPV2,IPV3,IAC1,IAC2,IAC3,VAC1,VAC2,VAC3,FAC1,PAC1,FAC2,PAC2,FAC3,PAC3,ETODAY,ETOTAL,HTOTAL
            // NLDN3020137X5092,35.9,170.9,161.7,-1,0.6,0.6,-1,0.8,-1,-1,238.1,-1,-1,50.03,206,-1,-1,-1,-1,15.25,2065.0,2964
            return "NLDN3020137X5092,35.9,170.9,161.7,-1,0.6,0.6,-1,0.8,-1,-1,238.1,-1,-1,50.03,206,-1,-1,-1,-1,15.25,2065.0,2964";
        } else {
            return trim($this->execute('--wsl'));
        }
    }
    
    public function getLiveData() {
    	$data = $this->getData();
        $dataLines = explode("\n", $data);
        return OmnikConverter::toLive($dataLines[count($dataLines) - 1]);
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
    	$data = $this->execute('');
        if ($data) {
    		$result = true;
    	}
    	
    	return array("result"=>$result, "testData"=>$data);
    }

    private function execute($options) {
    	$cmd = "";
    	if ($this->useCommunication === true) {
            $cmd = $this->communication->uri . ' ' . $options . ' ' . $this->communication->optional;
        } else {
    		// Old way not supported
        }
        // remove the slashes from the line below to see the command line in the log/wsl.log
        // HookHandler::getInstance()->fire("onInfo", "Running command: " . $cmd);
        return shell_exec($cmd);
    }

}
?>