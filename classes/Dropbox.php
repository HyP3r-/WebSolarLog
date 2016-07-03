<?php
Class Dropbox  {
	public $encrypter;
	public $storage;
	public $OAuth;
	public $dropbox;
	
	function __construct(){
		$this->userID = 1;
		$config = Session::getConfig();
		$protocol = (!empty($_SERVER['HTTPS'])) ? 'https' : 'http';
		
		$callback = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		

		spl_autoload_register(function($class){
			if (substr($class, 0, strlen("Model")) === "Model") {
				return;
			}
			if ($class === "Dropbox\OAuth\Storage\R" ||  substr($class, 0, strlen("RedBean")) === "RedBean") {
				$docRoot = dirname(dirname(__FILE__));
				require_once $docRoot."/classes/R.php";
				return;
			}
			$class = str_replace('\\', '/', $class);
			require_once('' . $class . '.php');
		});

		$this->encrypter = new \Dropbox\OAuth\Storage\Encrypter('!A@B#C$D%E*F(G)H!I@J#K$L%M^N&O*P');
		$this->storage = new \Dropbox\OAuth\Storage\PDO($this->encrypter, $this->userID);
		$this->storage->setTable('dropboxOauthTokens');
		$this->OAuth = new \Dropbox\OAuth\Consumer\Curl($config->dropboxKey, $config->dropboxSecret, $this->storage,$callback);
		$this->dropbox = new \Dropbox\API($this->OAuth);
	}
}
?>