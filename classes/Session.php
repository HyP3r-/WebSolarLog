<?php
class Session
{
    /**
     * Try to login
     * @return boolean
     */
    public static function login() {
        $username = Common::getValue('username', 'none');
        $password = sha1(Common::getValue('password', 'none'));
        if ($username === "admin" && $password === self::getConfig()->adminpasswd) {
            $_SESSION['userid'] = 1;
            $_SESSION['username'] = $username;
            return true;
        }
        return false;
    }

    /**
     * Remove the current session
     */
    public static function logout() {
        unset($_SESSION['userid']);
        unset($_SESSION['username']);
    }

    /**
     * Check if there is an valid login
     * @return boolean
     */

    public static function isLogin() {
        $result = false;
        if (isset($_SESSION['userid']) && isset($_SESSION['username'])) {
            if ($_SESSION['userid'] > 0 && $_SESSION['username'] === 'admin') {
                $result = true;
            }
        }
        return $result;
    }
      
    public static function initialize() {

    	self::initialize_db();
    	self::setTimezone();
    	self::setLanguage(); 
    	self::registerHooks();
    	
    	/**
    	 * Below we intialize some device to make sure the dbcheck hooks are registerd
    	 * When hooks are loaded from the database, this should be removed and fixed		
    	 */
    	$deviceService = new DeviceService();
    	$energyService = new EnergyService();
    	$graphService  = new GraphService();
    	$liveService = new LiveService();
    	$liveSmartMeterService = new LiveSmartMeterService();
    	$weatherService = new WeatherService();
    	$historySmartMeterService = new HistorySmartMeterService();
    }
    
    public static function initializeLight() {


    	
    	self::initialize_db();
    	self::setTimezone();
    	self::setLanguage();
    	self::registerHooks();
    	
    }
    
    private static function initialize_db() {
    	try {
    		// Setup the database
    		$config = Session::getConfig(true, false); // We dont need data from dbase
    		if ($config->getDatabaseUser() != "" && $config->getDatabasePassword() != "") {
    			R::setup($config->dbDSN, $config->getDatabaseUser(), $config->getDatabasePassword());
    		} else {
    			R::setup($config->dbDSN);
    		}
    		// Switch on/off debug
    		R::debug(false);
    		// Only use on sqlite for speedup
    		if (strpos($config->dbDSN,'sqlite') !== false) {
    			R::exec("PRAGMA synchronous = NORMAL"); // A little less secure then FULL, but much less IO
    			
    			R::exec("PRAGMA PRAGMA temp_store = 2"); // In memory (IO on SD is slow)
    			
    			PDODataAdapter::getInstance()->sqlEngine = 'sqlite';
    		}elseif(strpos($config->dbDSN,'mysql') !== false){
    			PDODataAdapter::getInstance()->sqlEngine = 'mysql';
    		}
    		RedBean_OODBBean::setFlagBeautifulColumnNames(false);
    	
    		R::setStrictTyping(false);
    	} catch (PDOException $e) {
    		exit("Could not connect with the database, this can be a wrong dsn in the configuration or php modules not installed like php5-sqlite");
    	}
    }
    
    private static $config;
    /**
     * 
     * @param string $reload reload the settings from the database
     * @param string $usedb use the database (we dont want this for the database settings
     * @return Config
     */
    public static function getConfig($reload=false,$usedb=true) {

    	if (!$usedb) {
    		$config = new Config();
    		// Get dbase settings
    		$dbconfig = self::loadConfigFile('database');
    		
    		if ($dbconfig != null && isset($dbconfig['database'])) {
    			$section = $dbconfig['database'];
    			if (is_array($section)) {
    				$config->dbDSN = $section['dsn'];
    				$config->setDatabaseUser($section['username']);
    				$config->setDatabasePassword($section['password']);
    			}
    		}
    		return $config;
    	}
    	
    	if (!isset(self::$config) || self::$config == null || $reload == true) {
    		self::$config = PDODataAdapter::getInstance()->readConfig();
    	}
    	
    	if (strpos(self::$config->dbDSN,'sqlite') !== false) {
    		self::$config->sqlEngine = 'sqlite'; //set db-engine dependent dateFunction
    	}elseif(strpos(self::$config->dbDSN,'mysql') !== false){
    		self::$config->sqlEngine = 'mysql'; //set db-engine dependent dateFunction
    	}
	    	
	    
    	return self::$config;
    }
    
