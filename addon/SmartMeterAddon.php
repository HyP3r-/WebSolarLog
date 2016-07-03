<?php
class SmartMeterAddon {
	private $adapter;
	private $config;
	private $deviceService;
	private $deviceHistoryService;
	private $liveSmartMeterService;
	private $addon = "smartMeter";
	private $install = false;
	
	function __construct() {
		$this->adapter = PDODataAdapter::getInstance();
		$this->deviceService = new DeviceService();
		$this->deviceHistoryService = new DeviceHistoryService();
		$this->liveSmartMeterService = new LiveSmartMeterService();
		$this->historySmartMeterService = new HistorySmartMeterService();
		$this->config = Session::getConfig();
		
		foreach ($this->config->devices as $device) {
			if($device->type == "metering"){
				$this->install = true;
			}
		}
	}

	function __destruct() {
		$this->liveSmartMeterService = null;
		$this->deviceHistoryService = null;
		$this->deviceService = null;
		$this->config = null;
		$this->adapter = null;
		$this->install = null;
	}

	
	public function installGraph(){
		if($this->install==true){
			HookHandler::getInstance()->fire("onDebug", "install SmartMeter");
			$graph = R::dispense('graph');
	
			$graph->series = self::defaultSeries();

			$graph->metaData = array(
					'legend'=>array(
						"show"=>true,
						"location"=>'s',
						"placement"=>'outsideGrid',
						"renderer"=>'EnhancedLegendRenderer',
						"rendererOptions"=>array(
								"seriesToggle"=>'normal',
								"numberRows"=>2
						),
						"left"=>10,
						"width"=>700
					)
			);
			return $graph;
		}else{
			return;
		}
	}
	
	/**
	 * Handle hook onHistory
	 * @param unknown $args
	 */
	public function onSmartMeterHistory($args) {
		$device = $args[1];
		$live = $args[2];
		$timestamp = $args[3];
		$this->addSmartMeterHistory($device->id, $live, $timestamp);

		// We are live, but db things offline
		if ($device->state == 0) {
			$this->deviceService->changeDeviceStatus($device, 1);
			$device->state == 1;
		}
		$sessionKey = 'noLiveCounter-' . $device->id;
		if (isset($_SESSION[$sessionKey])) {
			unset($_SESSION[$sessionKey]);
		}
	}

	public function onSmartMeterEnergy($args) {
		$device = $args[1];

		$arHistory = $this->readSmartMeterHistory($device->id, null);

		// Initialize the variables we dont want errors in the logs
		$gasUsage = 0;
		$highReturn = 0;
		$lowReturn = 0;
		$highUsage = 0;
		$lowUsage = 0;
		$gasUsageEnd = 0;
		$highReturnEnd = 0;
		$lowReturnEnd = 0;
		$highUsageEnd = 0;
		$lowUsageEnd = 0;

		if(count($arHistory)>1){
			$first = reset($arHistory);
			$last = end($arHistory);

			$gasUsageStart = $first['gasUsage'];
			$gasUsageEnd = $last['gasUsage'];
			$highReturnStart = $first['highReturn'];
			$highReturnEnd = $last['highReturn'];
			$lowReturnStart = $first['lowReturn'];
			$lowReturnEnd = $last['lowReturn'];
			$highUsageStart = $first['highUsage'];
			$highUsageEnd = $last['highUsage'];
			$lowUsageStart = $first['lowUsage'];
			$lowUsageEnd = $last['lowUsage'];

			$gasUsage = $gasUsageEnd - $gasUsageStart;
			$highReturn = $highReturnEnd - $highReturnStart;
			$lowReturn = $lowReturnEnd - $lowReturnStart;
			$highUsage = $highUsageEnd - $highUsageStart;
			$lowUsage = $lowUsageEnd - $lowUsageStart;
		}

		// Set the new values and save it
		$energy = new EnergySmartMeter();
		$energy->time = $args[2];
		$energy->INV = $device->id;
		$energy->gasUsage = $gasUsage;
		$energy->highReturn = $highReturn;
		$energy->lowReturn = $lowReturn;
		$energy->highUsage = $highUsage;
		$energy->lowUsage = $lowUsage;
		$energy->gasUsageT = $gasUsageEnd;
		$energy->highReturnT = $highReturnEnd;
		$energy->lowReturnT = $lowReturnEnd;
		$energy->highUsageT = $highUsageEnd;
		$energy->lowUsageT = $lowUsageEnd;
		$energy->co2 = Formulas::CO2gas($gasUsage, $this->config->co2kwh); // Calculate co2
		$this->addSmartMeterEnergy($device->id, $energy);

		HookHandler::getInstance()->fire("newSmartMeterEnergy", $device, $energy);
	}


