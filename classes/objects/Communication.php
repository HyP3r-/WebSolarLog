<?php
class Communication {
	public $id;
	public $type; // HTTP or COMPORT
	public $name;
	public $uri; // HTTP URL or executable?
	public $port;
	public $timeout;
	public $optional;
	public $lastTestTime;
	public $lastTestSettings;
	public $lastTestResult;
	public $lastTestData;
	
	public function toJson() {
		return json_encode($this);
	}

	public function getSettings() {
		return json_decode($this->lastTestSettings);
	}
}
?>