<?php
class Config
{
	// Database connection string
	public $dbDSN;
	// These are private, else they can be exported to json, use getters to get the values
	private $dbUser;
	private $dbPassword;
	public $sqlEngine;
	
    public $version_title;
    public $version_revision;
    public $version_release_time;
    public $version_release_description;
    public $version_update_time;
    public $checkNewTrunk;

    public $title;
    public $subtitle;
    public $url;
    public $location;
    public $gaugeMaxType;
    public $latitude;
    public $longitude;
    public $timezone;
    public $phpMinify;

    public $emailFromName;
    public $emailFrom;
    public $emailTo;
    public $emailAlarms;
    public $emailEvents;
    public $emailReports;

    public $smtpServer;
    public $smtpPort; // 25 (default), 587 (alternative), 465 (ssl)
    public $smtpSecurity; // none, ssl, tls
    public $smtpUser;
    public $smtpPassword;

    public $template;
    public $basePath;
    
    public $moneySign;
    
    public $upgradeMessage;
    public $upgradeMessageShow;
    
    /**
     * @deprecated
     */
    public $inverters; 

    public $devices;// Contains an array of devices
    
    public $graphSeries; // Contains an array of Graph Series
    public $graphAxes; // Contains an array of Graph axes
    public $graphShowACDC; // Should we show AC, DC of Both values in Graph?
    public $frontendLiveInterval; // X seconds interval that the frontend request live data from the database
    
    public $co2kwh;
    public $co2gas;
    public $co2CompensationTree;
    public $costkwh;
    public $costGas;
    public $costWater;
    public $debugmode;

    public $aurorapath; // The path to aurora
    public $mastervoltpath; // The path to aurora
    public $soladinSolgetpath; // The path to solget
    public $deltaSoliviapath; // The path to solget
    public $smagetpath; // The path to sma-get
    public $smaspotpath; // The path to sma-spot
    public $smaspotWSLpath; // The path to sma-spot -wsl
    public $kostalpikopath; // The path to Piko.py
    public $plugwiseStrech20IP;
    public $plugwiseStrech20ID;
    public $smartmeterpath;
    
    public $adminpasswd;
    public $invoiceDate;
    
    public $urlDir;
    public $urlComplete;
    
    public $dropboxKey;
    public $dropboxSecret;
    
    public $googleAnalytics;
    public $piwikServerUrl;
    public $piwikSiteId;
    public $cacheInputRate;
    
    public $hybridAuth;
    
    public $pauseWorker;
    public $restartWorker;

    public $useNewCommunication;
    
	/**
	 * Constructor
	 */
	function __construct()
	{
		// Disable for production switch to true to get debug logging
		$this->debugmode = false;
		
		// ### DATABASE CONFIG
		$this->dbDSN= 'sqlite:' . Session::getBasePath() .'/database/wsl.sdb';
		$this->dbUser='';
		$this->dbPassword='';

		// ### GENERAL
		$this->title='WebSolarLog';
		$this->subtitle='--== Sun <-> Energy ==--';
		$this->url=Common::getDomain();
		//$this->gaugeMaxType = 'panels';
		$this->location='Home Sweet Home';
		$this->latitude = '51.618017';
		$this->longitude = '2.48291';
		$this->timezone = 'UTC';
		
		//looks like we don't use this anymore
		// ### Create 2 inverter config for testing multi inverter config
		//$this->inverters = array();
		//
		
		
		// ### Graph series
		$this->graphSeries = array();
		
		// ### Graph axes
		$this->graphAxes = array();

		// ### Which Power data should we show? AC, DC of Both?
		$this->graphShowACDC = 'AC';

		// ### EMAIL
		$this->emailFrom = "test-from@test.localhost";
		$this->emailTo = "test-to@test.localhost";
		$this->emailAlarms = true;
		$this->emailEvents = true;
		$this->emailReports = false;
		
		// ## DROPBOX
		$this->dropboxKey = 'phrjcc77h8am0ch';
		$this->dropboxSecret = 'uxaakmr5iz5x4m4';
		$this->dropboxCallback = '#backup';
		
		$this->template = 'green'; // Default selected template

		$this->aurorapath = 'aurora'; // If in system path this is enough
		$this->mastervoltpath = 'aurora'; // If in system path this is enough
		$this->soladinSolgetpath = 'aurora'; // If in system path this is enough
		$this->smagetpath = 'sma_get'; // If in system path this is enough
		
		$this->frontendLiveInterval = 3;
		
		$this->co2kwh = 440; // 440g/kWh is conform europa average
		$this->co2gas = 2200; // 2200g/m3 natural gas is conform europa average
		$this->co2CompensationTree = 27; // 27g Co2 is what a average tree consume a day
		$this->costkwh = 23.5; 
		$this->costGas = 65;
		$this->costWater = 1200;
		$this->moneySign = "$";
		$this->adminpasswd = sha1('admin');
		$this->phpMinify = false;
		$this->pauseWorker = false;
		$this->restartWorker = false;
		
		$this->upgradeMessage = "";
		$this->upgradeMessageShow = false;
		
		$this->useNewCommunication = true;
	}
	
	public function getDatabaseUser() {
		return $this->dbUser;
	}
	public function setDatabaseUser($user) {
		$this->dbUser = $user;
	}
	
	public function getDatabasePassword() {
		return $this->dbPassword;		
	}

	public function setDatabasePassword($password) {
		$this->dbPassword = $password;
	}

	/**
	 * @Deprecated
	 */
	public function getInverterConfig($deviceId) {
		return $this->getDeviceConfig($deviceId);
	}
	
	public function getDeviceConfig($deviceId) {
		foreach ($this->allDevices as $device) {
            if ($device->id == $deviceId) {
				return $device;
			}
		}
	}	

	public function isValidCoords() {
		return ($this->isValidCoord($this->latitude) && $this->isValidCoord($this->longitude));
	}
		
	private function isValidCoord($coord) {
		return preg_match("/^[+-]?\d+\.\d+$/", $coord);
	}
}

?>