	/**
	 * Handle hook onLiveSmartMeterData
	 * @param unknown $args
	 */
	public function onLiveSmartMeterData($args) {
		$device = $args[1];
		$live = $args[2];
		
		if ($device == null) {
			HookHandler::getInstance()->fire("onError", "CoreAddon::onLiveSmartMeterData() device == null");
			return;
		}
		
		// Get the current live object
		$dbLive = $this->liveSmartMeterService->getLiveByDevice($device);
		$live->liveGas = 0;
		if ($dbLive) {
			$live->id = $dbLive->id;
			
			// Calculate the gass
			$live->liveGas = ($live->gasUsage - $dbLive->gasUsage);
		}
		
		$this->liveSmartMeterService->save($live);

		HookHandler::getInstance()->fire("newLiveData", $device, $live);
	}

	/**
	 * add the live info to the history
	 * @param int $deviceId
	 * @param Live $live
	 * @param string date
	 */
	public function addSmartMeterHistory($deviceId, LiveSmartMeter $live,$timestamp) {
 	
		
		$bean = R::dispense('historySmartMeter');

		// check if we have a "valid" Bean to store.
		$bean->deviceId = $deviceId;
		$bean->invtnum = $deviceId;
		$bean->gasUsage = $live->gasUsage;
		$bean->highReturn = $live->highReturn;
		$bean->lowReturn = $live->lowReturn;
		$bean->highUsage = $live->highUsage;
		$bean->lowUsage = $live->lowUsage;
		$bean->liveReturn = $live->liveReturn;
		$bean->liveUsage = $live->liveUsage;
		$bean->pvoutput = 0;
		$bean->pvoutputSend = $this->CheckPVoutputSend($deviceId);
		$bean->time = $timestamp;
		
		//Store the bean
		$id = R::store($bean);
		
		return $bean;
	}
	

	/**
	 * Check to see if this is a PVoutput record
	 * @return 0/1
	 */
	
	public function CheckPVoutputSend($deviceId) {
		$bean = R::findOne('historySmartMeter','pvoutputSend = 1 AND deviceId = :deviceId ORDER BY id DESC LIMIT 1',array("deviceId"=>$deviceId));
	
		// Check if we got a record thats been marked as a PVoutput record
		if (!$bean){
			if($bean['id'] > 0){
				if((time() - $bean['time']) >= 300){
					return '1';
				}else{
					return '0';
				}
			}else{
				// We do not have a record, so this record needs to be a PVoutput record
				return '1';
			}
		}else{
			//We do not have a record, so this record needs to be a PVoutput record
			return '1';
		}
	}
	

