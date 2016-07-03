<?php
require "php_serial.class.php";

/**
 * Script to check
 * @author Martin Diphoorn
 */

// Set the port the agent is listening
$listen_host = 0; // 0 = binding to all ip's
$listen_port = 9090;
$usb_port = (count($argv) > 1) ? $argv[1] : "" ;

abstract class AbstractAgent {
	private $port;
	private $host;
	
	private $socket;
	
	abstract protected function getData($ip);
	
	public function startAgent($host, $port) {
		$this->host = $host;
		$this->port = $port;
		
		// Setup the port and start listen
		$this->initializeAgent();
	}
	
	public function isChanged() {
		return socket_select($read = array($this->socket), $write = NULL, $except = NULL, 0) > 0;
	}
	
	public function handleRequest() {
		// Accept the new socket
		$newsocket = socket_accept($this->socket);
		
		socket_getpeername($newsocket, $ip);
		socket_write($newsocket, $this->getData($ip));
		socket_close($newsocket);
	}
	
	private function initializeAgent() {
		// create a streaming socket, of type TCP/IP
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		
		// set the option to reuse the port
		$success = socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
		
		// "bind" the socket to the address to "localhost", on port $port
		// so this means that all connections on this port are now our resposibility to send/recv data, disconnect, etc..
		$success = socket_bind($this->socket, $this->host, $this->port);
		
		// start listen for connections
		socket_listen($this->socket);
	}
}

class SmartMeterAgent extends AbstractAgent {
	private $serialPort;
	private $serial;
	private $max_script_time = 20; // The application is only allowed to run for 20 seconds
	
	public function start($serialPort) {
		$this->serialPort = $serialPort;
		$this->initializeSerialPort();
		$this->serial->deviceOpen();
	}
	
	private function initializeSerialPort() {
		$this->serial = new phpSerial;
		
		$this->serial->deviceSet($this->serialPort);
		
		$this->serial->confBaudRate(9600);
		$this->serial->confParity("even");
		$this->serial->confCharacterLength(7);
		$this->serial->confStopBits(1);
		$this->serial->confFlowControl("none");
		
		// switch to raw mode
		$this->serial->_exec("stty -F " . $this->serial->_device . " raw istrip");
	}

	function getData($ip) {
		$read = "";
		$isStart = false;
		$startTime = time();
		while(true) {
			if (!$isStart) {
				$read = "";
				sleep(1); // Give the cpu a break
			}
			$read .= $this->serial->readPort();
			if (substr( $read, 0, 1 ) === "/") {
				$isStart = true;
			}
			if ($isStart && strstr($read, '!')) {
				$isStart = false;
				return $read;
			}
			if(time() - $startTime > $this->max_script_time) {
				exit("Error: Max script time reached (" . $this->max_script_time . ")");
			}
		}
	}
}

// Check if an usb port is given
if ($usb_port == "") {
	exit("Please use: php wslP1.php <device>");
}

// Start the agent
$smartMeter = new SmartMeterAgent();
$smartMeter->start($usb_port);

// Get data and exit
echo($smartMeter->getData(""));
?>