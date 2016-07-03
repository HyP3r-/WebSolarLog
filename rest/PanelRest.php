<?php 
class PanelRest {
	private $panelService;
	
	/**
	 * Constructor
	 */
	function __construct() {
		$this->panelService = new PanelService();
	}
	
	/**
	 * Destructor
	 */
	function __destruct() {
		$this->panelService = null;
	}
	
	public function DELETE($request, $options){
		return $this->panelService->delete($options[0]);
	}
}

?>