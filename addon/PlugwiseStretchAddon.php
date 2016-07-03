<?php
class PlugwiseStretchAddon {
	private $stretchID;
	private $stretchIP;
	
	function __construct() {
		// Initialize objects
		$this->config = Session::getConfig();
		$this->stretchID = $this->config->plugwiseStrech20ID;
		$this->stretchIP = $this->config->plugwiseStrech20IP;
	}
	
	public function onJob(){
		if($this->stretchID && $this->stretchIP){
			if (filter_var($this->stretchIP, FILTER_VALIDATE_IP)) {
				$this->getPlugsWatts();
			}	
		}
	}
	
	public function getAllPlugwisePlugs() {
		$live = new LiveService();
		$smartMeterLive = new LiveSmartMeterService();
		
		$beans =  R::find('plugwise_plugs');
		
		$data['plugs'] = $beans;
		$plugsUsage = 0;
		foreach($beans as $bean){
			$plugsUsage += (float)$bean['currentPowerUsage'];
		}
		
		foreach ($this->config->devices as $device){
		if($device->type=="metering"){
				$meter= $smartMeterLive->getLiveByDevice($device);
			}
			if($device->type=="production"){
				$production= $live->getLiveByDevice($device);
			}
		}
		$data['plugsUsage'] = $plugsUsage;
		$data['live']['liveUsageW'] =  ((int)$meter->liveUsage > 0) ? (int)$meter->liveUsage : (int)0;
		$data['live']['gridV'] =  ((int)$production->GV > 0) ? (int)$production->GV : (int)0;
		$data['live']['GA'] = round((float)($data['live']['liveUsageW'] / $data['live']['gridV']),2);
		$data['live']['GAlimit'] = (int)20; 
		return $data;
	}
	

	//http://www.pvoutput.org/help.html#api-getteam
	//http://pvoutput.org/service/r2/getsystem.jsp?sid=7856&key=b26114a4b573381947b837c12b5d4f3155d1d08a&teams=1&donations=1&tariffs=1
	/**
	 * get System With Teams From PVoutput
	 * @param unknown $device
	 * @return NULL
	 */
	public function SavePlugwisePlug(PlugwisePlug $plug){
				$firstInput = false;
				$bean =  R::findOne('plugwise_plugs',
					' applianceID = :applianceID ',
					array(':applianceID'=>$plug->applianceID)
				);
				
				if (!$bean){
					$bean = R::dispense('plugwise_plugs');
					$firstInput = true;
				}
				if($plug->applianceID){
					($plug->applianceID!='') ? $bean->applianceID = $plug->applianceID : $bean->applianceID = $bean->applianceID;
					
					($firstInput==true && $plug->name!='') ? $bean->name = $plug->name : $bean->name = $bean->name;
					
					($plug->updateName==true) ?  $bean->name = $plug->name : $bean->name = $bean->name;

					($plug->type!='') ? $bean->type = $plug->type : $bean->type = $bean->type;
					($plug->hardwareVersion!='') ? $bean->hardwareVersion = $plug->hardwareVersion : $bean->hardwareVersion = $bean->hardwareVersion;
					($plug->firmwareVersion!='') ? $bean->firmwareVersion = $plug->firmwareVersion : $bean->firmwareVersion = $bean->firmwareVersion;
					($plug->createdDate!='') ? $bean->createdDate = $plug->createdDate : $bean->createdDate = $bean->createdDate ;
					($plug->modifiedDate!='') ? $bean->modifiedDate = $plug->modifiedDate : $bean->modifiedDate = $bean->modifiedDate;
					($plug->lastSeenDate!='') ? $bean->lastSeenDate = $plug->lastSeenDate : $bean->lastSeenDate = $bean->lastSeenDate;
					($plug->powerState !='') ? $bean->powerState = $plug->powerState : $bean->powerState = $bean->powerState;
					($plug->currentPowerUsage !='') ? $bean->currentPowerUsage = $plug->currentPowerUsage : $bean->currentPowerUsage = $bean->currentPowerUsage ;
					($plug->lastKnownMeasurementDate !='') ? $bean->lastKnownMeasurementDate = $plug->lastKnownMeasurementDate : $bean->lastKnownMeasurementDate = $bean->lastKnownMeasurementDate;
					($plug->macAddress !='') ? $bean->macAddress = $plug->macAddress : $bean->macAddress = $bean->macAddress;
	
					//Store the bean
					$id = R::store($bean);
				}
	}
	
