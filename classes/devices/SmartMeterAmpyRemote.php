<?php
Class SmartMeterAmpyRemote implements DeviceApi {
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
    	// not supported
    }

    public function getData() {
        if ($this->DEBUG) {
            //return $this->execute('-b -c -T ' . $this->COMOPTION . ' -d0 -e 2>'. Util::getErrorFile($this->INVTNUM));
            return '/XMX5XMXABCE000024595

0-0:96.1.1(22222222222222222222222222222222)
1-0:1.8.1(01192.830*kWh)
1-0:1.8.2(00789.784*kWh)
1-0:2.8.1(00234.992*kWh)
1-0:2.8.2(00572.925*kWh)
0-0:96.14.0(0002)
1-0:1.7.0(0000.47*kW)
1-0:2.7.0(0000.00*kW)
0-0:17.0.0(999*A)
0-0:96.3.10(1)
0-0:96.13.1()
0-0:96.13.0()
0-1:96.1.0(1111111111111111111111111111111111)
0-1:24.1.0(03)
0-1:24.3.0(130116180000)(00)(60)(1)(0-1:24.2.0)(m3)
(00348.414)
0-1:24.4.0(1)
!';
        } else {
            return trim($this->execute());
        }
    }
    
    public function getLiveData() {
    	//echo "\r\ngetLiveData\r\n";
    	$data = explode("\n",$this->getData());
    	//echo "\r\ndata:".var_dump($data)."\r\n";
    	return SmartMeterAmpyRemoteConverter::toLiveSmartMeter($data);
    }

    public function getInfo() {
        // not supported
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
    	$data = $this->getData();
    	if ($data) {
    		$result = true;
    	}
    	return array("result"=>$result, "testData"=>$data);
    }

    private function execute() {
    	$server = "127.0.0.1";
    	$port = 8080;
    	$timeout = 15;
    	if ($this->useCommunication === true) {
    		$server = $this->communication->uri;
    		$port = $this->communication->port;
    		$timeout = $this->communication->timeout;
    	} else {
	    	$address = explode(":", $this->ADR);
			if (count($address) != 2) {
				$error = "Error: wrong address given " . $address;
	    		HookHandler::getInstance()->fire("onError", $error);
				return;
			}
    		
	    	$server = $address[0];
	    	$port = $address[1];
    	}
    	
    	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    	if ($socket < 0) {
    		HookHandler::getInstance()->fire("onError", "Could not create socket: " . socket_strerror(socket_last_error($socket)));
    		return;
    	}
    	if (!@socket_set_option($socket,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>$timeout, "usec"=>0))) {
    		HookHandler::getInstance()->fire("onWarning", "Could not set socket timeout: " . socket_strerror(socket_last_error($socket)));
    	}
    	if (!@socket_connect($socket, $server, $port)) {
    		HookHandler::getInstance()->fire("onError", "Could not create connection: " . socket_strerror(socket_last_error($socket)));
    		return;
    	}
    	// Ampy Code;
    	$msg = '/?!';
    	socket_write($socket, $msg, strlen($msg)); //Send data
    	sleep(1);
    	// /Ampy Code;
    	$result = socket_read($socket, '1024');
    	socket_close($socket);
    	return $result;
    }
}
?>