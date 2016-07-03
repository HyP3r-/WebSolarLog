<?php 
class AuthenticationException extends Exception {
	// Redefine the exception so message isn't optional
	public function __construct($message, $code = 0, Exception $previous = null) {
		// make sure everything is assigned properly
		parent::__construct($message, $code, $previous);
	}
	
	// custom string representation of object
	public function __toString() {
		return $this->message . " in file " . $this->getFile() . '[' . $this->getLine() . ']' ;
	}
	
	public function getJSONMessage() {
		return array('result'=>'error', 'success'=>false,'exception'=>__CLASS__, 'message'=>$this->__toString());
	}
}
?>