    public static function writeConfig2FileSystem(){
    	$config = self::getConfig(true,true);
    	/*$config->smtpPassword=null;
    	$config->smtpUser=null;
    	$config->smtpServer=null;
    	$config->smtpSecurity=null;
    	$config->smtpPort=null;*/
    	$i=0;

    	// write to configfile that it is a filesystem config 
    	$config->configType = 'filesystem';
    	
    	$jsonFilePath = sys_get_temp_dir()."/WSLConfig.json";
    	$status = array();
    	FileUtil::writeObjectToJsonFile($jsonFilePath, $config);
    } 
    
    
    /**
     * Tries to load an config file
     * @param unknown $name
     * @return NULL|multitype:
     */
    public static function loadConfigFile($name) {
    	$path = self::getConfigPath($name . ".conf.php");
    	if ($path == null) {
    		// No settings available
    		return null;
    	}
    	return parse_ini_file($path,true);
    }
    
    /**
     * Retrieves the configpath for the filename,
     * if filename is given, but doesn't exist it returns null
     * If no file is give the config folder is returned
     * @param string $filename
     * @return NULL|string
     */
    public static function getConfigPath($filename = null) {
    	$configPath = self::getBasePath() . "/config";
    	
    	if ($filename == null) {
    		return $configPath;
    	}
    	
    	if (file_exists($configPath . "/" . $filename)) {
    		return $configPath . "/" . $filename;
    	}
    	
    	return null;
    }
    
    /**
     * Retrieves the base path
     * @return string
     */
    public static function getBasePath() {
    	return dirname(dirname(__FILE__));
    }
    
    /**
     * Set the language to the given language code
     */
    public static function setLanguage() {
    	// Check if we already know the language
    	$language = "en";
    	if (isset($_SESSION['WSL_LANGUAGE']) && $_SESSION['WSL_LANGUAGE'] != "") {
    		$language = $_SESSION['WSL_LANGUAGE'];
    	} else {
	    	$arBrowserLanguages = Util::getBrowserDefaultLanguage();
    		
	    	// Grep the first language we support
	    	$language = self::supportedLanguages($arBrowserLanguages); 
	    	$_SESSION['WSL_LANGUAGE'] = $language;
    	}

    	// Only use UTF-8 language
    	$locale = $language . ".UTF-8";
    	$domain = "default";
    	
    	setlocale(LC_ALL, $locale);
    	setlocale(LC_MONETARY, $locale);
    	putenv("LC_ALL=".$locale);
    	bindtextdomain($domain, "./locale");
    	bind_textdomain_codeset($domain, 'UTF-8');
    	textdomain($domain);
    	return array('locale'=>$locale,'domain'=>$domain);
    }
    
    /**
     * Returns the first supportedLanguages from the list
     * @param unknown $arBrowserLanguages
     */
    public static function supportedLanguages($arBrowserLanguages) {
    	if (is_array($arBrowserLanguages)) {
	    	// Go through all languages send by the browser
	    	foreach ($arBrowserLanguages as $language) {
	    		// First convert - to an _
	    		$language = str_replace("-", "_", $language);
	    		$languageParts = explode("_", $language);
	
	    		// Convert to our folder style
	    		$language = strtolower($languageParts[0]) . ((count($languageParts) == 2) ? "_" . strtoupper($languageParts[1]) : "");
	    		$languageParts = explode("_", $language);
	    		
	    		// Check if the full language dir is available
	    		if (is_dir(self::getBasePath() . "/locale/" . $language)) {
	    			return $language;
	    		}
	    		// Check if the first part off the language dir is available (nl-NL)
	    		if (is_dir(self::getBasePath() . "/locale/" . $languageParts[0])) {
	    			return $languageParts[0];
	    		}
	    		// Check if the language size is 2 characters and if lowercase_uppercase exists
	    		if (strlen($language) == 2) {
		    		$combinedLanguage = strtolower($language) . "_" . strtoupper($language);
		    		if (is_dir(self::getBasePath() . "/locale/" . $combinedLanguage)) {
		    			return $combinedLanguage;
		    		}
	    		}
	    	}
    	}
    	return "en"; // English is the default
    }
    
    /**
     * Sets the time zone
     */
    public static function setTimezone() {
    	ini_set('date.timezone', self::getConfig()->timezone);
    }
    
