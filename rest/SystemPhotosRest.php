<?php
class SystemPhotosRest {
	private $systemPhotosService;
	
	/**
	 * Constructor
	 */
	function __construct() {
		$this->systemPhotosService = new SystemPhotosService();
	}
	
	/**
	 * Destructor
	 */
	function __destruct() {
		$this->systemPhotosService = null;
	}

	public function DELETE($request, $options) {
	}
	
	/**
	 * Rest functions 
	 */
	public function GET($request, $options) {
		return $this->systemPhotosService->loadSystemPhotos();
	
	}
		
	/**
	 * Rest functions
	 */
	public function POST($request, $options) {

	}
}
?>