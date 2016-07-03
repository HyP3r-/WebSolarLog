<?php
class CommunicationRest {
	private $communicationService;
	private $deviceService;
	
	/**
	 * Constructor
	 */
	function __construct() {
		$this->communicationService = new CommunicationService();
		$this->deviceService = new DeviceService();
	}
	
	/**
	 * Destructor
	 */
	function __destruct() {
		$this->communicationService = null;
	}

	/**
	 * Rest functions 
	 */
	public function GET($request, $options) {
		$id = -1;
		if (count($options) > 0) {
			$id = (trim($options[0]) != "") ? strtolower($options[0]) : $type;
		}
		if ($id > 0) {
			return $this->communicationService->load($id);
		}
		return $this->communicationService->getList();
	}
	
	public function POST($request, $options) {
		if (!Session::isLogin()) {
			// TODO :: Martin :: Need to fix this!
			// throw new AuthenticationException("Not enough rights");
		}
		
		// Try to fill the object based on its public properties
		$postCommunication = new Communication();
		$publicProperties = get_object_vars($postCommunication);
		foreach ($publicProperties as $key=>$value) {
			if (isset($_POST[$key])) {
				$postCommunication->$key = $_POST[$key];
			}
		}
		
		return $this->communicationService->save($postCommunication);
	}
	
	/**
	 * Returns devices with just a few fields
	 * @param unknown $options
	 * @return multitype:multitype:NULL
	 */
	public function getShortList($options) {
		$result = array();
		foreach (Session::getConfig()->devices as $device) {
			$result[] = array("id"=>$device->id, "name"=>$device->name,"type"=>$device->type);
		}
		return $result;
	}
	
	/**
	 * 
	 * @param unknown $request
	 * @param unknown $options
	 */
	public function DELETE($request, $options){
		$communicationId = $options[0];
		$checkCommunicationUsed = $this->deviceService->checkCommunicationUsed($communicationId);

		if(count($checkCommunicationUsed)==0){
			return array("result"=>$this->communicationService->delete($communicationId),"linked"=>false);
		}else{
			return array("result"=>false,"linked"=>true);
		}
	}
	
	
	public function getStartTest($request) {
		$communicationId = Common::getValue("communicationId", -1);
		$deviceId = Common::getValue("deviceId", -1);
		
		if ($communicationId == -1 && $deviceId == -1) {
			if (count($request) == 3) {
				$deviceId = $request[1];
				$communicationId = $request[2];
			}
		}		

		/*
		 * Set device testMode "on"
		 */
		$deviceService = new DeviceService();
		$deviceService->setTestMode($deviceId,true);
		
		
		$item = new QueueItem(time(), "DeviceHandler.handleTest", "deviceId=".$deviceId."|"."communicationId=".$communicationId, false, 0, true);
		QueueServer::addItemToDatabase($item);
		
		return array("results"=>"added", "communicationId"=>$communicationId, "deviceId"=>$deviceId);
	}
}
?>