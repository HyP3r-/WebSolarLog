<?php
class PDODataAdapter {
	private static $instance;
	public $ProcessTime;
	public $sqlEngine;
	
	private $deviceService;

	// Singleton
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new PDODataAdapter();
		}
		return self::$instance;
	}


	function __construct() {
		$this->deviceService = new DeviceService();
	}

	function __destruct() {
		//print "Destroying " . $this->name . "\n";
		$this->deviceService = null;
	}


	public static function setProcessTime($timestamp=null){
		($timestamp) ? $this->ProcessTime = $timestamp : $ $this->ProcessTime=time();
	}

	/**
	 * write the max power today to the file
	 * @param int $invtnum
	 * @param MaxPowerToday $mpt
	 */
	public function writeMaxPowerToday($invtnum, MaxPowerToday $mpt) {
		$bean =  R::findOne('pMaxOTD',
				' INV = :INV AND SDTE LIKE :date ',
				array(':INV'=>$invtnum,
						':date'=> '%'.date('Ymd').'%'
				)
		);

		if (!$bean){
			$bean = R::dispense('pMaxOTD');
		}
		$bean->INV = $invtnum;
		$bean->SDTE = $mpt->SDTE;
		$bean->time = $mpt->time;
		$bean->GP = $mpt->GP;

		//Store the bean
		$id = R::store($bean);
		return $id;
	}

	/**
	 * read the MaxPowerToday from an file
	 * @param int $invtnum
	 * @return MaxPowerToday
	 */
	public function readMaxPowerToday($invtnum, $date=null) {
		(isset($date)) ? $date = $date : $date = date('d-m-Y');
		$beginEndDate = Util::getBeginEndDate('day', 1,$date);

		$bean =  R::findOne( 'pMaxOTD',
				' INV = :INV AND time > :beginDate AND  time < :endDate ORDER BY time',
				array(':INV'=>$invtnum,':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate'])
		);

		return $bean;
	}

	/**
	 * Diehl
	 */
	public function checkEventExists($number,$event){
		$bean = R::getAll('select * from event where number = :number and event = :event  LIMIT 2 ',
				array(':number'=>$number,':event'=>$event)
		);

		return $bean;
	}


	/**
	 * Reads the events of a certain Type
	 * @param int $invtnum
	 * @return bean object
	 */
	public function readTypeEvents($invtnum,$type,$limit=20) {
		($type=='Info')?$limit=1:$limit=$limit;
		$bean = R::getAll("
				select *,
				time AS date 
				FROM event
				WHERE Type = :type
				ORDER BY id DESC
				LIMIT :limit ",
				array(':limit'=>$limit,':type'=>$type)
		);
		return $bean;
	}

	/**
	 * Read Daily Data
	 * @param string $date
	 * @param int $invtnum
	 * @return bean object
	 */
	public function readDailyData($date, $invtnum) {
		$bean =  R::findAndExport(
				'history',
				' INV = :INV AND SDTE like :date ',
				array(':INV'=>$invtnum,':date'=> '%'.$date.'%')
		);

		$points = $this->DayBeansToDataArray($bean);
		$lastDays = new LastDays();
		$lastDays->points=$points[0];
		$lastDays->KWHT=$points[1];
		return $lastDays;
	}
	
		/**
	 * read Last Days Data
	 * @param int $invtnum
	 * @param int $limit
	 * @return bean object
	 */
	public function readLastDaysData($invtnum, $limit = 60) {
		$bean =  R::findAndExport(
				'energy',
				' INV = :INV  ',
				array(':INV'=>$invtnum
				)
		);
		$points = $this->DayBeansToDataArray($bean);
		$lastDays = new LastDays();
		$lastDays->points=$points[0];
		return $lastDays;
	}


	/**
	 *
	 * @param unknown_type $beans
	 */
	public function DayBeansToGraphPoints($beans,$graph,$startDate,$config){
		$i=0;
		$firstBean = array();
		$preBean = array();

		$KWHT = 0;
		$lastDays = new LastDays();

		foreach ($beans as $bean){
			if ($i==0){
				$firstBean = $bean;
				$preBean = $bean;
				$preBeanUTCdate = $bean['time'];
			}
			$UTCdate = $bean['time'];
			$UTCtimeDiff = $UTCdate - $preBeanUTCdate;
			$cumPower = round(($bean['KWHT']-$firstBean['KWHT'])*1000,0);
			// 09/30/2010 00:00:00
			$avgPower = Formulas::calcAveragePower($bean['KWHT'], $preBean['KWHT'], $UTCtimeDiff,0,0);
			$graph->points['cumPower'][] = array (  $UTCdate ,$cumPower);
			$graph->points['avgPower'][] = array (  $UTCdate ,$avgPower);
			$preBeanUTCdate = $bean['time'];
			$preBean = $bean;
			$i++;
		}

		if($i>0){
			$plantPower = $this->readPlantPower();
			if($cumPower>0 AND $plantPower>0){
				$kWhkWp = number_format(($cumPower/1000) / ($plantPower/1000),2,',','');
			}else{
				$kWhkWp = number_format(0,2,',','');
			}

			if($cumPower >= 1000){
				$cumPower = number_format($cumPower /=1000,2,',','');
				$cumPowerUnit = "kWh";
			}else{
				$cumPowerUnit = "W";
			}

			$graph->metaData['KWH']=array('cumPower'=>$cumPower,'KWHTUnit'=>$cumPowerUnit,'KWHKWP'=>$kWhkWp);
			$graph->series[0] = array('label'=>'Energy (Wh)','yaxis'=>'y2axis');
			$graph->series[1] = array('label'=>'Avg. Power(W)','yaxis'=>'yaxis');
			

			$graph->axes['yaxis']  = array('label'=>'Avg. Power(Wh)','min'=>0,'labelRenderer'=>'CanvasAxisLabelRenderer');
			$graph->axes['y2axis'] = array('label'=>'Energy (Wh)','min'=>0,'labelRenderer'=>'CanvasAxisLabelRenderer');
			$graph->metaData['hideSeries']= array();
			
			$graph->metaData['legend']= array(
							"show"=>true,
							"location"=>'nw',
							"renderer"=>'EnhancedLegendRenderer',
							"rendererOptions"=>array(
									"seriesToggle"=>'normal',
							),
							"width"=>0,
							"left"=>0
			);
		}
		return $graph;
	}

	/**
	 * Get an overal power amount for all production devices
	 * @return number
	 */
	function readPlantPower(){
		$plantPower = 0;
		foreach (Session::getConfig()->devices as $device) {
			if ($device->type == "production") {
				$plantPower += $device->plantpower;
			}
		}
		return $plantPower;
	}

	/**
	 *
	 * @param Config $config
	 */
	public function writeConfig(Config $config) {
		// Only save the object self not the arrays
		$bean = R::findOne('config');

		if (!$bean){
			$bean = R::dispense('config');
		}

		$bean->version_title = $config->version_title;
		$bean->version_revision = $config->version_revision;
		$bean->version_release_time = $config->version_release_time;
		$bean->version_release_description = $config->version_release_description;
		$bean->version_update_time = $config->version_update_time;
		$bean->checkNewTrunk = $config->checkNewTrunk;

		$bean->title = $config->title;
		$bean->subtitle = $config->subtitle;
		$bean->url = $config->url;
		$bean->gaugeMaxType = $config->gaugeMaxType;
		$bean->location = $config->location;
		$bean->latitude = $config->latitude;
		$bean->longitude = $config->longitude;
		$bean->timezone = $config->timezone;
		$bean->debugmode = $config->debugmode;
		$bean->phpMinify = $config->phpMinify;
		
		$bean->comPort = $config->comPort;
		$bean->comOptions = $config->comOptions;
		$bean->comDebug = $config->comDebug;

		$bean->emailFromName = $config->emailFromName;
		$bean->emailFrom = $config->emailFrom;
		$bean->emailTo = $config->emailTo;
		$bean->emailAlarms = $config->emailAlarms;
		$bean->emailEvents = $config->emailEvents;
		$bean->emailReports = $config->emailReports;

		$bean->smtpServer = $config->smtpServer;
		$bean->smtpPort = $config->smtpPort;
		$bean->smtpSecurity = $config->smtpSecurity;
		$bean->smtpUser = $config->smtpUser;
		$bean->smtpPassword = $config->smtpPassword;

		$bean->template = $config->template;
		$bean->aurorapath = $config->aurorapath;
		$bean->smagetpath = $config->smagetpath;
		$bean->smaspotpath = $config->smaspotpath;
		$bean->smaspotWSLpath = $config->smaspotWSLpath;
		$bean->soladinSolgetpath = $config->soladinSolgetpath;
		$bean->deltaSoliviapath = $config->deltaSoliviapath;
		$bean->kostalpikopath = $config->kostalpikopath;
		$bean->plugwiseStrech20IP = $config->plugwiseStrech20IP;
		$bean->plugwiseStrech20ID = $config->plugwiseStrech20ID;
		$bean->smartmeterpath = $config->smartmeterpath;
		$bean->frontendLiveInterval = $config->frontendLiveInterval;
		
		$bean->invoiceDate = $config->invoiceDate;
		
		$bean->co2kwh = $config->co2kwh;
		$bean->co2gas = $config->co2gas;
		$bean->co2CompensationTree = $config->co2CompensationTree;
		$bean->costkwh = $config->costkwh;
		$bean->costGas = $config->costGas;
		$bean->costWater = $config->costWater;
		$bean->moneySign = $config->moneySign;

		$bean->googleAnalytics = $config->googleAnalytics;
		$bean->piwikServerUrl = $config->piwikServerUrl;
		$bean->piwikSiteId = $config->piwikSiteId;
		$bean->cacheInputRate = $config->cacheInputRate;
		
		$bean->adminpasswd = $config->adminpasswd;

		$bean->pauseWorker = $config->pauseWorker;
		$bean->restartWorker = $config->restartWorker;

		$bean->upgradeMessage = $config->upgradeMessage;
		$bean->upgradeMessageShow = $config->upgradeMessageShow;
		
		$bean->useNewCommunication = $config->useNewCommunication;
		
		//Store the bean
		R::store($bean);
	}
	/**
	 *
	 */
	public function readConfig() {
		
		$bean = R::findOne('config');
		
		if (!$bean){
			$bean = R::dispense('config');
		}
		// not used?!
		// TODO
		//$deviceService = new DeviceService();
		
		$config = new Config();
		if ($bean) {
			$config->version_title = $bean->version_title;
			$config->version_revision = $bean->version_revision;
			$config->version_release_time = $bean->version_release_time;
			$config->version_release_description = $bean->version_release_description;
			$config->version_update_time = $bean->version_update_time;
			$config->checkNewTrunk = $bean->checkNewTrunk;
			$config->title = $bean->title;
			$config->subtitle = $bean->subtitle;
			$config->url = ($bean->url != "") ? $bean->url : $config->url;
			$config->location = $bean->location;
			$config->gaugeMaxType = $bean->gaugeMaxType;
			$config->latitude = ($bean->latitude != "") ? $bean->latitude : $config->latitude;
			$config->longitude = ($bean->longitude != "") ? $bean->longitude : $config->longitude;
			$config->timezone = ($bean->timezone != "") ? $bean->timezone : $config->timezone;
			$config->debugmode = ($bean->debugmode != "") ? $bean->debugmode : $config->debugmode;

			$config->comPort = $bean->comPort;
			$config->comOptions = $bean->comOptions;
			$config->comDebug = $bean->comDebug;
			$config->phpMinify = $bean->phpMinify;
		
			
			$config->invoiceDate = $bean->invoiceDate;
			
			$config->emailFromName = $bean->emailFromName;
			$config->emailFrom = $bean->emailFrom;
			$config->emailTo = $bean->emailTo;
			$config->emailAlarms = $bean->emailAlarms;
			$config->emailEvents = $bean->emailEvents;
			$config->emailReports = $bean->emailReports;

			$config->smtpServer = $bean->smtpServer;
			$config->smtpPort = $bean->smtpPort;
			$config->smtpSecurity = $bean->smtpSecurity;
			$config->smtpUser = $bean->smtpUser;
			$config->smtpPassword = $bean->smtpPassword;

			$config->template = ($bean->template != "") ? $bean->template : $config->template;
			$config->aurorapath = ($bean->aurorapath != "") ? $bean->aurorapath : $config->aurorapath;
			$config->mastervoltpath = ($bean->mastervoltpath != "") ? $bean->mastervoltpath : $config->mastervoltpath;
			$config->soladinSolgetpath = ($bean->soladinSolgetpath != "") ? $bean->soladinSolgetpath : $config->soladinSolgetpath;
			$config->deltaSoliviapath = ($bean->deltaSoliviapath != "") ? $bean->deltaSoliviapath : $config->deltaSoliviapath;			
			$config->smagetpath = ($bean->smagetpath != "") ? $bean->smagetpath : $config->smagetpath;
			$config->smaspotpath = ($bean->smaspotpath != "") ? $bean->smaspotpath : $config->smaspotpath;
			$config->smaspotWSLpath = ($bean->smaspotWSLpath != "") ? $bean->smaspotWSLpath : $config->smaspotWSLpath;
			$config->kostalpikopath = ($bean->kostalpikopath != "") ? $bean->kostalpikopath : $config->kostalpikopath;
			$config->plugwiseStrech20IP = ($bean->plugwiseStrech20IP != "") ? $bean->plugwiseStrech20IP : $config->plugwiseStrech20IP;
			$config->plugwiseStrech20ID = ($bean->plugwiseStrech20ID != "") ? $bean->plugwiseStrech20ID : $config->plugwiseStrech20ID;
			$config->smartmeterpath = ($bean->smartmeterpath != "") ? $bean->smartmeterpath : $config->smartmeterpath;

			$config->frontendLiveInterval =($bean->frontendLiveInterval > 0) ? $bean->frontendLiveInterval : $config->frontendLiveInterval; 
			
			$config->co2kwh = ($bean->co2kwh > 0) ? $bean->co2kwh : $config->co2kwh;
			$config->co2gas = ($bean->co2gas > 0) ? $bean->co2gas : $config->co2gas;
			$config->co2CompensationTree = ($bean->co2CompensationTree > 0) ? $bean->co2CompensationTree : $config->co2CompensationTree;
			$config->costkwh = ($bean->costkwh > 0) ? $bean->costkwh : $config->costkwh;
			$config->costGas = ($bean->costGas > 0) ? $bean->costGas : $config->costGas;
			$config->costWater = ($bean->costWater > 0) ? $bean->costWater : $config->costWater;
			$config->moneySign = $bean->moneySign;
			
			$config->devices = $this->deviceService->getActiveDevices();
			
			$config->allDevices = $this->deviceService->getAllDevices();
			
			// looks like we don't use this anymore.
			//$config->inverters = $config->devices; // @Deprecated
			
			// looks like we do not use this......
			//$config->graphSeries = $this->getGraphSeries();
			//$config->graphAxes = $this->getGraphAxes();

			$config->googleAnalytics = $bean->googleAnalytics;
			$config->piwikServerUrl = $bean->piwikServerUrl;
			$config->piwikSiteId = $bean->piwikSiteId;
			
			$config->cacheInputRate =$bean->cacheInputRate;
			
			$config->adminpasswd = ($bean->adminpasswd != "") ? $bean->adminpasswd : $config->adminpasswd;

			$config->pauseWorker = ($bean->pauseWorker != "") ? $bean->pauseWorker : $config->pauseWorker;
			$config->restartWorker = ($bean->restartWorker != "") ? $bean->restartWorker : $config->restartWorker;
			
			$config->upgradeMessage = $bean->upgradeMessage;
			$config->upgradeMessageShow =($bean->upgradeMessageShow != "") ? $bean->upgradeMessageShow : false;
			
			$config->useNewCommunication = ($bean->useNewCommunication != "") ? $bean->useNewCommunication : true;
		}

		return $config;
	}
	
	public function getGraphSeries(){
		$bean = R::findAll('graphSeries');
	}
	
	public function getGraphAxes(){
		$bean = R::findAll('graphAxes');
	}

	/**
	 * Create and run the query to getAll Values for a given Period
	 * @Param string $table
	 * @Param string $type
	 * @Param date $startDate
	 */
	public function readTablesPeriodValues($invtnum, $table, $type, $startDate){
		$count = 0;

		// get the begin and end date/time
		$beginEndDate = Util::getBeginEndDate($type, $count,$startDate);
		
		if ($invtnum > 0){
			$energyBeans = R::getAll("
					SELECT *
					FROM ".$table."
					WHERE INV = :INV AND time > :beginDate AND  time < :endDate
					ORDER BY time LIMIT ". $beginEndDate['days'].";",array(':INV'=>$invtnum,':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
		}else{
			$energyBeans = R::getAll("
					SELECT *
					FROM ".$table."
					WHERE time > :beginDate AND  time < :endDate
					ORDER BY time LIMIT ". $beginEndDate['days'].";",array(':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
		}
		//see if we have atleast 1 bean, else we make one :)
		(!$energyBeans) ? $energyBeans[0] = array('time'=>time(),'KWH'=>0,'KWHT'=>0) : $energyBeans = $energyBeans;
		return $energyBeans;
	}

	/**
	 * Get Max & (summed)Total Energy Values from Energy Tabel
	 * Return a Array() with MaxEnergyDay, MaxEnergyMonth, MaxEnergyYear, MaxEnergyOverall
	 */
	public function getMonthEnergyPerDay($invtnum=0,$startDate){
		$beginEndDate = Util::getBeginEndDate('month', 1,$startDate);

		if ($invtnum>0){
			$beans = R::getAll("
					SELECT INV, time AS date, KWH
					FROM energy WHERE INV = :INV AND time > :beginDate AND time < :endDate 
					ORDER BY time DESC LIMIT ". $beginEndDate['days'],
					array(':INV'=>$invtnum,':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
		}else{
			$beans = R::getAll("
					SELECT INV, time AS date, KWH
					FROM energy  WHERE time > :beginDate AND time < :endDate  
					ORDER BY time DESC LIMIT ". $beginEndDate['days'],
					array(':INV'=>$invtnum,':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
		}
		for ($i = 0; $i < count($beans); $i++) {
			$beans[$i]['KWH'] = number_format($beans[$i]['KWH'],2,',','');
		}
		return $beans;
	}

	/**
	 * Get Max & (summed)Total Energy Values from Energy Tabel
	 * Return a Array() with MaxEnergyDay, MaxEnergyMonth, MaxEnergyYear, MaxEnergyOverall
	 */
	public function getMonthMaxPowerPerDay($invtnum=0,$startDate){
		$beginEndDate = Util::getBeginEndDate('month', 1,$startDate);
		
		if ($invtnum>0){
			$beans = R::getAll("
					SELECT INV, time AS date, GP as maxGP
					FROM pMaxOTD WHERE INV = :INV AND time > :beginDate AND time < :endDate 
					ORDER BY time DESC LIMIT ". $beginEndDate['days'],
					array(':INV'=>$invtnum,':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
		}else{
			$beans = R::getAll("
					SELECT INV, time AS date, GP as maxGP
					FROM pMaxOTD  WHERE time > :beginDate AND time < :endDate  
					ORDER BY time DESC LIMIT ". $beginEndDate['days'],
					array(':INV'=>$invtnum,':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
		}
		for ($i = 0; $i < count($beans); $i++) {
			$beans[$i]['maxGP'] = number_format($beans[$i]['maxGP'],2,',','');
		}
		return $beans;
	}

	/**
	 * Get Max & (summed)Total Energy Values from Energy Tabel
	 * Return a Array() with MaxEnergyDay, MaxEnergyMonth, MaxEnergyYear, MaxEnergyOverall
	 */
	public function getYearMaxEnergyPerMonth($invtnum=0,$date){
		$beginEndDate = Util::getBeginEndDate('year', 1,$date);
		if ($invtnum>0){
			$beans = R::getAll("
					SELECT INV,MAX(kwh) AS KWH, time AS date
					FROM energy
					WHERE INV = :INV AND time > :beginDate AND time < :endDate
					GROUP BY ".$this->crossSQLDateTime("'%m-%Y'",'time','date')."
					ORDER BY time DESC LIMIT ". $beginEndDate['days'],array(':INV'=>$invtnum,':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
		}else{

			$beans = R::getAll("
					SELECT INV,MAX(kwh) AS KWH, time AS date
					FROM energy WHERE time > :beginDate AND time < :endDate
					GROUP BY ".$this->crossSQLDateTime("'%m-%Y'",'time','date')."
					ORDER BY time DESC LIMIT ". $beginEndDate['days'],
					array(':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
		}
		return $beans;
	}


	/**
	 * Get Max & (summed)Total Energy Values from Energy Tabel
	 * Return a Array() with MaxEnergyDay, MaxEnergyMonth, MaxEnergyYear, MaxEnergyOverall
	 */
	public function getYearEnergyPerMonth($invtnum=0,$date){
		$beginEndDate = Util::getBeginEndDate('year', 1,$date);
		if ($invtnum>0){
			$beans = R::getAll("
					SELECT INV, SUM(kWh) AS KWH, time AS date
					FROM energy
					WHERE INV = :INV AND time > :beginDate AND time < :endDate
					GROUP BY ".$this->crossSQLDateTime("'%m-%Y'",'time','date')."
					ORDER BY time DESC",
					array(':INV'=>$invtnum,':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
		}else{
			$beans = R::getAll("
					SELECT INV, SUM(kWh) AS KWH, time AS date
					FROM energy WHERE time > :beginDate AND time < :endDate
					GROUP BY ".$this->crossSQLDateTime("'%m-%Y'",'time','date')."
					ORDER BY time DESC",
					array(':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
		}
		return $beans;
	}










	/**
	 * Get Max & (summed)Total Energy Values from Energy Tabel
	 * Return a Array() with MaxEnergyDay, MaxEnergyMonth, MaxEnergyYear, MaxEnergyOverall
	 */
	public function getMaxMinEnergyYear($invtnum=0,$date){
		$beginEndDate = Util::getBeginEndDate('year', 1,$date);
		if ($invtnum>0){
			$beansMax = R::getRow("
					SELECT INV,max(kWh) as kWh, time AS date
					FROM energy
					WHERE INV = :INV AND time > :beginDate AND time < :endDate
					GROUP BY ".$this->crossSQLDateTime("'%Y'",'time','date')."
					ORDER BY time DESC",
					array(':INV'=>$invtnum,':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
			$beansMin = R::getRow("
					SELECT INV,min(kWh) as kWh, time AS date
					FROM energy
					WHERE INV = :INV AND time > :beginDate AND time < :endDate
					GROUP BY ".$this->crossSQLDateTime("'%Y'",'time','date')."
					ORDER BY time DESC",
					array(':INV'=>$invtnum,':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
			$beansMax['kWh'] = number_format($beansMax['kWh'],2,',','');
			$beansMin['kWh'] = number_format($beansMin['kWh'],2,',','');

			$return = array(
					"maxEnergy"=>$beansMax,
					"minEnergy"=>$beansMin
			);
		}else{
			$beansMax = R::getRow("
					SELECT INV,max(kWh) as kWh, time AS date
					FROM energy
					WHERE  time > :beginDate AND time < :endDate
					GROUP BY ".$this->crossSQLDateTime("'%Y'",'time','date')."
					ORDER BY time DESC",
					array('beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
			$beansMin = R::getRow("
					SELECT INV,min(kWh) as kWh, time AS date
					FROM energy
					WHERE time > :beginDate AND time < :endDate
					GROUP BY ".$this->crossSQLDateTime("'%Y'",'time','date')."
					ORDER BY time DESC",
					array('beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
			for ($i = 0; $i < count($beansMax); $i++) {
				$beansMax[$i]['kWh'] = number_format($beansMax[$i]['kWh'],2,',','');
			}
			for ($i = 0; $i < count($beansMin); $i++) {
				$beansMin[$i]['kWh'] = number_format($beansMin[$i]['kWh'],2,',','');
			}
			$return = array("maxEnergy"=>$beansMax,"minEnergy"=>$beansMin);
		}
		return $return;
	}



















	/**
	 * Get Max & (summed)Total Energy Values from Energy Tabel
	 * Return a Array() with MaxEnergyDay, MaxEnergyMonth, MaxEnergyYear, MaxEnergyOverall
	 */
	public function getMaxMinEnergyMonth($invtnum=0,$date){
		$beginEndDate = Util::getBeginEndDate('month', 1,$date);
		if ($invtnum>0){
			$beansMax = R::getRow("
					SELECT INV,max(kWh) as kWh, time AS date
					FROM energy
					WHERE INV = :INV AND time > :beginDate AND time < :endDate
					ORDER BY time DESC",
					array(':INV'=>$invtnum,':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
			$beansMin = R::getRow("
					SELECT INV,min(kWh) as kWh, time AS date
					FROM energy
					WHERE INV = :INV AND time > :beginDate AND time < :endDate
					ORDER BY time DESC",
					array(':INV'=>$invtnum,':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
			$beansMax['kWh'] = number_format($beansMax['kWh'],2,',','');
			$beansMin['kWh'] = number_format($beansMin['kWh'],2,',','');

			$return = array(
					"maxEnergy"=>$beansMax,
					"minEnergy"=>$beansMin
			);
		}else{
			$beansMax = R::getRow("
					SELECT INV,max(kWh) as kWh, time AS date
					FROM energy
					WHERE  time > :beginDate AND time < :endDate
					ORDER BY time DESC",
					array('beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
			$beansMin = R::getRow("
					SELECT INV,min(kWh) as kWh, time AS date
					FROM energy
					WHERE time > :beginDate AND time < :endDate
					ORDER BY time DESC",
					array('beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
			for ($i = 0; $i < count($beansMax); $i++) {
				$beansMax[$i]['kWh'] = number_format($beansMax[$i]['kWh'],2,',','');
			}
			for ($i = 0; $i < count($beansMin); $i++) {
				$beansMin[$i]['kWh'] = number_format($beansMin[$i]['kWh'],2,',','');
			}
			$return = array("maxEnergy"=>$beansMax,"minEnergy"=>$beansMin);
		}
		return $return;
	}

	/**
	 * Get Max & (summed)Total Energy Values from Energy Tabel
	 * Return a Array() with MaxEnergyDay, MaxEnergyMonth, MaxEnergyYear, MaxEnergyOverall
	 */
	public function getYearMaxPowerPerMonth($invtnum=0,$date){
		$beginEndDate = Util::getBeginEndDate('year', 1,$date);
		if ($invtnum>0){
			$beans = R::getAll("
					SELECT INV,MAX(GP) AS maxGP, time AS date
					FROM pMaxOTD
					WHERE INV = :INV AND  time > :beginDate AND time < :endDate
					GROUP BY ".$this->crossSQLDateTime("'%m-%Y'",'time','date')."
					ORDER BY time DESC",
					array(':INV'=>$invtnum,'beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
		}else{
			$beans = R::getAll("
					SELECT INV,MAX(GP) AS maxGP, time AS date
					FROM pMaxOTD
					WHERE  time > :beginDate AND time < :endDate
					GROUP BY ".$this->crossSQLDateTime("'%m-%Y'",'time','date')."
					ORDER BY time DESC",
					array('beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
		}
		for ($i = 0; $i < count($beans); $i++) {
			$beans[$i]['maxGP'] = number_format($beans[$i]['maxGP'],2,',','');
		}
		return $beans;
	}

	/**
	 * Get Max & (summed)Total Energy Values from Energy Tabel
	 * Return a Array() with MaxEnergyDay, MaxEnergyMonth, MaxEnergyYear, MaxEnergyOverall
	 */
	public function getDayEnergyPerDay($device){
		$beginEndDate = Util::getBeginEndDate('today', 1);

		if ($device->id > 0){
			$beans = R::getAll("SELECT INV,MAX(kwh) AS kWh, time AS date
					FROM energy WHERE INV = :INV  AND time > :beginDate AND time < :endDate
					GROUP BY ".$this->crossSQLDateTime("'d%-%m-%Y'",'time','date')."  
					ORDER BY time DESC",array(':INV'=>$device->id,':endDate'=>$beginEndDate['endDate'],':beginDate'=>$beginEndDate['beginDate']));
		}else{
			$beans = R::getAll("SELECT INV,MAX(kwh) AS kWh, time AS date
					FROM energy WHERE time > :beginDate AND time < :endDate 
					GROUP BY ".$this->crossSQLDateTime("'d%-%m-%Y'",'time','date')." 
					ORDER BY time DESC",array(':endDate'=>$beginEndDate['endDate'],':beginDate'=>$beginEndDate['beginDate']));
		}
		for ($i = 0; $i < count($beans); $i++) {
			$beans[$i]['kWh'] = number_format($beans[$i]['kWh'],2,',','');
			$beans[$i]['name'] = $device->name;
		}
		return $beans;
	}

	/**
	 * Get Max & (summed)Total Energy Values from Energy Tabel
	 * Return a Array() with MaxEnergyDay, MaxEnergyMonth, MaxEnergyYear, MaxEnergyOverall
	 */
	public function getDayMaxPowerPerDay($device){
		$beginEndDate = Util::getBeginEndDate('today', 1);

		if ($device->id > 0){
			$beans = R::getAll("SELECT INV,MAX(GP) AS maxGP, time AS date
					FROM pMaxOTD WHERE INV = :INV AND time > :beginDate AND time < :endDate 
					GROUP BY ".$this->crossSQLDateTime("'d%-%m-%Y'",'time','date')." 
					ORDER BY time DESC",array(':INV'=>$device->id,':endDate'=>$beginEndDate['endDate'],':beginDate'=>$beginEndDate['beginDate']));
		}else{
			$beans = R::getAll("SELECT INV,MAX(GP) AS maxGP, time AS date
					FROM pMaxOTD WHERE time > :beginDate AND time < :endDate 
					GROUP BY ".$this->crossSQLDateTime("'d%-%m-%Y'",'time','date')." 
					ORDER BY time DESC",array(':endDate'=>$beginEndDate['endDate'],':beginDate'=>$beginEndDate['beginDate']));

		}
		for ($i = 0; $i < count($beans); $i++) {
			$beans[$i]['maxGP'] = number_format($beans[$i]['maxGP'],2,',','');
			$beans[$i]['name'] = $device->name;
		}
		return $beans;
	}

	/**
	 *
	 * @param unknown_type $invtnum
	 * @param unknown_type $startDate
	 */

	public function getDetailsHistory($invtnum,$startDate){
		$beginEndDate = Util::getBeginEndDate('day', 1,$startDate);

		if ($invtnum>0){
			$beans = R::getAll("SELECT * FROM History WHERE INV = :INV AND time > :beginDate AND time < :endDate ORDER BY time ASC",
					array(':INV'=>$invtnum,':endDate'=>$beginEndDate['endDate'],':beginDate'=>$beginEndDate['beginDate']));
		}else{
			$beans = R::getAll("SELECT * FROM History WHERE time > :beginDate AND time < :endDate ORDER BY time ASC",
					array(':endDate'=>$beginEndDate['endDate'],':beginDate'=>$beginEndDate['beginDate']));
		}
		
		$historyColumns = R::getColumns('history');
		$labels[]= _('Grid')." "._('Power');
		$labels[]= _('Grid')." "._('Voltage');
		$labels[]= _('Grid')." "._('Amps');
		$labels[]= _('Grid')." "._('Frequency');

		$labels[]= "MPP1 "._('Power');
		$labels[]= "MPP1 "._('Voltage');
		$labels[]= "MPP1 "._('Amps');
		$labels[]= "MPP1 "._('Ratio');

		$labels[]= "MPP2 "._('Power');
		$labels[]= "MPP2 "._('Voltage');;
		$labels[]= "MPP2 "._('Amps');
		$labels[]= "MPP2 "._('Ratio');

		$labels[]= "DC>AC "._('Efficiency');
		$labels[]= "Boos. "._('Temperature');
		$labels[]= "Inv. "._('Temperature');

		// Initialize values
		$max = array();
		$max['P'] = 0;
		$max['V'] = 0;
		$max['A'] = 0;
		$max['FRQ'] = 0;
		$max['Ratio'] = 0;
		$max['T'] = 0;
		$max['EFF'] = 0;
		
		foreach($beans as $bean){
			$bean['time'] =$bean['time'];
			$live->GP[] 	= array($bean['time'],(float)$bean['GP']);
			($bean['GP'] > $max['P'])? $max['P'] = (float)$bean['GP'] : $max['P'] = $max['P'];

			$live->GV[] 	= array($bean['time'],(float)$bean['GV']);
			($bean['GV'] > $max['V'])? $max['V'] = (float)$bean['GV'] : $max['V'] = $max['V'];

			$live->GA[] 	= array($bean['time'],(float)$bean['GA']);
			($bean['GA'] > $max['A'])? $max['A'] = (float)$bean['GA'] : $max['A'] = $max['A'];

			$live->FRQ[] 	= array($bean['time'],(float)$bean['FRQ']);
			($bean['FRQ'] > $max['FRQ'])? $max['FRQ'] = (float)$bean['FRQ'] : $max['FRQ'] = $max['FRQ'];

			$live->I1P[] 	= array($bean['time'],(float)$bean['I1P']);
			($bean['I1P'] > $max['P'])? $max['P'] = (float)$bean['I1P'] : $max['P'] = $max['P'];

			$live->I1V[] 	= array($bean['time'],(float)$bean['I1V']);
			($bean['I1V'] > $max['V'])? $max['V'] = (float)$bean['I1V'] : $max['V'] = $max['V'];

			$live->I1A[] 	= array($bean['time'],(float)$bean['I1A']);
			($bean['I1A'] > $max['A'])? $max['A'] = (float)$bean['I1A'] : $max['A'] = $max['A'];

			$live->I1Ratio[]= array($bean['time'],(float)$bean['I1Ratio']);
			($bean['I1Ratio'] > $max['Ratio'])? $max['Ratio'] = (float)$bean['I1Ratio'] : $max['Ratio'] = $max['Ratio'];

			$live->I2P[] 	= array($bean['time'],(float)$bean['I2P']);
			($bean['I2P'] > $max['P'])? $max['P'] = (float)$bean['I2P'] : $max['P'] = $max['P'];

			$live->I2V[] 	= array($bean['time'],(float)$bean['I2V']);
			($bean['I2V'] > $max['V'])? $max['V'] = (float)$bean['I2V'] : $max['V'] = $max['V'];

			$live->I2A[] 	= array($bean['time'],(float)$bean['I2A']);
			($bean['I2A'] > $max['A'])? $max['A'] = (float)$bean['I2A'] : $max['A'] = $max['A'];

			$live->I2Ratio[]= array($bean['time'],(float)$bean['I2Ratio']);
			($bean['I2Ratio'] > $max['Ratio'])? $max['Ratio'] = (float)$bean['I2Ratio'] : $max['Ratio'] = $max['Ratio'];

			$live->EFF[] 	= array($bean['time'],(float)$bean['EFF']);
			($bean['EFF'] > $max['EFF'])? $max['EFF'] = (float)$bean['EFF'] : $max['EFF'] = $max['EFF'];

			$live->BOOT[]	= array($bean['time'],(float)$bean['BOOT']);
			($bean['BOOT'] > $max['T'])? $max['T'] = (float)$bean['BOOT'] : $max['T'] = $max['T'];

			$live->INVT[] 	= array($bean['time'],(float)$bean['INVT']);
			($bean['INVT'] > $max['T'])? $max['T'] = (float)$bean['INVT'] : $max['T'] = $max['T'];
		}


		if(!$beans){
			$live->GP[] 	= array(time()*1000,0);//0
			$live->GV[] 	= array(time()*1000,0);//1
			$live->GA[] 	= array(time()*1000,0);//2
			$live->GP2[] 	= array(time()*1000,0);//0
			$live->GV2[] 	= array(time()*1000,0);//1
			$live->GA2[] 	= array(time()*1000,0);//2
			$live->GP3[] 	= array(time()*1000,0);//0
			$live->GV3[] 	= array(time()*1000,0);//1
			$live->GA3[] 	= array(time()*1000,0);//2
			$live->FRQ[] 	= array(time()*1000,0);//3
			$live->I1P[] 	= array(time()*1000,0);//4
			$live->I1V[] 	= array(time()*1000,0);//5
			$live->I1A[] 	= array(time()*1000,0);//6
			$live->I1Ratio[]= array(time()*1000,0);//7
			$live->I2P[] 	= array(time()*1000,0);//8
			$live->I2V[] 	= array(time()*1000,0);//9
			$live->I2A[] 	= array(time()*1000,0);//10
			$live->I2Ratio[]= array(time()*1000,0);//11
			$live->I3P[] 	= array(time()*1000,0);//8
			$live->I3V[] 	= array(time()*1000,0);//9
			$live->I3A[] 	= array(time()*1000,0);//10
			$live->I3Ratio[]= array(time()*1000,0);//11
			$live->EFF[] 	= array(time()*1000,0);//12
			$live->BOOT[]	= array(time()*1000,0);//13
			$live->INVT[] 	= array(time()*1000,0);//14
			$max['P'] = 1;
			$max['V'] = 1;
			$max['A'] = 1;
			$max['EFF'] = 1;
			$max['FRQ'] = 1;
			$max['Ratio'] = 1;
			$max['T'] = 1;
		}
		// $switches is not defined set it to null
		return array("details"=>$live,"labels"=>$labels,"switches"=>null,"max"=>$max);
	}

	/**
	 *
	 * @param int $invtnum
	 */
	public function getYearsMonthCompareFilters(){
		$month = array();
		$year = array();
		$config = Session::getConfig();
		
		$i=1;
		$where = '';
		
		foreach ($config->devices as $device){
			if( $i < count($config->devices)){
				$or = " or ";
			}else{
				$or = "";
			}
			$where .= " deviceId = ".$device->id ." ". $or;
			$i++;
		}

		$query = "
				SELECT distinct(".$this->crossSQLDateTime("'%Y'",'time','date').") AS date 
				FROM Energy where " . $where . "
				GROUP BY ".$this->crossSQLDateTime("'%m-%Y'",'time','date')." 
				ORDER BY date ASC";

		$year = R::getAll($query);
		$month[] = array("number"=>1,"name"=>"Jan");
		$month[] = array("number"=>2,"name"=>"Feb");
		$month[] = array("number"=>3,"name"=>"Mar");
		$month[] = array("number"=>4,"name"=>"Apr");
		$month[] = array("number"=>5,"name"=>"May");
		$month[] = array("number"=>6,"name"=>"Jun");
		$month[] = array("number"=>7,"name"=>"Jul");
		$month[] = array("number"=>8,"name"=>"Aug");
		$month[] = array("number"=>9,"name"=>"Sep");
		$month[] = array("number"=>10,"name"=>"Oct");
		$month[] = array("number"=>11,"name"=>"Nov");
		$month[] = array("number"=>12,"name"=>"Dec");
		return array('month'=> $month, 'year'=>$year );
	}

	/**
	 * 
	 * @param unknown $month
	 * @param unknown $year
	 * @return multitype:NULL unknown
	 */
	public function getCompareBeans($invtnum, $month,$year){
		// init
		$counter = 0;
		$dataDays = 0;
		$first = false;
		$getCompareBeans = array();

		// get beans for month and year
		$beans = $this->readEnergyValues($invtnum, 'month', 1, $year."-".$month."-1");
		// lose one array
		$beans = $beans[0];
		foreach ($beans as $bean) {
			$newBeans[strtotime(date("Y-m-d",$bean['time']))] = $bean;  
		}
		// get last KWH value from the Which array
		$dates = $this->datesMonthInArray($month,$year);

		$dataDays = 0;
		foreach ($dates as $key=>$date) {
						
			if(isset($newBeans[$key])){
				$dataDays++;
				$line[] = $newBeans[$key];
			}else{
				if($dataDays == 0){
					// before the first data day
					$line[$counter]['time'] = strtotime($year."/".$month."/".($counter+1));
					$line[$counter]['KWH'] = (float)0;
					$line[$counter]['harvested'] =  sprintf("%01.2f",(float)0);
					$line[$counter]['displayKWH'] =  sprintf("%01.2f",(float)0);
				}else{
					// after the last data day
					$line[$counter]['time'] = strtotime($year."/".$month."/".($counter+1));
					$line[$counter]['KWH'] = $line[$counter-1]['KWH'];
					$line[$counter]['harvested'] =  0;
					$line[$counter]['displayKWH'] =  $line[$counter-1]['displayKWH'];	
				}
			}
			$counter++;
		}
		$getCompareBeans['line']=$line;
		$getCompareBeans['monthDays']=count($dataDays);
		
		return $getCompareBeans;
	}

	/**
	 *
	 * @param unknown_type $invtnum
	 * @param unknown_type $whichMonth
	 * @param unknown_type $whichYear
	 * @param unknown_type $compareMonth
	 * @param unknown_type $compareYear
	 */
	public function getCompareGraph($invtnum,$whichMonth,$whichYear,$compareMonth,$compareYear){
		$beans = array();
		$whichBeans = array();
		$compareBeans = array();
		
		$whichDates = array();
		$compareDates = array();

		if($whichMonth >0 AND $whichYear>0){
			if ($compareYear > 1970){
				// get Which beans
					
				$beans = $this->getCompareBeans($invtnum,$whichMonth,$whichYear);
				$whichBeans = $beans['line'];

				$beans = $this->getCompareBeans($invtnum,$compareMonth,$compareYear);
				$compareBeans = $beans['line'];
				
				// move compareBeans to expectedBeans, so we pass it to JSON.
				$expectedBeans  = $compareBeans;
				$diff = $this->getDiffCompare($whichBeans,$expectedBeans);
				$type = "energy vs energy";
			}else{
				// get Which beans				
				$beans = $this->getCompareBeans($invtnum,$whichMonth,$whichYear);
				$whichBeans = $beans['line'];				

				//get expected beans
				$expectedBeans = $this->expectedMonthProduction($invtnum,$compareMonth);

				$type = "energy vs expected";
				$diff = $this->getDiffCompare($whichBeans,$expectedBeans);
				}
		}

		return array(
				//"whichMonthDays"=>$whichMonthDays,
				//"compareMonthDays"=>$compareMonthDays,
				"compareBeans"=>$this->beansToGraphPoints($expectedBeans),
				"whichBeans"=>$this->beansToGraphPoints($whichBeans),
				"whichCompareDiff"=>$diff,
				"type"=>$type
		);
	}
	

	function expectedMonthProduction($invtnum,$month,$year=0){
		$config = Session::getConfig();

		($year < 1970) ? $year = date("Y") : $year = $year;
			
		$expectedMonthDays =  cal_days_in_month(CAL_GREGORIAN, $month, $year);
		$expectedMonthString = 'expected'.strtoupper(date('M', strtotime($month."/01/".$year)));
		$expectedPerc = $config->getDeviceConfig($invtnum)->$expectedMonthString;
		$expectedkwhYear = $config->getDeviceConfig($invtnum)->expectedkwh;

		// calculate month kWh = (year/100*month perc)
		$expectedKWhMonth = ($expectedkwhYear / 100)*$expectedPerc;

		// calculate daily expected, based on month day (28,29,30,31 days)
		$expectedKwhPerDay = ($expectedKWhMonth/$expectedMonthDays);

		// create expected
		for ($i = 0; $i < $expectedMonthDays; $i++) {
			$iCompareDay = $i+1;
			//($i>0) ? $ii = $i-1 : $ii = 0;
			$ii = $i - 1;
			if ($i == 0) {
				$ii = $i - 1;
				$expectedBeans[$ii]['KWH'] = 0;
			}
			$expectedBeans[$i]['time'] = strtotime(date("Y")."/".$month."/".$iCompareDay);
			$expectedBeans[$i]['KWH'] =  (float)number_format($expectedBeans[$ii]['KWH']+$expectedKwhPerDay,2,'.','');
			$expectedBeans[$i]['displayKWH'] =  sprintf("%01.2f",(float)$expectedBeans[$ii]['KWH']+(float)$expectedKwhPerDay);
			$expectedBeans[$i]['harvested'] = (float)number_format((float)$expectedKwhPerDay,2,'.','');
		}
		return $expectedBeans;
	}

	/**
	 * 
	 * @param string $month
	 * @param string $year
	 * @return multitype:unknown
	 */
	function datesMonthInArray($month,$year){
		$num = cal_days_in_month(CAL_GREGORIAN, $month, $year);
		$datesMonth=array();
		for($i=0;$i<$num;$i++){
			$datesMonth[strtotime(date(($i+1)."-".$month."-".$year))]=null;
	}
		return $datesMonth;
	}


	public function getDiffCompare($whichBeans,$expectedBeans){
		$whichCount = count($whichBeans);
		$expectedCount = count($expectedBeans);
		if($whichCount>=$expectedCount){
			for ($i = 0; $i < $whichCount; $i++) {
				$diffCumCalc = $whichBeans[$i]['KWH']-$expectedBeans[$i]['KWH'];
				$diffDailyCalc = $whichBeans[$i]['harvested']-$expectedBeans[$i]['harvested'];
				
				$diffcolor = $this->rangeBetweenColor($diffCumCalc, $expectedBeans[0]['KWH']);
				$diffHarvestedDayColor = $this->rangeBetweenColor($diffDailyCalc, round($expectedBeans[0]['KWH']*0.2,2));

				$diff[] = array("diffCumCalc"=>sprintf("%01.2f",(float)$diffCumCalc),"diffDailyCalc"=>$diffDailyCalc,'diffColor'=>$diffcolor,'diffHarvestedColor'=>$diffHarvestedDayColor);
			}
		}else{
			for ($i = 0; $i < $expectedCount; $i++) {
				if (isset($whichBeans[$i]) && isset($expectedBeans[$i])) {
					$diffCumCalc = $whichBeans[$i]['KWH']-$expectedBeans[$i]['KWH'];
					$diffDailyCalc = $whichBeans[$i]['harvested']-$expectedBeans[$i]['harvested'];
	
					$diffcolor = $this->rangeBetweenColor($diffCumCalc, $expectedBeans[0]['KWH']);
					$diffHarvestedDayColor = $this->rangeBetweenColor($diffDailyCalc, round($expectedBeans[0]['KWH']*0.2,2));

					$diff[] = array("diffCumCalc"=>sprintf("%01.2f",(float)$diffCumCalc),"diffDailyCalc"=>$diffDailyCalc,'diffColor'=>$diffcolor,'diffHarvestedColor'=>$diffHarvestedDayColor);
				}
			}

		}
		return $diff;
	}
	
	/**
	 * 
	 * @param unknown $compare
	 * @param unknown $to
	 * @param unknown $colors
	 * @return unknown
	 */
	public function rangeBetweenColor($compare, $to, $colors = array('orange','green','red')){
		if(($compare <= $to  AND $compare >= 0) OR ($compare<=0 AND $compare >= (-1 * abs($to)))){
			$var= $colors[0];
		}elseif($compare >= $to){
			$var= $colors[1];
		}else{
			$var= $colors[2];
		}
		return $var;
	}

	/**
	 * Get Max & (summed)Total Energy Values from Energy Tabel
	 * Return a Array() with MaxEnergyDay, MaxEnergyMonth, MaxEnergyYear, MaxEnergyOverall
	 */
	public function getYearSumPowerPerMonth($invtnum,$startDate){
		$device = array();
		$startDate = '01-01-'.$startDate;
		$beginEndDate = Util::getBeginEndDate('year', 1,$startDate);
		if ($invtnum>0){
			$query = "SELECT INV, SUM(KWH) as KWH, time 
					FROM Energy WHERE INV = :INV AND time > :beginDate AND time < :endDate 
					GROUP BY ".$this->crossSQLDateTime("'%m-%Y'",'time','date')." 
					ORDER BY time ASC";
			$beans = R::getAll($query,
					array(':INV'=>$invtnum,':endDate'=>$beginEndDate['endDate'],':beginDate'=>$beginEndDate['beginDate']));
		}else{
			$beans = R::getAll("SELECT INV, SUM(KWH) as KWH, time
					FROM Energy WHERE time > :beginDate AND time < :endDate 
					GROUP BY ".$this->crossSQLDateTime("'%m-%Y'",'time','date')." 
					ORDER BY time ASC",
					array(':endDate'=>$beginEndDate['endDate'],':beginDate'=>$beginEndDate['beginDate']));
		}
		
		if(count($beans)==0){
			$newBean = null;
		}else{
			$firstMonth = date("n",$beans[0]['time']+700);
			$lastMonth = date("n",$beans[count($beans)-1]['time']);
			$device = $this->deviceService->load($invtnum);
			$cumExp = 0;
			$cumKWH = 0;

			$expected = $device->expectedkwh;
			$invExp[0] = ($expected/100)*$device->expectedJAN;
			$invExp[1] = ($expected/100)*$device->expectedFEB;
			$invExp[2] = ($expected/100)*$device->expectedMAR;
			$invExp[3] = ($expected/100)*$device->expectedAPR;
			$invExp[4] = ($expected/100)*$device->expectedMAY;
			$invExp[5] = ($expected/100)*$device->expectedJUN;
			$invExp[6] = ($expected/100)*$device->expectedJUL;
			$invExp[7] = ($expected/100)*$device->expectedAUG;
			$invExp[8] = ($expected/100)*$device->expectedSEP;
			$invExp[9] = ($expected/100)*$device->expectedOCT;
			$invExp[10] = ($expected/100)*$device->expectedNOV;
			$invExp[11] = ($expected/100)*$device->expectedDEC;

			
			// prepend months 1-4 to 5-8
			$unshiftMonths = $firstMonth-2;
			for ($i = 0; $i < $unshiftMonths; $i++) {
				array_unshift($beans, array(
				"time" => (int)strtotime(date("01-".($unshiftMonths-$i)."-".date("Y",strtotime($startDate)))),
				"KWH"=>(int)0,
				"monthNumber"=>($unshiftMonths-$i),
				"Exp" => number_format(0,0,',',''),
				"cumExp"=>number_format(0,0,',',''),
				"cumDiff"=>0,
				"Diff"=>0,
				"cumKWH"=>0,
				)
				);
			}
				

			//months 5-8 from database
			for ($i = $i; $i < count($beans); $i++) {
				$beans[$i]['monthNumber'] =  date("n",$beans[$i]['time']);
				$beans[$i]['KWH'] = number_format($beans[$i]['KWH'],0,',','');
				// if previous bean == 0 and current bean > 0 and we are not at bean 0
				// we got a partial month
				if($beans[$i-1]['KWH'] == 0 && $beans[$i]['KWH'] > 0 && $i > 0){
					$currentMonth = $i+1;
					$beginEndDate = Util::getBeginEndDate('month', 1,date("01-".$currentMonth."-".date("Y",strtotime($startDate))));
					
					$getPartialMonthDayCount = R::getAll("SELECT id
											FROM Energy WHERE INV = :INV AND time > :beginDate AND time < :endDate
											ORDER BY time ASC",
							array(':INV'=>$invtnum,':endDate'=>$beginEndDate['endDate'],':beginDate'=>$beginEndDate['beginDate']));
						
					$expectedPartialMonth = $invExp[$i] / cal_days_in_month(CAL_GREGORIAN, $currentMonth , date("Y",strtotime($startDate)));
					$expected = ($expectedPartialMonth * count($getPartialMonthDayCount));
				}else{
					$expected = $invExp[$beans[$i]['monthNumber']-1];
				}

				$cumExp += $expected;
				$cumKWH += $beans[$i]['KWH'];
				
				$beans[$i]['Exp'] = number_format($expected,0,',','');
				$beans[$i]['cumDiff'] = number_format($cumKWH-$cumExp,0,',','');
				$beans[$i]['Diff'] = number_format($beans[$i]['KWH']-$beans[$i]['Exp'],0,',','');
				
				$beans[$i]['cumKWH'] = number_format($cumKWH,0,',','');
				$beans[$i]['cumExp'] = number_format($cumExp,0,',','');
			}
			
			
			$lastMonth = end($beans);
			// append months 5-8 to 9-12 
			for ($i = count($beans)+1; $i <= 12; $i++) {
				$expected = $invExp[count($beans)];

				$cumExp += $expected;
				$cumKWH = $lastMonth['cumKWH'];
				array_push($beans, array(
						"KWH"			=>(int)0,
						"time"			=> (int)strtotime(date("01-".($i)."-".date("Y",strtotime($startDate)))),
						"monthNumber"	=>($i),
						"Diff" 			=> number_format(0-$expected,0,',',''),
						"Exp" 			=> number_format($expected,0,',',''),
						"cumExp" 		=> number_format($cumExp,0,',',''),
						"cumKWH" 		=> number_format($cumKWH,0,',',''),
						"cumDiff" 		=> number_format($cumKWH-$cumExp,0,',','')
					)
				);
			}
		}
		return array("energy"=>$this->CompareBeansToGraphPoints($beans),"expected"=>$expected);
	}


	/**
	 * Get Max & (summed)Total Energy Values from Energy Tabel
	 * Return a Array() with MaxEnergyDay, MaxEnergyMonth, MaxEnergyYear, MaxEnergyOverall
	 */
	public function getDayHistoryPerRecord($deviceId,$config){
		$beginEndDate = Util::getBeginEndDate('today', 1);
		//var_dump($parameters);
		
		if($deviceId>0){
			$parameters = array(':deviceId'=>$deviceId,':endDate'=>$beginEndDate['endDate'],':beginDate'=>$beginEndDate['beginDate']);			
			$beans[] = R::getAll("SELECT deviceId, GP,KWHT, time AS date
				FROM history
				WHERE deviceId = :deviceId  AND time > :beginDate AND time < :endDate
				ORDER BY time DESC",$parameters);
		}else{
			foreach($config->devices as $device){
				
				if($device->type=="production"){
					$parameters = array(':deviceId'=>$device->id,':endDate'=>$beginEndDate['endDate'],':beginDate'=>$beginEndDate['beginDate']);
					
					$beans = R::getAll("SELECT deviceId, GP,KWHT, time AS date
						FROM history
						WHERE deviceId = :deviceId  AND time > :beginDate AND time < :endDate
						ORDER BY time DESC",$parameters);
					$newBeans = array();
					foreach ($beans as $key => &$value) {
  						$newBeans[$key]['GP'] = number_format($beans[$key]['GP'],2,',','');
  						$newBeans[$key]['date'] = $beans[$key]['date'];
  						
					}
					$result[] = array('deviceName'=>$device->name, "data"=>$newBeans);
				}
			}
		}
		


		
		return $result;
	}



	/**
	 * return a array with GraphPoints
	 * @param date $startDate ("Y-m-d") ("1900-12-31"), when no date given, the date of today is used.
	 * @return array($beginDate, $endDate);
	 */
	public function getGraphDayPoint($invtnum,$type, $startDate,$config){
		($type == 'today')?$type='day':$type=$type;
		$graph = new Graph();


		$beans = $this->readTablesPeriodValues($invtnum, 'history', $type, $startDate);
		
		
		$graph->axes['xaxis'] = array('label'=>'','renderer'=>'DateAxisRenderer',
				'tickRenderer'=>'CanvasAxisTickRenderer','labelRenderer'=>'CanvasAxisLabelRenderer',
				'tickInterval'=>3600,'tickOptions'=>array('formatter'=>'DayDateTickFormatter','angle'=>-45));

		$graph = $this->DayBeansToGraphPoints($beans,$graph,$startDate,$config);
		

		$hookGraph = HookHandler::getInstance()->fire("GraphDayPoints",$invtnum,$startDate,$type);
		
		if($hookGraph->axes!=null){
			foreach ($hookGraph->axes as $key => $value){
				$graph->axes[$key] = $value;
			}
		}
		if($hookGraph->series!=null){
			foreach($hookGraph->series as $series){
				$graph->series[] = $series;
			}
		}
		if($hookGraph->points!=null){
			foreach ($hookGraph->points as $key => $value){
				$graph->points[$key] = $value;
			}
		}

		// if timestamp of the hook is not null, we override the current timestamp
		if($hookGraph->timestamp!=null){
			$graph->timestamp = $hookGraph->timestamp;
		}

		
		if($hookGraph->metaData != null){
			if(isset($graph->metaData['hideSeries']['label']) && count($graph->metaData['hideSeries']['label']) == count($graph->series)){
				$graph->metaData['hideSeries']=null;
			}
			$graph->metaData= array_merge_recursive((array)$hookGraph->metaData,(array)$graph->metaData);
			
			// if metaData['legend'] of the hook is not null, we override the current metaData['legend']
			if($hookGraph->metaData['legend']!=null){
				$graph->metaData['legend'] = $hookGraph->metaData['legend'];
			}
		}
		
		$array['graph'] = $graph;


		return $array;
	}

	/**
	 * return a array with GraphPoints
	 * @param date $startDate ("Y-m-d") ("1900-12-31"), when no date given, the date of today is used.
	 * @return array($beginDate, $endDate);
	 */
	public function getGraphPoint($invtnum,$type, $startDate){
		($type == 'today')?$type='day':$type=$type;
		// if $type
		(stristr(strtolower($type), 'day') === FALSE) ?	$table = "energy" : $table = "history";


		$beans = $this->readTablesPeriodValues($invtnum, $table, $type, $startDate);
		if(strtolower($table) == "history"){
			// NO history bean? Create a dummy bean...
			(!$beans) ? $beans[0] = array('time'=>time(),'KWH'=>0,'KWHT'=>0) : $beans = $beans;
			return $this->DayBeansToGraphPoints($beans);
		}else{
			return $this->PeriodBeansToGraphPoints($beans,$type,$startDate);
		}
	}


	/**
	 * return a array that can be understand by JQplot
	 * @param array $beans from $this->getGraphPoint()
	 * @return array($beginDate, $endDate);
	 */
	public function beansToGraphPoints($beans){
		$points = array();
		$cumPower = 0;

		foreach ($beans as $bean){
			//$cumPower += $bean['KWH'];
			//echo mktime(0, 0, 0,date("m",$bean['time']),date("d",$bean['time']),date("Y",$bean['time']))."   ";
			if (isset($bean['time'])) {
				$points[] = array (
						mktime(0, 0, 0,date("m",$bean['time']),date("d",$bean['time']),date("Y",$bean['time'])),
						date("d-m-Y",$bean['time']),
						$bean['KWH'],
						$bean['displayKWH'],
						$bean['harvested']
				);
			}
		}
		//number_format($cumPower,2,'.',''),
		// if no data was found, create 1 dummy point for the graph to render
		if(count($points)==0){
			$cumPower = 0;
			$points[] = array (time(), 0,0);
		}

		$lastDays = new LastDays();
		$lastDays->points=$points;
		$lastDays->KWHT=$cumPower;
		return $lastDays;
	}




	/**
	 * return a array that can be understand by JQplot
	 * @param array $beans from $this->getGraphPoint()
	 * @return array($beginDate, $endDate);
	 */
	public function CompareBeansToGraphPoints($beans){
		$points = array();
		$cumPower = 0;
		foreach ($beans as $bean){
			$cumPower += $bean['KWH'];
			$points[] = array (
					//$bean['time'],
					//mktime(0, 0, 0,date("m",$bean['time']),1,date("Y",$bean['time'])),
					floatval($bean['KWH']),
					"1-".date("m-Y",$bean['time']),
					floatval($bean['Exp']),
					floatval($bean['Diff']),//
					floatval($bean['cumExp']),
					floatval($bean['cumKWH']),//
					floatval($bean['cumDiff']),//
					$bean['time']
			);
		}

		// if no data was found, create 1 dummy point for the graph to render
		if(count($points)==0){
			$cumPower = 0;
			$points[] = array (time(), 0,0);
		}

		$lastDays = new LastDays();
		$lastDays->points=$points;
		$lastDays->KWHT=$cumPower;
		return $lastDays;
	}


	/**
	 * return a array that can be understand by JQplot
	 * @param array $beans from $this->getGraphPoint()
	 * @return array($beginDate, $endDate);
	 */
	public function PeriodBeansToGraphPoints($beans,$period,$date){
		$points = array();
		$cumPower=0;
		foreach ($beans as $bean){
			$cumPower += $bean['KWH'];
			$points[] = array (
					$bean['time'],
					floatval($bean['KWH']),
					floatval($cumPower)
			);
		}

		// if no data was found, create 1 dummy point for the graph to render
		if(count($points)==0){
			$cumPower = 0;
			$points[] = array (time(), 0,0);
		}else{
			if (strtolower($period) == 'month'){
				$endOfMonth =Util::getTimestampOfDate(0,0,0,  date('t'),date("m",strtotime($date)), date("Y"));
			}else{
				$beginEndWeek = Util::getStartAndEndOfWeek(strtotime($date));
				$endOfMonth = $beginEndWeek[1];
			}
			$countPoints = count($points)-1;

			$time = strtotime(date("d-m-Y",($points[$countPoints][0])));
			$time += 86400;
			while ($time<$endOfMonth){
				$time += 86400;
				$points[] = array (
						($time),
						0,
						date("Y-m-d",$time),
						floatval($cumPower)
				);
			}
		}

		$lastDays = new LastDays();
		$lastDays->points=$points;
		$lastDays->KWHT=$cumPower;
		return $lastDays;
	}



	/**
	 * read Energy Values
	 * @param int $invtnum
	 * @param str $type can be: today, week, month, year
	 * @param int $count
	 * @param date $startDate
	 * @param str $maxType
	 *
	 */
	public function readEnergyValues($invtnum, $type, $count, $startDate){
		$config = Session::getConfig();
		$energyBeans = $this->readTablesPeriodValues($invtnum, "energy", $type,$startDate);
		$Energy = array();

		$Energy['KWH'] = 0;
		$KWHT = 0;
		$cum = 0;
		foreach ($energyBeans as $energyBean){
			$invConfig = $this->deviceService->load($energyBean['INV']);
			if($invConfig->id > 0){
			
			$energyBean['KWH'] = (float)$energyBean['KWH'];
			$Energy['index'] = date("d",$energyBean['time'])-1;
			$Energy['date'] = date("Y-m-d",$energyBean['time']);
			$Energy['INV'] =  $energyBean['INV'];
			$Energy['KWHKWP'] = number_format($energyBean['KWH'] / ($invConfig->plantpower/1000),2,',','');
			$Energy['harvested'] = number_format((float)$energyBean['KWH'],2,'.','');
			$Energy['KWH'] += number_format((float)$energyBean['KWH'],2,'.','');

			$cum +=$energyBean['KWH'];
			$Energy['displayKWH'] = sprintf("%01.2f",(float)$cum);
			$Energy['CO2'] =Formulas::CO2kWh($energyBean['KWH'],$config->co2kwh);
			$Energy['time'] = strtotime(date("Y-m-d",$energyBean['time']));
			$Energy['KWHT'] = number_format($energyBean['KWHT'],2,',','');
			$KWHT += $energyBean['KWH'];
			}
			$energy[] = $Energy;
		}
		return array($energy,$KWHT);
	}
	/**
	 *
	 * @param unknown_type $invtnum
	 * @param unknown_type $type
	 * @param unknown_type $count
	 * @param unknown_type $startDate
	 */
	public function readPmaxValues($invtnum, $type, $count, $startDate){

		$beans = $this->readTablesPeriodValues($invtnum, "pMaxOTD", $type,$startDate);

		foreach ($beans as $bean){
			$oMaxPowerToday = new MaxPowerToday();
			$oMaxPowerToday->GP =  number_format($bean['GP'],2,',','');
			$oMaxPowerToday->INV = $bean['INV'];
			$oMaxPowerToday->time = date("H:i:s d-m-Y",$bean['time']);;
			$maxPowerDay[] = $oMaxPowerToday;
		}
		return array($maxPowerDay);
	}

	/**
	 *
	 */
	public function readPageIndexData() {
		// summary live data
		$list = array();
		$list['summary'] = $this->readCache(1,"index","periodFigures",0,"");

		return $list;
	}
	
	/**
	 *
	 * @param string $group
	 * @param string $page
	 * @param string $module
	 * @return unknown|Ambigous <multitype:, unknown>
	 */

	//(1,"index","live",$device->id,"trend");

	public function readCache($group="",$page="",$module="",$deviceId=0,$key=""){
		$where = array();
		//
		// build Query finds
		//
			
		//
		//    $module
		//
		//check if we are looking for a $module
		if ($module != ""){
			(stristr($module, '%')!='')?$findLike = ' LIKE ' : $findLike = '=';
			$where[] = " module ".$findLike." '". $module ."' ";
		}
		//check if we need to add a 'AND'
		($where[count($where)-1]!='' AND $where[count($where)-1]!=' AND ')? $where[] = ' AND ' : $where = $where;

		//
		//    $page
		//
		//check if we are looking for a $page
		if ($page != ""){
			(stristr($page, '%')!='')?$findLike = ' LIKE ' : $findLike = '=';
			$where[] = " page ".$findLike." '". $page."' ";
		}
		// check if we need to add a 'AND'
		($where[count($where)-1]!='' AND $where[count($where)-1]!=' AND ')? $where[] = ' AND ' : $where = $where;

		//
		//    $deviceId
		//
		//check if we are looking for a $page
		if ($deviceId != ""){
			$where[] = " `key` LIKE '%-". $deviceId."' ";
		}
		//check if we need to add a 'AND'
		($where[count($where)-1]!='' AND $where[count($where)-1]!=' AND ')? $where[] = ' AND ' : $where = $where;


		//
		//    $deviceId
		//
		//check if we are looking for a $page
		if ($deviceId != ""){
			$where[] = " `key` like '". $key."-%' ";
		}

		//when we end with a " AND ", we pop it of.
		(end($where)==" AND ")?  array_pop($where) : $where = $where;

		//convert the array in a string
		$whereString = "";
		foreach ($where as $value) {
			$whereString = $whereString." ".$value;
		}

		//make the query
		$query = "SELECT `key`,value,page,module FROM cache WHERE ".$whereString." ORDER BY `key` ASC";

		//run the query
		$cached =  R::getAll($query);

		//when we want to group the data, we do it here
		if($group!=''){
			// loop through all the items
			foreach ($cached as $cache){
				//split them
				preg_match_all('/((?:^|[A-Z])[a-z]+)/',$cache['key'],$camleKeys);
				//group the values by there Camle Key
				$grouped[$camleKeys[$group][0]][$cache['key']] = $cache['value'];
			}
			// return the grouped array
			return $grouped;
		}else{
			// return the plain db array
			return $cached;
		}
	}



	/**
	 *
	 */
	public function readPageIndexLiveValues($config) {
		// summary live data
		$devices = array();
		$GP  = 0;
		$GP2 = 0;
		$GP3 = 0;
		$I1P = 0;
		$I2P = 0;
		$I3P = 0;
		$IP  = 0;
		$EFF = 0;
		$totalSystemIP = 0;
		$totalSystemACP = 0;

		$liveBean = array();

		foreach ($config->devices as $device){			
			if($device->type=="production"){
				// TODO
				// We probably do not want to run this query, because its also in the "else" below so we do this dubble
				//$liveBean =  R::findOne('live',' INV = :INV ', array(':INV'=>$device->id));
				$oDevice = 	array();
				
				// if sun is down AND voltage of string1 is 0, then we are down.
				// start running 300 sec (5 min) before/after sun up/down.
				if(Util::isSunDown(-300)){
					$live = new Live();
					$live->name = $device->name;
					$live->status = 'offline';
					$live->time = date("H:i:s");
					$live->INV = $device->id;
					$live->totalDeviceIP = 0;
					$live->totalDeviceACP =0;
					$live->GP = number_format(0,0,',','');
					$live->GA = number_format(0,0,',','');
					$live->GV = number_format(0,0,',','');
					$live->I1P = number_format(0,2,',','');
					$live->I1A = number_format(0,2,',','');
					$live->I1V = number_format(0,2,',','');
					$live->I1Ratio = number_format(0,2,',','');
					$live->I2P = number_format(0,2,',','');
					$live->I2A = number_format(0,2,',','');
					$live->I2V = number_format(0,2,',','');
					$live->I2Ratio = number_format(0,2,',','');
					$live->I3P = number_format(0,2,',','');
					$live->I3A = number_format(0,2,',','');
					$live->I3V = number_format(0,2,',','');
					$live->I3Ratio = number_format(0,2,',','');

					$live->IP = number_format(0,2,',','');
					$live->EFF = number_format(0,2,',','');
					$live->trendImage = "equal";
					$live->trend = _("equal");
					$live->avgPower = number_format(0,2,',','');
				}else{
					$liveBean =  R::findOne('live',' deviceId = :deviceId ', array(':deviceId'=>$device->id));
					/*
					$GP += $liveBean['GP'];
					$GP2 += $liveBean['GP2'];
					$GP3 += $liveBean['GP3'];
					
					$I1P += $liveBean['I1P'];
					$I2P += $liveBean['I2P'];
					$I3P += $liveBean['I3P'];*/

					// sum system (all devices) power values
					$totalSystemIP  += $liveBean['I1P'] + $liveBean['I2P'] + $liveBean['I3P'];
					$totalSystemACP  += $liveBean['GP'] + $liveBean['GP2'] + $liveBean['GP3'];


					$live = new Live();
					$live->name = $device->name;
					$live->status = 'online';
					$live->time = date("H:i:s",$liveBean['time']);

					// sum device power values
					$live->totalDeviceIP  = round($liveBean['I1P'] + $liveBean['I2P'] + $liveBean['I3P'],2);
					$live->totalDeviceACP  = round($liveBean['GP'] + $liveBean['GP2'] + $liveBean['GP3'],2);
										
					$live->INV = $liveBean['INV'];
					$live->GP = ($liveBean['GP']<1000) ? number_format($liveBean['GP'],1,'.','') : number_format($liveBean['GP'],0,'','');
					$live->GA = ($liveBean['GA']<1000) ? number_format($liveBean['GA'],1,'.','') : number_format($liveBean['GA'],0,'','');
					$live->GV = ($liveBean['GV']<1000) ? number_format($liveBean['GV'],1,'.','') : number_format($liveBean['GV'],0,'','');

					$live->GP2 = ($liveBean['GP2']<1000) ? number_format($liveBean['GP2'],1,'.','') : number_format($liveBean['GP2'],0,'','');
					$live->GA2 = ($liveBean['GA2']<1000) ? number_format($liveBean['GA2'],1,'.','') : number_format($liveBean['GA2'],0,'','');
					$live->GV2 = ($liveBean['GV2']<1000) ? number_format($liveBean['GV2'],1,'.','') : number_format($liveBean['GV2'],0,'','');
					
					$live->GP3 = ($liveBean['GP3']<1000) ? number_format($liveBean['GP3'],1,'.','') : number_format($liveBean['GP3'],0,'','');
					$live->GA3 = ($liveBean['GA3']<1000) ? number_format($liveBean['GA3'],1,'.','') : number_format($liveBean['GA3'],0,'','');
					$live->GV3 = ($liveBean['GV3']<1000) ? number_format($liveBean['GV3'],1,'.','') : number_format($liveBean['GV3'],0,'','');

					$live->I1P = ($liveBean['I1P']<1000) ? number_format($liveBean['I1P'],1,'.','') : number_format($liveBean['I1P'],0,'','');
					$live->I1A = ($liveBean['I1A']<1000) ? number_format($liveBean['I1A'],1,'.','') : number_format($liveBean['I1A'],0,'','');
					$live->I1V = ($liveBean['I1V']<1000) ? number_format($liveBean['I1V'],1,'.','') : number_format($liveBean['I1V'],0,'','');
					$live->I1Ratio = ($liveBean['I1Ratio']<1000) ? number_format($liveBean['I1Ratio'],1,'.','') : number_format($liveBean['I1Ratio'],0,'','');

					$live->I2P = ($liveBean['I2P']<1000) ? number_format($liveBean['I2P'],1,'.','') : number_format($liveBean['I2P'],0,'','');
					$live->I2A = ($liveBean['I2A']<1000) ? number_format($liveBean['I2A'],1,'.','') : number_format($liveBean['I2A'],0,'','');
					$live->I2V = ($liveBean['I2V']<1000) ? number_format($liveBean['I2V'],1,'.','') : number_format($liveBean['I2V'],0,'','');
					$live->I2Ratio = ($liveBean['I2Ratio']<1000) ? number_format($liveBean['I2Ratio'],1,'.','') : number_format($liveBean['I2Ratio'],0,'','');
					
					$live->I3P = ($liveBean['I3P']<1000) ? number_format($liveBean['I3P'],1,'.','') : number_format($liveBean['I3P'],0,'','');
					$live->I3A = ($liveBean['I3A']<1000) ? number_format($liveBean['I3A'],1,'.','') : number_format($liveBean['I3A'],0,'','');
					$live->I3V = ($liveBean['I3V']<1000) ? number_format($liveBean['I3V'],1,'.','') : number_format($liveBean['I3V'],0,'','');
					$live->I3Ratio = ($liveBean['I3Ratio']<1000) ? number_format($liveBean['I3Ratio'],1,'.','') : number_format($liveBean['I3Ratio'],0,'','');
					
					$live->IP = ($liveBean['IP']<1000) ? number_format($liveBean['IP'],1,'.','') : number_format($liveBean['IP'],0,'','');
					$live->ACP = ($liveBean['ACP']<1000) ? number_format($liveBean['ACP'],1,'.','') : number_format($liveBean['ACP'],0,'','');
					$live->EFF = ($liveBean['EFF']<1000) ? number_format($liveBean['EFF'],1,'.','') : number_format($liveBean['EFF'],0,'','');
						
					$avgPower = $this->readCache("","index","live",$device->id,"trend");
					$live->trendImage = $avgPower[0]['value'];
					$live->trend = _($live->trendImage);
				}

				$oDevice["id"] = $device->id;
				$oDevice["currentTime"] = time();
				$oDevice["live"] = $live;

				$devices[] = $oDevice;
			}
		}

		$sum = array();
		/*
		$sum['GP'] = ($GP<1000) ? number_format($GP,1,'.','') : number_format($GP,0,'','');
		$sum['GP2'] = ($GP2<1000) ? number_format($GP2,1,'.','') : number_format($GP2,0,'','');
		$sum['GP3'] = ($GP3<1000) ? number_format($GP3,1,'.','') : number_format($GP3,0,'','');
		$sum['I1P'] = ($I1P<1000) ? number_format($I1P,1,'.','') : number_format($I1P,0,'','');
		$sum['I2P'] = ($I2P<1000) ? number_format($I2P,1,'.','') : number_format($I2P,0,'','');
		$sum['I3P'] = ($I3P<1000) ? number_format($I3P,1,'.','') : number_format($I3P,0,'','');*/
		$sum['totalSystemACP'] = ($totalSystemACP<1000) ? number_format($totalSystemACP,1,'.','') : number_format($totalSystemACP,0,'','');
		$sum['totalSystemIP'] = ($totalSystemIP<1000) ? number_format($totalSystemIP,1,'.','') : number_format($totalSystemIP,0,'','');
		$totalSystemEff = 0;
		if($totalSystemIP>0 AND $totalSystemIP>0){
			$totalSystemEff = ($totalSystemACP / $totalSystemIP) * 100;
		}
		
		$sum['EFF'] = ($totalSystemEff>0) ? number_format($totalSystemEff,1,'.','') : '0,0';
		return array('devices'=>$devices,'sum'=>$sum);
	}

	/**
	 * Get Max & (summed)Total Energy Values from Energy Tabel
	 * Return a Array() with MaxEnergyDay, MaxEnergyMonth, MaxEnergyYear, MaxEnergyOverall
	 */
	public function getMaxTotalEnergyValues($invtnum,$type,$config,$limit=1){
		// get config
		// We now get is from the parent
		//$config = Session::getConfig();
		// init var
		$sumPlantPower = $this->readPlantPower()/1000;
		//echo "SELECT INV,COUNT(kwh) as countkWh,MAX(kwh) AS kWh, SUM(kwh) AS sumkWh, time AS date FROM energy WHERE INV = :INV GROUP BY ".$this->crossSQLDateTime("'%m-%Y'",'time','date')." ORDER BY time DESC limit 0,1";
		// type to lowercase
		$type = strtolower($type);
		// init array
		$avgEnergyBeansToday = 0;
		// init var
		$initialkwh = 0;


		if($type == "today" || $type == "day" || $type == "all"){
			$totalEnergyBeansToday = $this->readTablesPeriodValues(0, "energy", "today", date("d-m-Y"));
			$maxPowerBeansToday = $this->readTablesPeriodValues(0, "pMaxOTD", "today", date("d-m-Y"));

			if (count ( $maxPowerBeansToday )==0 ){
				$avgEnergyBeansToday= number_format(0,3,',','');
				$totalEnergyBeansToday[]['KWH']=0;
				$totalEnergyBeansTodayKWHKWP = number_format('0.000',3,',','');
			}else{
				$totalEnergyBeansTodayKWHKWP= number_format(($totalEnergyBeansToday[0]['KWH'] / $sumPlantPower),3,',','');
				for ($i = 0; $i < count($totalEnergyBeansToday); $i++) {
					//$maxPowerBeansToday[$i]['sumkWh'] = number_format($maxPowerBeansToday[$i]['sumkWh'],2,',','');
					$avgEnergyBeansToday += $totalEnergyBeansToday[$i]['KWH'];
					$totalEnergyBeansToday[$i]['KWH'] = number_format($totalEnergyBeansToday[$i]['KWH'],3,',','');
				}
				$avgEnergyBeansToday = number_format($avgEnergyBeansToday,3,',','');
			}

			if(count ( $maxPowerBeansToday )==0 ){
				$maxPowerBeansToday[]['GP']="0";
			}
		}

		if($type == "week" || $type == "all"){
			$totalEnergyBeansWeek = R::getAll("
					SELECT COUNT(kwh) as countkWh,MAX(kwh) AS kWh, SUM(kwh) AS sumkWh,
					 ".$this->crossSQLDateTime("'%Y%W'",'time','date')." AS date 
					FROM energy GROUP BY date 
					ORDER BY time DESC limit 0,:limit",array(':limit'=>$limit));
			if(count($totalEnergyBeansWeek)>0){
				$avgEnergyBeansWeek = number_format($totalEnergyBeansWeek[0]['sumkWh']/$totalEnergyBeansWeek[0]['countkWh'],2,',','');

				for ($i = 0; $i < count($totalEnergyBeansWeek); $i++) {
					$totalEnergyBeansWeek[$i]['sumkWh'] = number_format($totalEnergyBeansWeek[$i]['sumkWh'],2,',','');
				}
				$totalEnergyBeansWeekKWHKWP= number_format($totalEnergyBeansWeek[0]['sumkWh'] / $sumPlantPower,2,',','');
			}else{
				$totalEnergyBeansWeekKWHKWP= number_format('0',2,',','');
			}
		}

		if($type == "month" ||  $type == "all"){
			
			if ($invtnum>0){
				$totalEnergyBeansMonth = R::getAll("
						SELECT INV,COUNT(kwh) as countkWh,MAX(kwh) AS kWh, SUM(kwh) AS sumkWh, time AS date 
						FROM energy 
						WHERE INV = :INV 
						GROUP BY ".$this->crossSQLDateTime("'%m-%Y'",'time','date')." 
						ORDER BY time DESC limit 0,:limit",array(':limit'=>$limit,':INV'=>$invtnum));
				$avgEnergyBeansMonth = number_format($totalEnergyBeansMonth[0]['sumkWh']/$totalEnergyBeansMonth[0]['countkWh'],2,',','');
				for ($i = 0; $i < count($totalEnergyBeansMonth); $i++) {
					$totalEnergyBeansMonth[$i]['sumkWh'] = number_format($totalEnergyBeansMonth[$i]['sumkWh'],2,',','');
				}
			}else{
				$totalEnergyBeansMonth = R::getAll("SELECT INV,COUNT(kwh) as countkWh, MAX(kwh) AS kWh, SUM(kwh) AS sumkWh, time AS date 
						FROM energy 
						GROUP BY ".$this->crossSQLDateTime("'%m-%Y'",'time','date')." 
						ORDER BY time DESC limit 0,:limit",array(':limit'=>$limit));
				if(count($totalEnergyBeansMonth)>0){
					$avgEnergyBeansMonth = number_format($totalEnergyBeansMonth[0]['sumkWh']/$totalEnergyBeansMonth[0]['countkWh'],2,',','');
					for ($i = 0; $i < count($avgEnergyBeansMonth); $i++) {
						$totalEnergyBeansMonth[$i]['sumkWh'] = number_format($totalEnergyBeansMonth[$i]['sumkWh'],2,',','');
					}
				}else{
					$totalEnergyBeansMonth[0]['sumkWh'] = number_format('0',2,',','');
				}
			}
			if($totalEnergyBeansMonth[0]['sumkWh']>0){
				$totalEnergyBeansMonthKWHKWP= number_format($totalEnergyBeansMonth[0]['sumkWh'] / $sumPlantPower,2,',','');
			}else{
				$totalEnergyBeansMonthKWHKWP= number_format('0',2,',','');
			}
		}

		if($type == "year" || $type == "all"){
			if ($invtnum>0){
				$totalEnergyBeansYear = R::getAll("
						SELECT COUNT(kwh) as countkWh,MAX(kwh)  AS kWh,  SUM(kwh) AS sumkWh,time AS date 
						FROM energy WHERE INV = :INV 
						GROUP BY ".$this->crossSQLDateTime("'%Y'",'time','date')."  
						ORDER BY time DESC limit 0,:limit",array(':limit'=>$limit,':INV'=>$invtnum));
				$avgEnergyBeansYear = number_format($totalEnergyBeansYear[0]['sumkWh']/$totalEnergyBeansYear[0]['countkWh'],2,',','');
				for ($i = 0; $i < count($totalEnergyBeansYear); $i++) {
					$totalEnergyBeansYear[$i]['sumkWh'] = number_format($totalEnergyBeansYear[$i]['sumkWh'],2,',','');
				}
			}else{
				$totalEnergyBeansYear = R::getAll("
						SELECT COUNT(kwh) as countkWh,MAX(kwh)  AS kWh,  SUM(kwh) AS sumkWh,time AS date FROM energy 
						GROUP BY ".$this->crossSQLDateTime("'%Y'",'time','date')." 
						ORDER BY time DESC limit 0,:limit",array(':limit'=>$limit));
				if($totalEnergyBeansYear[0]['sumkWh']>0){
					$avgEnergyBeansYear = number_format($totalEnergyBeansYear[0]['sumkWh']/$totalEnergyBeansYear[0]['countkWh'],2,',','');
				}else{
					$avgEnergyBeansYear = number_format('0',2,',','');
				}
				for ($i = 0; $i < count($totalEnergyBeansYear); $i++) {
					$totalEnergyBeansYear[$i]['sumkWh'] = number_format($totalEnergyBeansYear[$i]['sumkWh'],2,',','');
				}
			}
			if($totalEnergyBeansYear[0]['sumkWh']>0){
				$totalEnergyBeansYearKWHKWP= number_format($totalEnergyBeansYear[0]['sumkWh'] / $sumPlantPower,2,',','');
			}else{
				$totalEnergyBeansYearKWHKWP= number_format('0',2,',','');
			}
		}

		if($type == "overall" || $type == "all"){
			if ($invtnum>0){
				$totalEnergyBeansOverall = R::getAll("
						SELECT COUNT(kwh) as countkWh, MAX(kwh)  AS kWh,  SUM(kwh) AS sumkWh, time AS date 
						FROM energy 
						WHERE INV = :INV 
						ORDER BY time limit 0,:limit",array(':limit'=>$limit,':INV'=>$invtnum));
				$avgEnergyBeansOverall = number_format($totalEnergyBeansOverall[0]['sumkWh']/$totalEnergyBeansOverall[0]['countkWh'],2,',','');
				for ($i = 0; $i < count($totalEnergyBeansOverall); $i++) {
					$totalEnergyBeansOverall[$i]['sumkWh'] = number_format($totalEnergyBeansOverall[$i]['sumkWh'],2,',','');
				}
			}else{
				$totalEnergyBeansOverall = R::getAll("
						SELECT COUNT(kwh) as countkWh, MAX(kwh)  AS kWh,  SUM(kwh) AS sumkWh, time AS date 
						FROM energy 
						ORDER BY time limit 0,:limit",array(':limit'=>$limit));

				if($totalEnergyBeansOverall[0]['sumkWh']>0){
					$avgEnergyBeansOverall = number_format($totalEnergyBeansOverall[0]['sumkWh']/$totalEnergyBeansOverall[0]['countkWh'],2,',','');
				}else{
					$avgEnergyBeansOverall = number_format('0',2,',','');
				}

				for ($i = 0; $i < count($totalEnergyBeansOverall); $i++) {
					$totalEnergyBeansOverall[$i]['sumkWh'] = number_format($totalEnergyBeansOverall[$i]['sumkWh'],2,',','');
				}
			}
			if($totalEnergyBeansOverall[0]['sumkWh'] >0){
				$totalEnergyBeansOverallKWHKWP= number_format($totalEnergyBeansOverall[0]['sumkWh'] / $sumPlantPower,2,',','');
			}else{
				$totalEnergyBeansOverallKWHKWP= number_format('0',2,',','');
			}


		}


		foreach ($config->devices as $device){
			$initialkwh += floatval($device->initialkwh);
		}
		$tempTotal = 0;
		$totalEnergyOverallTotal = number_format($initialkwh + floatval($totalEnergyBeansOverall[0]['sumkWh']),2,',','');
		if($totalEnergyOverallTotal>0){
			$totalEnergyOverallTotalKWHKWP =  number_format($totalEnergyOverallTotal / $sumPlantPower,2,',','');
		}else{
			$totalEnergyOverallTotalKWHKWP =  number_format('0',2,',','');
		}

		$energy = array(
				"todayMaxPower"=>(isset($maxPowerBeansToday[0]['GP']) ? $maxPowerBeansToday[0]['GP'] : 0),
				"todayMaxPowerTime"=>isset($maxPowerBeansToday[0]['date']) ? $maxPowerBeansToday[0]['date'] : time(),
				"todayDays"=>1,
				"todayAvgKwh"=>$avgEnergyBeansToday,
				"todayKwhKwp"=>$totalEnergyBeansTodayKWHKWP,
				"todayGenkWhCO2"=>($avgEnergyBeansToday*$config->co2kwh)/1000,

				"weekMaxPower"=>$totalEnergyBeansWeek[0]['kWh'],
				"weekMaxPowerTime"=>$totalEnergyBeansWeek[0]['date'],
				"weekDays"=>$totalEnergyBeansWeek[0]['countkWh'],
				"weekSumKwh"=>$totalEnergyBeansWeek[0]['sumkWh'],
				"weekAvgKwh"=>$avgEnergyBeansWeek,
				"weekKwhKwp"=>$totalEnergyBeansWeekKWHKWP,

				"monthMaxPower"=>$totalEnergyBeansMonth[0]['kWh'],
				"monthMaxPowerTime"=>$totalEnergyBeansMonth[0]['date'],
				"monthDays"=>$totalEnergyBeansMonth[0]['countkWh'],
				"monthSumKwh"=>$totalEnergyBeansMonth[0]['sumkWh'],
				"monthAvgKwh"=>$avgEnergyBeansMonth,
				"monthKwhKwp"=>$totalEnergyBeansMonthKWHKWP,

				"yearMaxPower"=>$totalEnergyBeansYear[0]['kWh'],
				"yearMaxPowerTime"=>$totalEnergyBeansYear[0]['date'],
				"yearDays"=>$totalEnergyBeansYear[0]['countkWh'],
				"yearSumKwh"=>$totalEnergyBeansYear[0]['sumkWh'],
				"yearAvgKwh"=>$avgEnergyBeansYear,
				"yearKwhKwp"=>$totalEnergyBeansYearKWHKWP,

				"overallMaxPower"=>$totalEnergyBeansOverall[0]['kWh'],
				"overallMaxPowerTime"=>$totalEnergyBeansOverall[0]['date'],
				"overallDays"=>$totalEnergyBeansOverall[0]['countkWh'],
				"overallSumKwh"=>$totalEnergyBeansOverall[0]['sumkWh'],
				"overallAvgKwh"=>$avgEnergyBeansOverall,
				"overallKwhKwp"=>$totalEnergyBeansOverallKWHKWP,
					
				"totalMaxPower"=>$totalEnergyOverallTotal,
				"totalMaxPowerTime"=>"0",
				"totalKwhKwp"=>$totalEnergyOverallTotalKWHKWP,

				"initialKwh" => $initialkwh,
		);
		return $energy;
	}

	/**
	 * 
	 * @param string $format = the formate
	 * @param string $column = the timestamp column
	 * @param string $dateTimeFunction = the SQLite function that doe
	 * @return string
	 */
	public function crossSQLDateTime($format, $column, $dateTimeFunction=''){
		$config = Session::getConfig();
		

		switch ($config->sqlEngine) {
		case 'sqlite':
			//strftime ( '%d-%m-%Y' , date ( time , 'unixepoch' ) );
			($dateTimeFunction=='') ? $dateTimeFunction = 'date' : $dateTimeFunction = $dateTimeFunction;
			//return "strftime (" . $format . " , " . $dateTimeFunction . " ( " . $column . " , 'unixepoch' ) )";
			return "strftime (" . $format . " ,  ".$dateTimeFunction . " ( " . $column . " , 'unixepoch' ) )";
			break;
		case 'mysql':
			//DATE_FORMAT(date,format)
			return "DATE_FORMAT(" . $column . "," . $format . ")";
			break;
		}
	}
	
	
	public function dropboxDisconnect($id){
		try{
			$bean =  R::findOne('dropboxOauthTokens',' userid = :userid ',array(':userid'=>1));
			R::trash( $bean );
			return true;
		} catch (Exception $e) {
			return false;
		}
	}
	
	
	public function dropboxTokenExists(){
		$beans =  R::findAndExport('dropboxOauthTokens',' id >0');
		return (count($beans) > 0 ? true : false);
	}

	public function dropboxSaveFile($file){
		//echo $file['path'];
		$bean = R::findOne('dropboxFilenameCaching',' path = :path ',array(':path'=>$file['path']));

		// Does not exists create the file
		if(!$bean){
			$bean = R::dispense('dropboxFilenameCaching');
		}
		
		// Update the file info
		$bean->path = $file['path'];
		$bean->fullPath = $file['fullPath'];
		$bean->client_mtime = $file['client_mtime'];
		$bean->bytes = $file['bytes'];
		$bean->active = 1;
		$id = R::store($bean);
	}
	public function dropboxDropFile($path){
		$bean = R::findOne('dropboxFilenameCaching', ' path = ? ', array($path));
		R::trash($bean); // Use trash to delete the student
	}


	public function dropboxCheckActive($files){
		R::exec( 'update dropboxFilenameCaching set active=0 where id>0');
			
		foreach($files as $file){
			R::exec( 'update dropboxFilenameCaching set active=1 where path=:path',array(':path'=>$file->path) );
		}
		R::exec( 'delete from dropboxFilenameCaching where active=0' );
	}


	public function dropboxGetFilesFromDB(){
		$beans =  R::findAndExport('dropboxFilenameCaching',' id > 0 ORDER BY client_mtime DESC');
		$i=0;
		if($beans){
			foreach ($beans as $bean) {
				$datas['client_mtime'] = date("D d-m-Y H:i",$bean['client_mtime']);
				$datas['fullPath'] = $bean['fullPath'];
				$datas['path'] = $bean['path'];
				$datas['id'] = $i;
				$datas['num'] = $i+1;
				$datas['size'] = number_format($bean['bytes']/1000000,2,'.',''); // Bytes -> MegaByte
				$totalBackupSize += $bean['bytes'];
				$i++;
				$data['files'][] = $datas;
			}
			$data['noData'] = 0;
		}else{
			$data['noData'] = 1;
			$data['files'][0]['noData']='No backups';
			$data['files'][0]['noData2']='It could be that we are out of sync with your dropbox, please hit the Sync link below';
		}
		$data['totalBackups'] = $i+1;
		$data['totalBackupSize'] = number_format($totalBackupSize/1000000,2,'.',''); // Bytes -> MegaByte
		$data['avarageBackupSize'] = number_format(($totalBackupSize/($i+1))/1000000,2,'.',''); // Bytes -> MegaByte
			
		return $data;
	}



	public function remove_hybridauth_session($current_user_id, $type){
		$bean =  R::findone('hybridUsersConnections',
				' user_id = :user_id AND type like :type ',
				array(':user_id'=>$current_user_id,
						':type'=> strtolower($type)
				)
		);
		R::trash($bean);

	}

	/**
	 *
	 * @param unknown $current_user_id  = id of user
	 * @param unknown $hybridauth_session_data  = Session data
	 * @param unknown $user_profile = user profile
	 * @param unknown $type = Twitter/Facebook
	 */

	public function save_hybridauth_session($current_user_id, $hybridauth_session_data,$user_profile, $type){
		$bean = R::findOne('hybridUsersConnections',' user_id = :user_id and type = :type LIMIT 1',array(':user_id'=>$current_user_id,':type'=>$type));

		if(!$bean){
			$bean = R::dispense('hybridUsersConnections');

			$bean->user_id = $current_user_id;
			$bean->hybridauth_session = $hybridauth_session_data;
			$bean->updated_at = strtotime("now");
			$bean->displayName = $user_profile->displayName;
			$bean->type= $type;

			//Store the bean

			$id = R::store($bean);
		}
	}

	/**
	 *
	 * @param int $user_id = id of user
	 * @param str $type = Twitter/Facebook
	 * @return Ambigous <multitype:, multitype:NULL >
	 */
	function get_hybridauth_session( $user_id, $type ){
		$sessionData = '';
		$beans = R::findAndExport('hybridUsersConnections',' user_id = :user_id and type = :type LIMIT 1',array(':user_id'=>$user_id,':type'=>$type));
		if ($beans){
			foreach ($beans as $bean){
				$sessionData= $bean;
			}
		}
		if($sessionData){
			return $sessionData;
		}
	}

	/**
	 * setDeviceType
	 *
	 * @param Device $device // Device object
	 * @return boolean // changed true;
	 */
	function setDeviceType(Device $device){
		// get device bean
		$bean = R::load('inverter', $device->id);
		$bean->type = "production";
		//Store the bean with the new device type
		R::store($bean);
		return true;
	}

	/**
	 *
	 * @param string $type (options: panels, average) default = panels
	 * @param unknown $deviceNum
	 */
	function getAvgPower($config,$deviceNum=0){
		
		($config->gaugeMaxType=='') ? $type='panels' : $type = $config->gaugeMaxType;
		
		switch ($type) {
			case 'panels':
				$avarage = array();
				$average['recent'] = 0;
				foreach ($config->devices as $device) {
					$average['recent'] += $device->plantpower;
				}
				break;
			case 'average':

				$recentBegin = time()-400;
				$recentEnd = time();

				$pastBegin = time()-800;
				$pastEnd = time()-400;
				if ($deviceNum > 0){
					$queryWhere = " inv = ". $deviceNum ." AND ";
				}else{
					$queryWhere = "";
				}
				$query = "SELECT  avg(GP) AS avgGP FROM 'history' WHERE ".$queryWhere." time > :begin AND  time < :end ORDER BY time DESC";

				$avgRecent =  R::getAll($query,array(':begin'=>$recentBegin,':end'=>$recentEnd));
				$avgPast   =  R::getAll($query,array(':begin'=>$pastBegin,':end'=>$pastEnd));

				$average['recent'] = $avgRecent[0]['avgGP'];
				$average['past'] = $avgPast[0]['avgGP'];

				if($average['recent']>$average['past']){
					$average['trend'] = _("up");
				}elseif($average['recent']<$average['past']){
					$average['trend'] = _("down");
				}else{
					$average['trend'] = _("equal");
				}

				break;
			default:
				break;
		}
		return $average;
	}


	// TODO: remove ?
	/*
	 * Looks the code below is not used.
	 */
	/**
	 *
	 * @param string $type (options: panels, average) default = panels
	 * @param unknown $deviceNum
	 */
	public function getPowerTrend($deviceNum=0){
		$config = Session::getConfig();
		$average = $this->readCache(1,"index","live",$deviceNum,"");
		return $average;
	}


	public function saveCache(Cache $cache){
		$bean = R::findone('cache',' `key` = :key ', array( ":key"=> $cache->key));
		if (!$bean){
			$bean = R::dispense('cache');
		}
		$bean->key = $cache->key;
		$bean->value = $cache->value;
		$bean->module = $cache->module;
		$bean->page = $cache->page;
		$bean->timestamp = $cache->timestamp;
		$id = R::store($bean);
	}

	public function checkDefaultPassword($config){
		if($config->adminpasswd=='d033e22ae348aeb5660fc2140aec35850c4da997'){
			//We have a default password....
			return true;
		}else{
			//We do NOT have a default password
			return false;
		}
	}
}