<?php 
/**
 * This ApiController will handle the api requests.
 * It supports most off the restfull service methods.
 * 
 * - PUT
 * - POST
 * - GET
 * - DELETE
 * - HEAD*
 * - OPTIONS*
 * - TRACE* 
 * - CONNECT*
 * 
 * At the moment the ones with an * are not supported
 * 
 * @author Martin Diphoorn
 * @version 1.0.4
 * - fixed bug: object exepected but array give
 * 
 * version 1.0.3
 * - added RedBean_OODBBean support
 * 
 * version 1.0.2
 * - first stable version
 */
class ApiController
{
	// Singleton
	private static $instance;
	
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new ApiController(); 
		}
		return self::$instance;
	}
	// End singleton
	
	/**
	 * Start the routing based on the incoming method type and the request path
	 */
	public function route() {
		$method = $_SERVER['REQUEST_METHOD'];
		$request = (isset($_SERVER['PATH_INFO'])) ? explode("/", trim($_SERVER['PATH_INFO'], "/")) : "";
		
		return $this->routeRequest($method, $request);
	}
	
	/**
	 * Route the request
	 * @param $method
	 * @param $request
	 * @param $index
	 */
	public function routeRequest($method, $request) {
		if (!is_array($request)) {
			exit("Invalid request");			
		}
		
		$requestPath = array_values($request); // Copy orignal array, we dont want to modify it
		
		if (count($requestPath) == 0) {
			exit("Invalid request");			
		}
		
		$classname = array_shift($requestPath) . "Rest"; // Get the first entry and remove it from the array
		if ($classname == "Rest") {
			exit("Invalid request");			
		}
		
		// Create the first object
		$object = new $classname();
		
		// Loop to the last getter we can found
		for ($pathId = 0; $pathId < count($requestPath); $pathId++) {
			$methodObject = "";
			if (count($requestPath) >= 0) {
				$methodObject = $requestPath[$pathId];
				$propertyName = "get" . ucfirst($methodObject);
			}
			
			// Check if it exists
			if (!method_exists($object, $propertyName)) {
				if (method_exists($object, $method)) {
					return $this->getJson($object->$method($request, $requestPath));
				} else {
					return $object;
				}
			}
			
			$object = $object->$propertyName($requestPath);
			array_shift($requestPath);
		}
		
		if (count($requestPath) == 0 && method_exists($object, $method)) {
			return $this->getJson($object->$method($request, $requestPath));
		}
		
		return $object;
	}
	
	private function getJson($object) {
		// Check if this is an RedBean_OODBBean
		if (!is_array($object) && "RedBean_OODBBean" == get_class($object)) {
			return $object->getJson();
		}
		
		// Check if the array contains RedBean_OODBBean classes
		if (is_array($object)) {
			$result = array();
			foreach ($object as $bean) {
				if (!is_array($object) && "RedBean_OODBBean" == get_class($bean)) {
					$result[] = $bean->export();
				}
			}
			if (count($result) > 0) {
				return $result;
			}
		}
		
		return $object;
	}
}
?>