	public function onSummary($args){
		
		$device = $args[1];
		if($device->deviceApi == "DutchSmartMeter"){
			$locale  = localeconv();
			$deviceId = $device->id;

			$date = $args[2];
			
			$beginEndDate = Util::getBeginEndDate('day', 1,$date);

			$parameters = array(':deviceId'=>$deviceId,':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']);

			$finds =  R::find( 'energySmartMeter',
					' deviceId = :deviceId AND time >= :beginDate AND  time <= :endDate ',
					$parameters
			);
			
			$find = reset ( $finds );
			
			$bean['lowReturn'] = $find['lowReturn']/1000;
			$bean['lowReturnCO2'] = $bean['lowReturn'] * $this->config->co2kwh;
			$bean['lowReturnTrees'] = $bean['lowReturnCO2'] / $this->config->co2CompensationTree;
			
			$bean['lowUsage'] = $find['lowUsage']/1000;
			$bean['lowUsageCO2'] = $bean['lowUsage'] * $this->config->co2kwh;
			$bean['lowUsageTrees'] = $bean['lowUsageCO2'] / $this->config->co2CompensationTree;
			
			$bean['highReturn'] = $find['highReturn']/1000;
			$bean['highReturnCO2'] = $bean['highReturn'] * $this->config->co2kwh;
			$bean['highReturnTrees'] = $bean['highReturnCO2'] / $this->config->co2CompensationTree;
			
			
			$bean['highUsage'] = $find['highUsage']/1000;
			$bean['highUsageCO2'] = $bean['highUsage'] * $this->config->co2kwh;
			$bean['highUsageTrees'] = $bean['highUsageCO2'] / $this->config->co2CompensationTree;
			
			$bean['gasUsage'] = $find['gasUsage']/1000;
			$bean['gasUsageCO2'] = ($bean['gasUsage'] * $this->config->co2gas)/1000;
			$bean['gasUsageTrees'] = $bean['gasUsageCO2'] / $this->config->co2CompensationTree;
			$bean['gasUsageCosts'] = round((($find['gasUsage']/1000) * $this->config->costGas)/100,2);
			
			$bean['returnKWH'] = $bean['lowReturn'] + $bean['highReturn'];
			$bean['returnCO2'] =  ($bean['returnKWH'] * $this->config->co2kwh)/1000;
			$bean['returnCosts'] = ($bean['returnKWH'] * $this->config->costkwh)/100;
			
			$bean['usageKWH'] = $bean['lowUsage'] + $bean['highUsage'];
			$bean['usageCosts'] = ($bean['usageKWH'] / $this->config->costkwh);
			$bean['usageCO2'] = $bean['usageKWH'] * $this->config->co2kwh;
			$bean['effUsageKWH'] = $bean['usageKWH']-$bean['returnKWH'];
			$bean['effUsageCosts'] = $find['effUsage'] * $this->config->costkwh;

			return $bean;
		}else{
			return;
		}
	}
	
	/**
	 * Read the history file
	 * @param int $deviceId
	 * @param string $date
	 * @return array<Live> $live (No Live but BEAN object!!)
	 */
	// TODO :: There's no Live object returned....?!
	public function readSmartMeterHistory($deviceId, $date) {
 		(!$date)? $date = date('d-m-Y') : $date = $date;
		$beginEndDate = Util::getBeginEndDate('day', 1,$date);

		$bean =  R::find( 'historySmartMeter',
				' deviceId = :deviceId AND time > :beginDate AND  time < :endDate order by time',
				array(':deviceId'=>$deviceId,':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate'])
		);
		return $bean;
	}

	/**
	 * Return the amount off history records
	 * @param int $deviceId
	 * @param string $date
	 * @return int $count
	 */
	public function getSmartMeterHistoryCount($deviceId) {
		$date = date('d-m-Y');
		$beginEndDate = Util::getBeginEndDate('day', 1,$date);

		$bean =  R::find('historySmartMeter',
				' deviceId = :deviceId AND time > :beginDate AND  time < :endDate order by time',
				array(':deviceId'=>$deviceId,':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate'])
		);
		return count($bean);
	}



	/**
	 * write the max power today to the file
	 * @param int $deviceId
	 * @param MaxPowerToday $mpt
	 */
	public function addSmartMeterEnergy($deviceId, EnergySmartMeter $energy) {
		$date = date('d-m-Y');
		$beginEndDate = Util::getBeginEndDate('day', 1,$date);

		$bean =  R::findone('energySmartMeter',
				' deviceId = :deviceId AND time > :beginDate AND  time < :endDate order by time',
				array(':deviceId'=>$deviceId,':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate'])
		);

		$oldGasUsageT = 0;
		$oldHighReturnT = 0;
		$oldLowReturnT = 0;
		$oldHighUsageT = 0;
		$oldLowUsageT = 0;
		$oldLiveReturnT = 0;
		$oldLiveUsageT = 0;

		if (!$bean){
			$bean = R::dispense('energySmartMeter');
		} else {
			$oldGasUsageT = $energy->gasUsageT;
			$oldHighReturnT = $energy->highReturnT;
			$oldLowReturnT = $energy->lowReturnT;
			$oldHighUsageT = $energy->highUsageT;
			$oldLowUsageT = $energy->lowUsageT;
		}

		$bean->deviceId = $deviceId;
		$bean->invtnum = $deviceId;
		$bean->gasUsageT = $energy->gasUsageT;
		$bean->highReturnT = $energy->highReturnT;
		$bean->lowReturnT = $energy->lowReturnT;
		$bean->highUsageT = $energy->highUsageT;
		$bean->lowUsageT = $energy->lowUsageT;

		$bean->gasUsage = $energy->gasUsage;
		$bean->highReturn = $energy->highReturn;
		$bean->lowReturn = $energy->lowReturn;
		$bean->highUsage = $energy->highUsage;
		$bean->lowUsage = $energy->lowUsage;
		$bean->liveReturn = $energy->liveReturn;
		$bean->liveUsage = $energy->liveUsage;
		$bean->time = time();

		return R::store($bean);
	}

	public function defaultAxes(){
		$show = 'false';
		foreach ($this->config->devices as $device) {
			if($device->type == "metering"){
				$show = 'true';
			}
		}
		
		$axe = R::dispense('axes',2);
		$axe[0]['json'] = json_encode(array('axe'=>'y3axis','label'=>_("Gas").' '._("(L)"),'min'=>0,'labelRenderer'=>'CanvasAxisLabelRenderer'));
		$axe[0]['show'] = $show;
		$axe[0]['order'] = 3;
		$axe[0]['addon'] = $this->addon;
		$axe[1]['json'] = json_encode(array('axe'=>'y4axis','label'=>_("Actual").''._("(W)"),'labelRenderer'=>'CanvasAxisLabelRenderer'));
		$axe[1]['show'] = $show;
		$axe[1]['order'] = 4;
		$axe[1]['addon'] = $this->addon;

		return $axe;
	}

	public function defaultSeries(){
		$show = 'false';
		foreach ($this->config->devices as $device) {
			if($device->type == "metering"){
				$show = 'true';
			}
		}

		$serie = R::dispense('series',11);

		$serie[0]['json'] = json_encode(array('label'=>'Cum Gas (l)','yaxis'=>'y3axis'));
		$serie[0]['name'] = 'gasUsage';
		$serie[0]['disabled'] = 'false';
		$serie[0]['show'] = $show;
		$serie[0]['addon'] = $this->addon;
		$serie[0]['order'] = 0;
		
		$serie[1]['json'] = json_encode(array('label'=>'Smooth Gas (l)','yaxis'=>'y3axis'));
		$serie[1]['name'] = 'smoothGasL';
		$serie[1]['disabled'] = 'false';
		$serie[1]['show'] = $show;
		$serie[1]['addon'] = $this->addon;
		$serie[1]['order'] = 1;
		
		$serie[2]['json'] = json_encode(array('label'=>'Cum low usage (W)','yaxis'=>'y2axis'));
		$serie[2]['name'] = 'cumLowUsageW';
		$serie[2]['disabled'] = 'false';
		$serie[2]['show'] = $show;
		$serie[2]['addon'] = $this->addon;
		$serie[2]['order'] = 2;
		
		$serie[3]['json'] = json_encode(array('label'=>'Cum high usage (W)','yaxis'=>'y2axis'));
		$serie[3]['name'] = 'cumHighUsageW';
		$serie[3]['disabled'] = 'false';
		$serie[3]['show'] = $show;
		$serie[3]['addon'] = $this->addon;
		$serie[3]['order'] = 3;
		
		$serie[4]['json'] = json_encode(array('label'=>'Cum low return (W)','yaxis'=>'y2axis'));
		$serie[4]['name'] = 'cumLowReturnW';
		$serie[4]['disabled'] = 'false';
		$serie[4]['show'] = $show;
		$serie[4]['addon'] = $this->addon;
		$serie[4]['order'] = 4;
		
		$serie[5]['json'] = json_encode(array('label'=>'Cum high return (W)','yaxis'=>'y2axis'));
		$serie[5]['name'] = 'cumHighReturnW';
		$serie[5]['disabled'] = 'false';
		$serie[5]['show'] = $show;
		$serie[5]['addon'] = $this->addon;
		$serie[5]['order'] = 5;
		
		$serie[6]['json'] = json_encode(array('label'=>'Low usage (W)' ,'yaxis'=>'yaxis'));
		$serie[6]['name'] = 'lowUsageW';
		$serie[6]['disabled'] = 'false';
		$serie[6]['show'] = $show;
		$serie[6]['addon'] = $this->addon;
		$serie[6]['order'] = 6;
		
		$serie[7]['json'] = json_encode(array('label'=>'High usage (W)' ,'yaxis'=>'yaxis'));
		$serie[7]['name'] = 'highUsageW';
		$serie[7]['disabled'] = 'false';
		$serie[7]['show'] = $show;
		$serie[7]['addon'] = $this->addon;
		$serie[7]['order'] = 7;
		
		$serie[8]['json'] = json_encode(array('label'=>'Low return (W)','yaxis'=>'yaxis'));
		$serie[8]['name'] = 'lowReturnW';
		$serie[8]['disabled'] = 'false';
		$serie[8]['show'] = $show;
		$serie[8]['addon'] = $this->addon;
		$serie[8]['order'] = 8;
		
		$serie[9]['json'] = json_encode(array('label'=>'High return (W)','yaxis'=>'yaxis'));
		$serie[9]['name'] = 'highReturnW';
		$serie[9]['disabled'] = 'false';
		$serie[9]['show'] = $show;
		$serie[9]['addon'] = $this->addon;
		$serie[9]['order'] = 9;
		
		$serie[10]['json'] = json_encode(array('label'=>'Actual usage (W)','yaxis'=>'y4axis'));
		$serie[10]['name'] = 'actualUsageW';
		$serie[10]['disabled'] = 'false';
		$serie[10]['show'] = $show;
		$serie[10]['addon'] = $this->addon;
		$serie[10]['order'] = 10;
		
		return $serie;
	}
	/**
	 * 
	 */
	public function addSeries(){
		$graph = new Graph();
		$graph = $this->defaultSeries();
	}
	
	/**
	 * 
	 * @param unknown $beans
	 * @param unknown $startDate
	 * @return Graph
	 */
	public function beansToGraphPoints($beans,$startDate,$disabledSeries){
		$graph = new Graph();
		$metaData = array();
		/*
		 * Generate Graph Point and series
		*/
		$i=0;
		foreach ($beans as $bean){
			if ($i==0){
				$firstBean = $bean;
				$preBean = $bean;
				$preBeanUTCdate = $bean['time'];
			}
			$UTCdate = $bean['time'];
			$UTCtimeDiff = $UTCdate - $preBean['time'];

			if(!in_array('cumGas',$disabledSeries)){
				$graph->points['gasUsage'][] = array (
						$UTCdate ,
						$bean['gasUsage']-$firstBean['gasUsage']);
			}

			if(!in_array('smoothGasL',$disabledSeries)){
				if($i==0){
					$graph->points['smoothGasL'][] = array ($UTCdate ,$firstBean['gasUsage']-$bean['gasUsage']);
				}
	
				if( $bean['gasUsage']-$firstBean['gasUsage'] != $preBean['gasUsage']-$firstBean['gasUsage']){
					$graph->points['smoothGasL'][] = array ($UTCdate,$bean['gasUsage']-$firstBean['gasUsage']);
				}
			}

			if(!in_array('cumLowUsageW',$disabledSeries)){
				$graph->points['cumLowUsageW'][] = array ($UTCdate,$bean['lowUsage']-$firstBean['lowUsage']);
			}
			if(!in_array('cumHighUsageW',$disabledSeries)){
				$graph->points['cumHighUsageW'][] = array ($UTCdate ,$bean['highUsage']-$firstBean['highUsage']);
			}
			if(!in_array('cumLowReturnW',$disabledSeries)){
				$graph->points['cumLowReturnW'][] = array ($UTCdate ,$bean['lowReturn']-$firstBean['lowReturn']);
			}
			
			if(!in_array('cumHighReturnW',$disabledSeries)){
				$graph->points['cumHighReturnW'][] = array ($UTCdate ,$bean['highReturn']-$firstBean['highReturn']);
			}
			
			$lowUsage = Formulas::calcAveragePower($preBean['lowUsage'], $bean['lowUsage'], $preBean['time']-$bean['time'])/1000;
			$highUsage = Formulas::calcAveragePower($preBean['highUsage'], $bean['highUsage'], $preBean['time']-$bean['time'])/1000;
			$lowReturn = Formulas::calcAveragePower($preBean['lowReturn'], $bean['lowReturn'], $preBean['time']-$bean['time'])/1000;
			$highReturn = Formulas::calcAveragePower($preBean['highReturn'], $bean['highReturn'], $preBean['time']-$bean['time'])/1000;

			$lowActual = $lowUsage-$lowReturn;
			$highActual =  $highUsage-$highReturn;
			$actualUsage = (int)0;
			($lowActual!=0) ?	$actualUsage = $lowActual :	$actualUsage = $highActual;

			if(!in_array('lowUsageW',$disabledSeries)){
				$graph->points['lowUsageW'][] = array ($UTCdate ,$lowUsage);
			}
			if(!in_array('highUsageW',$disabledSeries)){
				$graph->points['highUsageW'][] = array ($UTCdate ,$highUsage);
			}
			if(!in_array('lowReturnW',$disabledSeries)){
				$graph->points['lowReturnW'][] = array ($UTCdate ,$lowReturn);
			}
			if(!in_array('highReturnW',$disabledSeries)){
				$graph->points['highReturnW'][] = array ($UTCdate ,$highReturn);
			}
			(!isset($minActual)) ? $minActual = 0 : $minActual = $minActual;
			(!isset($maxActual)) ? $maxActual = 0 : $maxActual = $maxActual;
			($actualUsage<$minActual) ? $minActual = $actualUsage : $actualUsage = $actualUsage;
			($actualUsage>$maxActual) ? $maxActual = $actualUsage : $actualUsage = $actualUsage;

			if(!in_array('actualUsageW',$disabledSeries)){
				$graph->points['actualUsageW'][] = array ($UTCdate ,round(trim($actualUsage),0));				
			}
			$preBean = $bean;
			$i++;
		}
		
		// see if we have more then 1 bean (the dummy bean)
		if($i > 1){
			$graph->timestamp = Util::getBeginEndDate('day', 1,$startDate);
			($maxActual>0) ? $maxActual = ceil( ( ($maxActual*1.1)+100) / 100 ) * 100 : $maxActual= $maxActual;
			($minActual<0) ? $minActual = ceil( ( ($minActual*1.1)+100) / 100 ) * 100 : $minActual = $minActual;
		}else{
			$graph->points=null;
		}
		return $graph;
	}


	/**
	 * return a array with GraphPoints
	 * @param date $labels[] = 'High Power(W)';$startDate ("Y-m-d") ("1900-12-31"), when no date given, the date of today is used.
	 * @return array($beginDate, $endDate);
	 */
	// Hook fired with ("GraphDayPoints",$deviceId,$startDate,$type,$hiddenSeries);
	public function GraphDayPoints($args){		
		if($args[1]->deviceApi == 'DutchSmartMeter'){
			(strtolower($args[3]) == 'today')?$type='day':$type=$args[3];
			$graphDataService = new GraphDataService();
			$graph = $this->readTablesPeriodValues($args[1], 'historySmartMeter', $type, $args[2]);
			
			$graph = $this->beansToGraphPoints($graph,$args[2],$args[4]);
				
			return $graph;
		}
		
	}
	
	
	/**
	 *
	 * @param unknown $deviceId
	 * @param unknown $table
	 * @param unknown $type
	 * @param unknown $startDate
	 * @return unknown
	 */
	
	public static function readTablesPeriodValues($device, $table, $type, $startDate){
		$count = 0;
	
		// get the begin and end date/time
		$beginEndDate = Util::getBeginEndDate($type, $count,$startDate);
		$energyBeans = array();

		$energyBeans = R::getAll("SELECT *
                                  FROM historySmartMeter
                                  WHERE deviceId = :deviceId AND time > :beginDate AND  time < :endDate ORDER BY id",
		array(':deviceId'=>$device->id,':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
	
		
		//see if we have atleast 1 bean, else we make one :)
		(!$energyBeans) ? $energyBeans[0] = array('time'=>time(),'KWH'=>0,'KWHT'=>0) : $energyBeans = $energyBeans;
		return $energyBeans;
	}
	
	
}
?>