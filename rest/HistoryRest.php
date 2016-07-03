<?php
class HistoryRest
{
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
	
	public function getMetering() {
		return new MeteringRest();
	}
	
	public function getProduction() {
		return new ProductionRest();
	}	

	public function getWeather() {
		return new WeatherRest();
	}	
}
?>