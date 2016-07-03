<?php
class QueueServer {
	private static $instance;
	
	// Singleton
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new QueueServer();
		}
		return self::$instance;
	}
	
	public static function addItemToDatabase(QueueItem $item) {
		self::getInstance()->updateDbQueueItem($item);
	}
	
	public static function removeItemFromDatabase(QueueItem $item) {
		self::getInstance()->removeDbQueueItem($item);
	}
	
	public static function printDebugInfo() {
		$items = self::getInstance()->queue;

		// Get the memory values
		$memory = memory_get_usage() / 1024 / 1024; // calculate mb
		$memory_string = number_format($memory, 2);
		
		HookHandler::getInstance()->fire("onDebug", " QueueServer: current memory usage = " . $memory_string . "mb queue size: " . count($items));
		foreach ($items as $queueItem) {
			HookHandler::getInstance()->fire("onDebug", "QueueServer item: " . $queueItem->toString());
		}
	}
	
	// Class
	private $started;
	private $queue;

	function __construct() {
		// Initialize objects
		$this->queue = array();
	}
	
	function __destruct() {
		// Release objects
		$this->sync();
		$this->queue = null;
	}
	
	public function start() {
		HookHandler::getInstance()->fire("onDebug", " QueueServer: start");
		$syncOffset = 30; // seconds
		$this->sync();
		$this->started = true;
		
		while ($this->started) {
			$syncOffsetTime = time();
			while (time() - $syncOffsetTime < $syncOffset) {
				$nextItem = $this->next();
				// Do we have an item to process
				if ($nextItem != null) {
					$this->process($nextItem);
				}
				usleep(1500000); // 1.5 second
				
			}
			
			// sync with the database
			$this->sync();
			usleep(2000000); // 2 second Breath
		}
	}

	public function stop() {
		HookHandler::getInstance()->fire("onDebug", " QueueServer: stop");
		$this->sync();
		$this->started = false;
	}
	
	// Add an item to the  queue
	public function add(QueueItem $item) {
		$item->validate(); // Validate before adding
		$this->queue[] = $item;
	}
	
	// Remove an item from the queue
	private function remove(QueueItem $item) {
		foreach ($this->queue as $key => $queueItem) {
			if ($queueItem->dbId == $item->dbId) {
				unset($this->queue[$key]);
			}
		}
	}
	
	
	/**
	 * Checks if there is an item in the queue needed to be processed, returns the item and removes it from the queue
	 * @return Null, QueueItem
	 */
	private function next() {
		$currentTime = time();
		
		foreach ($this->queue as $key => $queueItem) {
			if ($queueItem->time <= $currentTime) {
				unset($this->queue[$key]);
				return $queueItem;
			} 
		}
		return null;
	}
	
	
	// Retrieve new items from the queue
	private function sync() {
		// Get the current db ids in the queue
		$currentQueueIds = $this->getDbQueueItemIds();
		
		// Get the current QueueItems from the db
		$beans = R::findAll("QueueItem", "order by time");
		
		// Check for new ones in the database
		foreach ($beans as $bean) {
			if (!in_array($bean->id, $currentQueueIds)) {
				$arguments = $bean->arguments; // TODO :: Support for more then one parameter
				
				// Create new queue Item 
				$newQueueItem = new QueueItem($bean->time, $bean->classmethod, $arguments, $bean->requeue, $bean->requeueTime, true);
				$newQueueItem->dbId = $bean->id;
				
				// Add the new item to the queue
				QueueServer::getInstance()->add($newQueueItem);
			}
		}
	}
	
	private function getDbQueueItemIds () {
		$ids = array();
		foreach ($this->queue as $key => $queueItem) {
			if ($queueItem->dbSync && $queueItem->dbId > 0) {
				$ids[] = $queueItem->dbId;
			}
		}
		return $ids;
	}
	
	// Remove an item from the database queue
	private function removeDbQueueItem(QueueItem $item) {
		// remove from queue
		$this->remove($item);
	
		// Check if it is in the database
		if ($item->dbId > 0) {
			$dbQueueItem = R::load("QueueItem", $item->dbId);
			if ($dbQueueItem) {
				R::trash($dbQueueItem);
			}
		}
	}
	
	private function updateDbQueueItem(QueueItem $item) {
		$dbItem = null;
		if ($item->dbId > 0) {
			$dbItem = R::load("QueueItem", $item->dbId);
		}
		
		// Check if it is still in the dbase or we received an empty object
		if ($item->dbId > 0 && ($dbItem == null || $dbItem->id != $item->dbId )) {
			// Deleted remove it from the queue
			$this->remove($item);
			return;
		}
		
		if ($dbItem == null) {
			$dbItem = R::dispense("QueueItem");
		}
		$dbItem->time = $item->time;
		$dbItem->classmethod = $item->classmethod;
		$dbItem->arguments = $item->arguments;
		$dbItem->requeue = $item->requeue;
		$dbItem->requeueTime = $item->requeueTime;
		$dbItem->dbSync = $item->dbSync;
		
		$item->dbId = R::store($dbItem);
		return $item;
	}
	
	// Process the job 
	private function process(QueueItem $item) {
		// Remove from database if needed
		if ($item != null && $item->dbSync && $item->requeue == false) {
			$this->removeDbQueueItem($item);
		}
		
		try {
			R::begin(); // Every queuItem has its own database transaction
			$classmethod = explode (".",$item->classmethod);
			$classname = $classmethod[0];
			$methodname = $classmethod[1];
			
			$parameters = array();
			$parameters[] = $item;
			if (is_array($item->arguments)) {
				$parameters = array_merge($parameters, $item->arguments);
			} elseif (trim($item->arguments)) {
				$parameters = array_merge($parameters, array($item->arguments));
			}
	
			if ($classname == "HookHandler") {
				$object = HookHandler::getInstance();
			} else {
				$object = new $classname();
			}
			$object->$methodname($parameters);
			
			R::commit(); // Commit the transaction
		} catch (Exception $e) {
			R::rollback();
			HookHandler::getInstance()->fire("onError", "QueueItem $item->classmethod had an error: " . $e->getMessage());
		}

		// We still need to requeue the item even after an exception!
		if ($item->requeue) {
			$item->time = $item->getNextTime();
			// Save the new version to the database
			if ($item->dbSync) {
				$item = $this->updateDbQueueItem($item);
			}
			if ($item != null) {
				$this->add($item);
			}				
		}
	}
}
?>