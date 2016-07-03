<?php
class ConfigRest {
	
	/**
	 * Constructor
	 */
	function __construct() {
	}
	
	/**
	 * Destructor
	 */
	function __destruct() {
	}

	/**
	 * Rest functions 
	 */
	
	public function GET($request, $options) {
		$option = "";
		if (count($options) > 0) {
			$option = strtolower($options[0]);
		}
		if ($option == "upgrademessageshow") {
			return array("result"=>Session::getConfig()->upgradeMessageShow);
		}
		if ($option == "upgrademessage") {
			return array("result"=>Session::getConfig()->upgradeMessageShow, "message"=>isset(Session::getConfig()->upgradeMessage) ? Session::getConfig()->upgradeMessage : "");
		}
		return array("result"=>false);
	}
}
?>