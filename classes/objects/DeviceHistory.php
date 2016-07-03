<?php
class DeviceHistory {
	public $id;
	public $deviceId;
    public $time;
    public $amount;
    public $processed;
    
   	function __construct () {
   		$this->id = -1;
   		$this->deviceId = -1;
   		$this->amount = 0;
   		$this->processed = false;
   	}
}
?>