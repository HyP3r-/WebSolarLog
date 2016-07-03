<?php
class Live {
	public $id;
	public $INV;
	public $deviceId;
	public $I1V;
	public $I1A;
	public $I1P;
	public $I1Ratio;
	
	public $I2V;
	public $I2A;
	public $I2P;
	public $I2Ratio;
	
	public $I3V;
	public $I3A;
	public $I3P;
	public $I3Ratio;
	
	public $GV;
	public $GA;
	public $GP;
	//
	public $GV2;
	public $GA2;
	public $GP2;
	//
	public $GV3;
	public $GA3;
	public $GP3;
	
	public $SDTE;
	public $time;
	public $FRQ;
	public $EFF;
	public $INVT;
	public $BOOT;
	public $KWHT;
	public $IP;
	public $ACP;

	/**
	 * below are transient variables
	 */
	public $status;
	public $name;
	public $trendImage; // Untranslated version off trend for the image selection
	public $trend;
	public $avgPower;
	public $type;
	
	/**
	 * Convert the live object to an history object
	 * @return History
	 */
	public function toHistory() {
		$history = new History();
		$history->INV = $this->INV;
		$history->deviceId = $this->deviceId;
		$history->SDTE = $this->SDTE;
		$history->time = $this->time;
		
		$history->I1V = $this->I1V;
		$history->I1A = $this->I1A;
		$history->I1P = $this->I1P;
		$history->I1Ratio = $this->I1Ratio;
		
		$history->I2V = $this->I2V;
		$history->I2A = $this->I2A;
		$history->I2P = $this->I2P;
		$history->I2Ratio = $this->I2Ratio;
		
		$history->I3V = $this->I3V;
		$history->I3A = $this->I3A;
		$history->I3P = $this->I3P;
		$history->I3Ratio = $this->I3Ratio;
		
		$history->GV = $this->GV;
		$history->GA = $this->GA;
		$history->GP = $this->GP;

		$history->GV2 = $this->GV2;
		$history->GA2 = $this->GA2;
		$history->GP2 = $this->GP2;

		$history->GV3 = $this->GV3;
		$history->GA3 = $this->GA3;
		$history->GP3 = $this->GP3;
		
		$history->ACP = $this->ACP;
		$history->IP  = $this->IP;
		
		$history->FRQ = $this->FRQ;
		$history->EFF = $this->EFF;
		$history->INVT = $this->INVT;
		$history->BOOT = $this->BOOT;
		$history->KWHT = $this->KWHT;
		$history->pvoutput = false;
		$history->pvoutputErrorMessage = 0;
		$history->pvoutputSend = 0;
		
		// Calculate the day number
		$history->dayNum = date("z", $history->time) + 1;
		
		return $history;
	}
}
?>