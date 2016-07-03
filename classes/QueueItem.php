<?php
class QueueItem {
	/**
	 *	This is the time after which it is executed 
	 */
	public $time;
	
	/**
	 * The class and method to be executed example: Logger.debug() 
	 */
	public $classmethod;
	
	/**
	 * The arguments to be passed to the method: Logger.debug($arguments)
	 */
	public $arguments;

	/**
	 * Should this item be put on queue again
	 */
	public $requeue;
	
	/**
	 * The time the queue should wait before processed again
	 */
	public $requeueTime;
	
	/**
	 * Should this item be synchronized in the database
	 */
	public $dbSync;
	
	/**
	 * The id in the database, if empty it is an new object 
	 */
	public $dbId;
	
	/**
	 * Constructor for creating an QueueItem
	 * @param number $time
	 * @param string $classmethod
	 * @param string $arguments
	 * @param bool $requeue
	 * @param number $requeueTime
	 * @param bool $dbSync
	 */
	public function __construct($time, $classmethod, $arguments = null, $requeue = false, $requeueTime = 3600, $dbSync = false) {
		$this->time = $time;
		$this->classmethod = $classmethod;
		$this->arguments = $arguments;
		$this->requeue = $requeue;
		$this->requeueTime = $requeueTime;
		$this->dbSync = $dbSync;
	}
	
	/**
	 * Throws QueueItemValidationException if something is wrong
	 */
	public function validate() {
		if ($this->requeue && $this->requeueTime < 1) {
			echo ("Warning invalid requeueTime! \n");
		}
	}
	
	/**
	 * Calculate the next time.
	 * If the time is already past, calculate the first next time.
	 * @return time
	 */
	public function getNextTime() {
		$newTime = $this->time + $this->requeueTime;
		if (time() > $newTime) {
			$newTime = time();
			return $newTime + $this->requeueTime - ($newTime % $this->requeueTime);
		}
		return $newTime;
	}
	
	public function toString() {
		$arguments = "";
		if (is_array($this->arguments)) {
			foreach ($this->arguments as $argument) {
				$arguments .= "[";
				if ($argument instanceof Device) {
					$arguments .= "{id:" . $argument->id . ",name:" . $argument->name . "}";
				} else {
					$argument .= "{". print_r($argument, true) . "}";
				}
				$arguments .= "]";
			}
			
		} else {
			$arguments = print_r($this->arguments, true);
		}
		
		return  $this->classmethod . " time=" . $this->time . " requeueTime=" . 
				$this->requeueTime . " requeue=" . $this->requeue . " dbsync=" . $this->dbSync .
		        " arguments=" . $arguments;
	}
}
?>