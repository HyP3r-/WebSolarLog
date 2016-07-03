<?php

class Device {
    public $id;
    public $deviceApi;
    public $type;
    public $name;
    public $liveOnFrontend;
    public $graphOnFrontend;
    public $graphShowACDC;
    public $description;
    public $initialkwh;
    public $producesSince;
    public $expectedkwh;
    public $plantpower;
    public $communicationId;
    public $comAddress;
    public $comLog;
    public $syncTime;
    public $pvoutputEnabled;
    public $pvoutputApikey;
    public $pvoutputSystemId;
    public $pvoutputWSLTeamMember;
    public $pvoutputAutoJoinTeam;
    public $sendSmartMeterData;
    public $state;
    public $refreshTime;
    public $historyRate;
    public $active;
    public $testMode;

    public $expectedJAN;
    public $expectedFEB;
    public $expectedMAR;
    public $expectedAPR;
    public $expectedMAY;
    public $expectedJUN;
    public $expectedJUL;
    public $expectedAUG;
    public $expectedSEP;
    public $expectedOCT;
    public $expectedNOV;
    public $expectedDEC;

    public $panels;

    /**
     * Constructor
     */
    function __construct()
    {
        $this->id = -1;
        $this->name = "";
        $this->description = "";
        $this->initialkwh = 0;
        $this->expectedkwh = 0;
        $this->plantpower = 0;
        $this->comAddress = '2';
        $this->comLog = false;
        $this->syncTime = false;
        $this->pvoutputWSLTeamMember = false;
        $this->pvoutputEnabled = false;
        $this->panels = array();
        $this->state = 0;
        $this->historyRate = 300;
        $this->refreshTime = 2;
        $this->sendSmartMeterData = true;
        $this->pvoutputAutoJoinTeam = true;
    }

    function getApi(Config $config) {
    	$api = null;
    	$deviceApi = strtoupper($this->deviceApi);
        if ($deviceApi == "AURORA") {
            $api = new Aurora($config->aurorapath, $this->comAddress, $config->comDebug);
        }
    	if ($deviceApi == "SMA-RS485") {
	    	$api = new Sma($config->smagetpath, $this->comAddress, $config->comDebug);
	    }
    	if ($deviceApi == "SMA-BT") {
	    	$api = new SMABlueTooth($config->smaspotpath, $this->comAddress, $config->comDebug);
	    }
    	if ($deviceApi == "SMA-BT-WSL") {
	    	$api = new SMASpotWSL($config->smaspotWSLpath, $this->comAddress, $config->comDebug);
	    }
	    if ($deviceApi == "DIEHL-ETHERNET") {
	    	$api = new Diehl($config->smagetpath, $this->comAddress, $config->comDebug);
	    }
	    if ($deviceApi == "DUTCHSMARTMETER") {
	    	$api = new SmartMeter($config->smartmeterpath, $this->comAddress, $config->comDebug);
	    }
        if ($deviceApi == "DUTCHSMARTMETERREMOTE") {
	    	$api = new SmartMeterRemote($config->smartmeterpath, $this->comAddress, $config->comDebug);
	    }
	    if ($deviceApi == "SMARTMETERAMPYREMOTE") {
	    	$api = new SmartMeterAmpyRemote($config->smartmeterpath, $this->comAddress, $config->comDebug);
	    }
    	if ($deviceApi == "OMNIK") {
            $api = new Omnik($config->omnikpath, $this->comAddress, $config->comDebug);
        }
        if ($deviceApi == "MASTERVOLT") {
            $api = new MasterVolt($config->mastervoltpath, $this->comAddress, $config->comDebug);
	    }
    	if ($deviceApi == "SOLADINSOLGET") {
	    	$api = new SoladinSolget($config->soladinSolgetpath, $this->comAddress, $config->comDebug);
	    }
	    if ($deviceApi == "DELTASOLIVIA") {
	    	$api = new DeltaSolivia($config->deltaSoliviapath, $this->comAddress, $config->comDebug);
	    }
	     
	    
    	if ($deviceApi == "OPEN-WEATHER-MAP") {
	    	$api = new WeatherOWM($config->latitude, $config->longitude);
	    }
    	if ($deviceApi == "KOSTALPIKO") {
	    	$api = new KostalPiko($config->kostalpikopath, $this->comAddress, $config->comDebug);
	    }

	    // Do we want to use the new communication?
	    if ($config->useNewCommunication){
		    $communicationService = new CommunicationService();
		    $communication = $communicationService->load($this->communicationId);
		    $api->setCommunication($communication, $this);
	    }
	    
	    return $api;
    }
}
?>