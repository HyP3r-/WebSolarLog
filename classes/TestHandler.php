<?php
class TestHandler {
	private $communicationService;
	
	public function __construct() {
		$this->communicationService = new CommunicationService();
	}
	
	public function testCommunication($args) {
		$item = $args[0];
		$id = $args[1];

		$communication = $this->communicationService->load($id);
		$testSettings = $communication->getSettings();
		
		var_dump("testSettings");
		
	}
}
?>