<?php
class DiagnosticsService {
	//public static $tbl = "weather";
	private $config;

	function __construct() {
		HookHandler::getInstance()->add("onJanitorDbCheck", "WeatherService.janitorDbCheck");
		$this->config = Session::getConfig();
	}


	/**
	 * The diagnostics() function runs a number of test for us.
	 * It also returns the available drivers/extensions/classes, version check, DB info, log paths etc...
	 * @return array
	 */
	public function load() {
		$adapter = new PDODataAdapter();


		$result = array();
		$result['sqlite'] = false;
		$result['available_drivers'] = PDO::getAvailableDrivers();
		$result['db']['sqlEngine'] = $adapter->sqlEngine;
		// Check if sql lite is installed
		foreach ($result['available_drivers'] as $driver) {
			if ($driver == 'sqlite') {
				$result['sqlite'] = true;
			}
		}
		// check path is ending with a slash(/)
		$configURL = 'http://'.$this->config->url.((substr($this->config->url, -1)=='/') ? '' : '/');
		$basePath = Session::getBasePath().((substr(Session::getBasePath(), -1)=='/') ? '' : '/');

		// old files
		//$result['logs'][] = array('url' => $configURL.'log/debug.log','location' => $basePath.'log/debug.log', 'name' => 'Debug');
		//$result['logs'][] = array('url' => $configURL.'log/error.log','location' => $basePath.'log/error.log', 'name' => 'Error');
		// /old files

		// new logfile
		$result['logs'][] = array('url' => $configURL.'log/wsl.log','location' => $basePath.'log/wsl.log', 'name' => 'wsl');

		$PidFilename = $basePath.'scripts/server.php.pid';

		$result['currentTime']=time();
		if (file_exists($PidFilename)) {
			$stat = stat($PidFilename);
			$result['pid']['exists']=true;
			$result['pid']['timeDiff'] = time()-$stat['atime'];
			if($result['pid']['timeDiff'] >= 75){
				$result['pid']['WSLRunningState']=false;
			}else{
				$result['pid']['WSLRunningState']=true;
			}

			$result['pid']['atime']=$stat['atime'];
			$result['pid']['mtime']=$stat['mtime'];
			$result['pid']['ctime']=$stat['ctime'];
		} else {
			$result['pid']['exists']=false;
		}
		$result['commands']['start'] = $basePath.'scripts/./wsl.sh start';
		$result['commands']['stop'] = $basePath.'scripts/./wsl.sh stop';
		$result['commands']['restart'] = $basePath.'scripts/./wsl.sh restart';
		$result['commands']['status'] = $basePath.'scripts/./wsl.sh status';


		$result['db']['dsn'] = $this->config->dbDSN;
		if ($result['db']['sqlEngine'] == 'sqlite') {
			$SDBFilename = Session::getBasePath().'/database/wsl.sdb';
			$result['dbRights'] = Util::file_perms($SDBFilename);


			if (file_exists($SDBFilename)) {
				$stat = stat($SDBFilename);
				$result['db']['exists']=true;
				$result['db']['margin'] = 70;
				$result['db']['timeDiff'] = time()-$stat['mtime'];
				($result['db']['timeDiff'] >= $result['db']['margin']) ? $result['db']['dbChanged']=false : $result['db']['dbChanged']=true;

				$result['db']['atime']=$stat['atime'];
				$result['db']['mtime']=$stat['mtime'];
				$result['db']['ctime']=$stat['ctime'];
			} else {
				$result['db']['exists']=false;
			}
		}

		// TODO
		// needs some standard DB testing;
		// last live record, could we connect, etc...


		// Try to get the sqlite version if installed
		if ($result['sqlite'] === true) {
			$filename = tempnam(sys_get_temp_dir(), 'empty'); // use a temporary empty db file for version check
			$conn = new PDO('sqlite:' . $filename);
			$result['sqliteVersion'] = $conn->getAttribute(constant("PDO::ATTR_SERVER_VERSION"));
			$result['sqliteVersionCheck'] = (version_compare($result['sqliteVersion'], '3.7.11', '>=')) ? true : false ;
			$conn = null; // Close the connection and free resources
		}else{

		}

		// Check if the following extensions are installed/activated
		$checkExtensions = array('curl','sqlite3','sqlite','json','calendar','mcrypt');
		foreach ($checkExtensions as $extension) {
			if($extension == 'sqlite'){
				if(!$extensions['sqlite3']['status']){
					$extensions[$extension] = Util::checkIfModuleLoaded($extension,$type='extension');
				}
			}else{
				$extensions[$extension] = Util::checkIfModuleLoaded($extension,$type='extension');
			}
		}



		// check functions
		$extensions["mcrypt_module_open"] = array('name'=>"mcrypt_module_open",'status'=>Encryption::isMcryptAvailable(),'type'=>'function');
		$result['extensions'] = $extensions;

		$result['phpVersion'] = phpversion();
		$result['phpVersionCheck'] = (version_compare($result['phpVersion'], '5.3.10', '>=')) ? true : false ;

		$result['sqliteVersionMixed'] = (isset($extensions['sqlite']) && $extensions['sqlite']['status'] && !$extensions['sqlite3']['status']);

		// Encryption/Decryption test
		if (Encryption::isMcryptAvailable()) {
			$testphrase ="This is an test sentence";
			$encrypted = Encryption::encrypt($testphrase);
			$decrypted = Encryption::decrypt($encrypted);

			$result['encrypting'] = ($testphrase === $decrypted);
		} else {
			$result['encrypting'] = false;
		}


		$result['browserLanguages'] = Util::getBrowserDefaultLanguage();
		$result['supportedLanguages'] = Session::supportedLanguages($result['browserLanguages']);
		$result['setLanguage'] = Session::setLanguage();
		$result['sessionLanguage'] = $_SESSION['WSL_LANGUAGE'];

		$dateTimeZoneUTC = new DateTimeZone("utc");
		$dateTimeZoneConfig = new DateTimeZone($this->config->timezone);

		$dateTimeUTC = new DateTime("now", $dateTimeZoneUTC);
		$dateTimeConfig = new DateTime("now", $dateTimeZoneConfig);

		$offset = $dateTimeZoneConfig->getOffset($dateTimeUTC);

		$result['time']['offset'] =  ($offset>0) ? $offset/3600 : $offset;
		$result['time']['dateTimeUTC'] = $dateTimeUTC;
		$result['time']['dateTimeLocation'] = $dateTimeConfig;
		return $result;
	}

}
?>