	public function switchPowerState(PlugwisePlug $plug){
		$post = array('/minirest/appliances;id='.$plug->applianceID.'/power_state='.$plug->powerState);
		
		$return = $this->PlugwiseStretchCurl("http://".$this->stretchIP."/minirest/appliances;id=".$plug->applianceID. "/power_state=".$plug->powerState,$post);
		if($return){
			$this->SavePlugwisePlug($plug);
		}
	}

	public function syncPlugsWithDB(){
		try{
			$xml = $this->PlugwiseStretchCurl("http://".$this->stretchIP."/minirest/modules");
			if ($xml['content']!='' && $xml['curl_info']['content_type']=='text/xml') {
				//simplexml_load_string($fp);
				$xml =  simplexml_load_string($xml['content']);
				foreach($xml->module as $module){
					$plug = new PlugwisePlug();
					$plug->applianceID = (string)$module->appliance['id'];
					$plug->name = (string)$module->name;
					$plug->type = (string)$module->type;
					$plug->hardwareVersion = (string)$module->hardware_version;
					$plug->firmwareVersion = (string)$module->firmware_version;
					$plug->createdDate = (string)$module->created_date;
					$plug->modifiedDate = (string)$module->modified_date;
					$plug->lastSeenDate = (string)$module->last_seen_date;
					$plug->powerState = (string)$module->power_state;
					$plug->currentPowerUsage = (string)$module->current_power_usage;
					$plug->lastKnownMeasurementDate = (string)$module->last_known_measurement_date;
					$plug->macAddress = (string)$module->mac_address;
					//exit();
					// save plug
					$this->SavePlugwisePlug($plug);
				}
			}
		}catch( Exception $e ){
			echo $e->getMessage	();
		}
	}
	

	public function getPlugsWatts(){
		// check if there is a valid Stretch-IP
		if (filter_var($this->stretchIP, FILTER_VALIDATE_IP)) {
			try{
				$xml = $this->PlugwiseStretchCurl("http://".$this->stretchIP."/minirest/modules");
				if ($xml['content']!='' && $xml['curl_info']['content_type']=='text/xml') {
					$xml =  simplexml_load_string($xml['content']);
					foreach($xml->module as $module){
						$plug = new PlugwisePlug();
						$plug->applianceID = (string)$module->appliance['id'];
						$plug->currentPowerUsage = (string)$module->current_power_usage;
						$plug->name = (string)$module->name;
						// save plug
						$this->SavePlugwisePlug($plug);
					}
				}
			}catch( Exception $e ){
				echo $e->getMessage	();
			}
		}else{
			return false;
		}
	}

	

	function PlugwiseStretchCurl($URL,$post=null)
	{
		$c = curl_init();
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_URL, $URL);
		curl_setopt($c, CURLOPT_USERPWD, 'stretch:'.$this->stretchID);
		if($post){
			curl_setopt($c,CURLOPT_POST, 1);
			curl_setopt($c,CURLOPT_POSTFIELDS, $post[0]);
		}
		curl_setopt($c, CURLOPT_HTTPHEADER, array(
		'X-Requested-With: XMLHttpRequest',
		'Referer: http://'.$this->stretchIP.'/html_interface/'
		));
		curl_setopt($c, CURLOPT_VERBOSE, true);
		
		//$verbose = fopen('/home/marco/curl.txt', 'wb+');
		//curl_setopt($c, CURLOPT_STDERR, $verbose);
		
		$contents = curl_exec($c);
		$info = curl_getinfo($c);
		curl_close($c);
		if ($contents) return array("content"=>$contents, "curl_info"=>$info);
		else return FALSE;
	}
}