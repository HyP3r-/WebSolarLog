<?php
class HookHandler {
	private static $instance;
	
	// Singleton
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new HookHandler();
		}
		return self::$instance;
	}
	
	// Class
	private $hooks;
	
	/**
	 * Constructor
	 */
    public function __construct() {
        $this->hooks = array();
    }
    
    /**
     * add the given hook
     * @param $name off the hook
     * @param $callback function
     * @throws InvalidArgumentException
     */
    public function add($name, $callback) {
        // Check if it is already registerd
    	foreach($this->getCallbacks($name) as $cb) {
    		if ($cb == $callback) {
    			return; // Hook already exists
    		}
    	}

    	// Add the hook
    	//HookHandler::fire("onDebug", "Adding hook: " . $callback);
    	$this->hooks[$name][] = $callback;
    }
    
    /**
     * Get all listeners
     * @param $name off the hook
     * @return array:
     */
    public function getCallbacks($name) {
        return isset($this->hooks[$name]) ? $this->hooks[$name] : array();
    }
    
    /**
     * fire all hook listeners
     * @param $name off the hook
     * @param * will be passed too the hook
     */
    public function fire() {
    	$numargs = func_num_args();
    	if ($numargs < 1) return; // We at least need one 1 paramater the name off the hook
    	
    	// Get the name off the hook
    	$name = func_get_arg(0);
    	
    	$result = array();
        foreach($this->getCallbacks($name) as $callback) {
        	// Callback should be an class with an method seperated by an .
        	$classmethod = explode (".",$callback);
        	$classname = $classmethod[0];
        	$methodname = $classmethod[1];
        	$object = new $classname();
        	
        	if (method_exists($object, $methodname)) {
        		// use below for debugging hooks
        		// echo (date("Ymd His") . "\t fire :: object=".$classname.", methodname=".$methodname);
        		
        		// Handle any exception thrown by an hook
        		try {
        			// we cannot use return here, ass we break the hook after the first entry!
        			// Maybe fill an array with results off the hooks?
	        		$hookResult = $object->$methodname(func_get_args());
	        		if ($hookResult) {
	        			$result[] = array("name"=>$name,"result"=>$hookResult);
	        		}
        		} catch (SetupException $exception) {
        			echo (json_encode(Util::createErrorMessage($exception)));
        			exit();
        		} catch (Exception $e) {
        			try {
        				$this->fire('onError', "Error in hook " . $callback . " error: " . $e->getMessage());
        				$this->fire('onDebug', "Error in hook " . $callback . " error: " . $e->getMessage() . " trace: " .  $e->getTraceAsString());
	        		} catch (Exception $ignore) {}
        		}
        	} else {
        		// Log handling?
        	}
        }
        if (count($result) == 1) {
        	$first = reset($result);
        	return $first["result"];
        } 

        return $result;
    }
    /**
     * Pass the hookname on to the fire 
     * TODO :: Support for more paramters
     */
    public function fireFromQueue() {
    	$args = func_get_args();
    	$this->fire($args[0][1]); // Pass it on 
    }
}