    /**
     * Register all the addon hooks
     */
    public static function registerHooks() {
    	// TODO :: Get from dbase
    	
    	$hookHandler = HookHandler::getInstance();
    	
    	// LoggerAddon, Needs to be first so we can use debug as fast as possible
    	$hookHandler->add("onDebug", "LoggerAddon.onDebug");
    	$hookHandler->add("onError", "LoggerAddon.onError");
    	$hookHandler->add("onWarning", "LoggerAddon.onWarning");
    	$hookHandler->add("onInfo", "LoggerAddon.onInfo");
    	$hookHandler->add("onInverterStartup", "LoggerAddon.onInverterStartup");
    	$hookHandler->add("onInverterError", "LoggerAddon.onInverterError");
    	$hookHandler->add("onInverterShutdown", "LoggerAddon.onInverterShutdown");
    	
    	// Core hooks
    	$hookHandler->add("onInverterInfo", "CoreAddon.onInverterInfo");
    	$hookHandler->add("onInverterAlarm", "CoreAddon.onInverterAlarm");
    	$hookHandler->add("onHistory", "CoreAddon.onHistory");
    	$hookHandler->add("onLiveData", "CoreAddon.onLiveData");
    	$hookHandler->add("onFastJob", "CoreAddon.touchPidFile"); // Touch the pid file
    	$hookHandler->add("onEnergy", "CoreAddon.onEnergy"); // Will run also if device is down
    	$hookHandler->add("newHistory", "CoreAddon.onEnergy"); // Update on every new History
    	
    	// BasicChecksAddon
    	$hookHandler->add("newLiveData", "BasicChecksAddon.onNewLive");
    	$hookHandler->add("onNoLiveData", "BasicChecksAddon.onNoLiveData");
    	$hookHandler->add("onRegularJob", "BasicChecksAddon.on10MinJob");
    	$hookHandler->add("onInActiveJob", "BasicChecksAddon.onInActiveJob");
    	
    	// MailAddon
    	$hookHandler->add("onError", "MailAddon.onError");
    	$hookHandler->add("onWarning", "MailAddon.onWarning");
    	$hookHandler->add("onInverterStartup", "MailAddon.onInverterStartup");
    	$hookHandler->add("onInverterShutdown", "MailAddon.onInverterShutdown");
    	$hookHandler->add("onInverterError", "MailAddon.onInverterError");
    	$hookHandler->add("onInverterWarning", "MailAddon.onInverterWarning");
    	
    	
    	// PvOutputAddon >>> Testing >>> moved to server.php as a QueueItem
    	//$hookHandler->add("onFastJob", "PvOutputAddon.onJob");
    	
    	// Smart meter addon
    	$hookHandler->add("onLiveSmartMeterData", "SmartMeterAddon.onLiveSmartMeterData");
    	$hookHandler->add("onSmartMeterHistory", "SmartMeterAddon.onSmartMeterHistory");
    	$hookHandler->add("onSmartMeterEnergy", "SmartMeterAddon.onSmartMeterEnergy"); // Will run at 00:00
    	
    	
    	$hookHandler->add("GraphDayPoints", "SmartMeterAddon.GraphDayPoints"); // Will run at 00:00
       	$hookHandler->add("GraphDayPoints", "GraphDataService.GraphDayPoints"); // Will run at 00:00

       	$hookHandler->add("mainSummary", "SmartMeterAddon.onSummary"); // Will run at 00:00
       	$hookHandler->add("mainSummary", "EnergyService.onSummary"); // Will run at 00:00
       	$hookHandler->add("mainSummary", "WeatherService.onSummary"); // Will run at 00:00
       	
       	
       	
    	$hookHandler->add("installGraph", "SmartMeterAddon.installGraph"); // Will run at 00:00
    	
    	$hookHandler->add("defaultAxes", "SmartMeterAddon.defaultAxes");
    	$hookHandler->add("defaultSeries", "SmartMeterAddon.defaultSeries");
    	
    	$hookHandler->add("onPVoutputAddStatus", "PvOutputAddon.onJob");

    	//plugwise update 
    	$hookHandler->add("onFastJob", "PlugwiseStretchAddon.onJob");
    	
    	//write config as a JSON config file to filesystem
    	$hookHandler->add("onFastJob", "Session.writeConfig2FileSystem");
    	
    	// fire from frontend
    	$hookHandler->add("checkEnergy", "EnergyCheckAddon.checkEnergy");

    	// TwitterAddon
    	$hookHandler->add("onInverterShutdown", "TwitterAddon.sendTweet");
    	
    	//send last record to PVoutput
    	$hookHandler->add("onInverterShutdown", "PvOutputAddon.onShutdown");
    	
    	// Statistics
    	$hookHandler->add("onCacheJob", "CacheAddon.averagePower");
    	$hookHandler->add("onCacheJob", "CacheAddon.EnergyValues");
    }
}
?>