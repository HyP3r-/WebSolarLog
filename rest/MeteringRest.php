<?php
class MeteringRest
{
	/**
	 * Constructor
	 */
	function __construct()
	{
	}
	
	/**
	 * Destructor
	 */
	function __destruct()
	{
	}

	/**
	 * Rest functions 
	 */
	
	public function GET($request, $options) {
		$type = "today"; // Default
		if (count($options) > 0) {
			$type = (trim($options[0]) != "") ? $options[0] : $type;
		}
		
		$result = array();
		$result['type'] = $type;
		$result['data'] = null;
		switch ($type) {
			case "today":
				$smartMeterAddon = new SmartMeterAddon();
				$result['data'] = $smartMeterAddon->readSmartMeterHistory(2, null);
				break;
		}
		
		return $result;
	}	
}
?>