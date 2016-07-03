<?php
class SummaryRest {
	private $summaryService;
	
	/**
	 * Constructor
	 */
	function __construct() {
		$this->summaryService = new SummaryService();
	}
	
	/**
	 * Destructor
	 */
	function __destruct() {
		$this->summaryService = null;
	}

	public function DELETE($request, $options) {
	}
	
	/**
	 * Rest functions 
	 */
	public function GET($request, $options) {
		$date = $request[1];
		if($request[1] == "totals"){
		return $this->summaryService->totalProductionSummary();
		}else{
		return $this->summaryService->load($date);
		}
		
		
	
	}
		
	/**
	 * Rest functions
	 */
	public function POST($request, $options) {

	}

	
	
}
?>