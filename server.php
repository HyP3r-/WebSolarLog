<?php
define('checkaccess', TRUE);
// clear old session logs

require 'classes/classloader.php';

try {
	$data = array();
	Session::initializeLight();
	
	if(file_exists(sys_get_temp_dir()."/WSLConfig.json")){
		$data['configType'] = 'json';
		$config = json_decode(file_get_contents(sys_get_temp_dir()."/WSLConfig.json"));
	}else{
		$data['configType'] = 'db';
		$config = Session::getConfig();
	}
	
	if(!(Common::getValue('noDB'))){
		if (PeriodHelper::isPeriodJob("inActiveJob", 30)) {
			HookHandler::getInstance()->fire("onInActiveJob");
		}
		// Initialize return array
		$dataAdapter = PDODataAdapter::getInstance();
	}
	
	// Retrieve action params
	$method = $_GET['method'];

	// Set headers for JSON response
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-type: application/json');


	
	$devicenum = Common::getValue('devicenum', 0);
	$page = Common::getValue('page', 0);
	$count = Common::getValue('count', 0);
	$type = Common::getValue('type', 0);
	$date = Common::getValue('date', 0);
	$year = Common::getValue('year', 0);
	
	switch ($method) {
		case 'getTabs':
			$tabs = array();
			$tabs[] = array( "graphName" => "Today","translation" => _("Today"),"position" => "0","active" => ($page == "index") ? 'true' : 'false');
			$tabs[] = array( "graphName" => "Yesterday","translation" => _("Yesterday"),"position" => "1","active" => ($page == "yesterday") ? 'true' : 'false');
			$tabs[] = array( "graphName" => "Month","translation" => _("Month"),"position" => "2","active" => ($page == "month") ? 'true' : 'false');
			$tabs[] = array( "graphName" => "Year","translation" => _("Year"),"position" => "3","active" => ($page == "year") ? 'true' : 'false');
				
			$options = array();
			$options[] =array( "value" => "Today","name"=> _("Day"));
			$options[] =array( "value" => "Week","name"=> _("Week"));
			$options[] =array( "value" => "Month","name"=> _("Month"));
			$options[] =array( "value" => "Year","name"=> _("Year"));
				
			$data['tabsPosition'] =  Common::searchMultiArray($tabs, 'active', 'true');
			$lang = array();
			$lang['date'] = _('date');
			$lang['periode'] = _('periode');
			$lang['previous'] = _('previous');
			$lang['next'] = _('next');
				
			$data['lang'] = $lang;
			$data['tabs'] = $tabs;
			$data['options'] = $options;

			break;
		case 'setLanguage':
			$localeExplode = explode(".", setlocale(LC_CTYPE, 0));
			$lang = Common::getValue("language",$localeExplode[0]);
			$_SESSION['WSL_LANGUAGE'] = $lang;
			Session::setLanguage();
		case 'getLanguages':
			$languages = array();
			if ($handle = opendir('locale')) {
				while (false !== ($entry = readdir($handle))) {
					if ($entry != "." && $entry != ".." ) {
						$languages[] = $entry;
					}
				}
			}
			$data['languages'] = $languages;
			//$data['currentlanguage'] = $user_lang;
			$currentLang = explode(".", setlocale(LC_CTYPE, 0));
			$data['currentlanguage'] = $currentLang[0];
			break;
		case 'analyticsSettings':
			$data['googleSuccess'] = false;
			if (isset($config->googleAnalytics) && $config->googleAnalytics != "") {
				$data['googleSuccess'] = true;
				$data['googleAnalyticsCode'] = $config->googleAnalytics;
			}
			$data['piwikSuccess'] = false;
			if (isset($config->piwikServerUrl) && $config->piwikServerUrl != "") {
				$data['piwikSuccess'] = true;
				$data['piwikServerUrl'] = $config->piwikServerUrl;
				$data['piwikSiteId'] = $config->piwikSiteId;
			}
			break;
				
		case 'getDevices':
			$devices = array();
			foreach ($config->devices as $device){
				if($device->type=="production"){
					$devices[] = array(
							'name'=>$device->name,
							'id'=>$device->id
					);
				}
			}
				
			$slimConfig['devices'] = $devices;
			$data['slimConfig'] = $slimConfig;
			break;
		case 'getMisc':
			$eventService = new EventService();
				
			$serverUptime = Util::serverUptime();

			$slimConfig = array();
			$slimConfig['lat'] = $config->latitude;
			$slimConfig['long'] = $config->longitude;
			$devices = array();
			foreach ($config->devices as $device){
				$panels = null;
				$info = $eventService->getArrayByDeviceAndType($device, 'Info', 1);
				if (count($info) == 1) {
					$info = $info[0];
				}
				if (count($info) == 0) {
					$info = null;
				}

				if($device->type=="production"){
					foreach ($device->panels as $panel){
						$panels[] = array(
								'id'=>$panel->id,
								'description'=>$panel->description,
								'roofOrientation'=>$panel->roofOrientation,
								'roofPitch'=>$panel->roofPitch,
								'amount'=>$panel->amount,
								'wp'=>$panel->wp,
								'totalWp'=>$panel->amount*$panel->wp
						);
					}
				}
				$devices[] = array(
						'name'=>$device->name,
						'type'=>$device->type,
						'deviceApi'=>$device->deviceApi,
						'pvoutputWSLTeamMember'=>$device->pvoutputWSLTeamMember,
						'pvoutputSystemId'=>$device->pvoutputSystemId,
						'pvoutputEnabled'=>$device->pvoutputEnabled,
						'expectedKWH'=>$device->expectedkwh,
						'plantPower'=>$device->plantpower,
						'panels'=>$panels,
						'events'=>array(
								'notice'=>$eventService->getArrayByDeviceAndType($device, 'notice'),
								'alarm'=>$eventService->getArrayByDeviceAndType($device, 'alarm'),
								'info'=>$info
						)
				);
			}
			$slimConfig['devices'] = $devices;
				
			$lang = array();
			$lang['Time'] = _('Time');
			$lang['Inv'] = _('Inv');
			$lang['Inverter'] = _('Inverter');
			$lang['Event'] = _('Event');
			$lang['Miscellaneous'] = _('Miscellaneous');
			$lang['Notice'] = _('Notice');
			$lang['Alarm'] = _('Alarm');
			$lang['Info'] = _('Info');
			$lang['Information'] = _('Information');
			$lang['power'] = _('power');
			$lang['system'] = _('system');
			$lang['expected'] = _('expected');
			$lang['amount'] = _('amount');
			$lang['wp'] = _('Wp');
			$lang['kWh'] = _('kWh');
			$lang['installedPower'] = _('installed power');
			$lang['orientation'] = _('orientation');
			$lang['pitch'] = _('pitch');
			$lang['power'] = _('power');
			$lang['mpp'] = _('mpp');
			$lang['description'] = _('description');
			$lang['location'] = _('location');
			$lang['logger'] = _('logger');
			$lang['uptime'] = _('uptime');
			$lang['days'] = _('days');
			$lang['hours'] = _('hours');
			$lang['mins'] = _('mins');
			
			$data['lang'] = $lang;
			$data['slimConfig'] = $slimConfig;
			$data['serverUptime'] = $serverUptime;
			break;
		/*
		 * TODO
		 * Not used anymore, so we could delete it!?
		case 'getGraphDayPoints':
			$lines = array();
			$lines['graph'] = null;
			$dtz = new DateTimeZone($config->timezone);
			$timezoneOffset = new DateTime('now', $dtz);

			$data['timezoneOffset'] = $dtz->getOffset( $timezoneOffset )/3600;
				

			$slimConfig = array();
			$slimConfig['lat'] = number_format($config->latitude,2,'.','');
			$slimConfig['long'] = number_format($config->longitude,2,'.','');
			$util = new Util();
				
			$data['sunInfo'] = $util->getSunInfo($config, $date);
			foreach ($config->devices as $device){
				if($device->type=='production'){
					foreach ($device->panels as $panel){
						$panels[] = array(
								'roofOrientation'=>$panel->roofOrientation,
								'roofPitch'=>$panel->roofPitch,
								'totalWp'=>$panel->amount*$panel->wp
						);
					}
					$inverters[] = array(
							'plantPower'=>$device->plantpower,
							'panels'=> (isset($panels) ? $panels : 0)
					);
				}
			}
			$slimConfig['inverters'] = $inverters;
				
			$graphService = new GraphService();
			$dayData = new DayDataResult();
			$options['deviceNum'] = $devicenum;
			$options['type'] = $type;
			$options['date'] = $date;
			$options['mode'] = 'frontend';
			$data['graph'] = $graphService->loadGraph($options);
				
			$dayData = new DayDataResult();
			$dayData->graph = $lines['graph'];
				
			$dayData->success = true;
			$lang = array();
			$lang['cumPowerW'] = _('Energy (Wh)');
			$lang['avgPowerW'] = _('avg. Power (W)');
			$lang['harvested'] = _('harvested (W)');
			$lang['cumulative'] = _('cumulative (W)');
			$lang['generated'] = _('generated');
			$lang['max'] = _('max');
			$data['slimConfig'] = $slimConfig;
			$data['lang'] = $lang;
			$data['dayData'] = $dayData;
			break;
		*/
		case 'getGraphPoints':
			$lines = $dataAdapter->getGraphPoint(0, $type, $date);
			$dayData = new DayDataResult();
			$dayData->data = $lines->points;
			$dayData->success = true;
			$lang = array();
			$lang['cumPowerW'] = _('Energy').' (Wh)';
			$lang['avgPowerW'] = _('avg. Power').' (W)';
			$lang['harvested'] = _('harvested').' (kW)';
			$lang['cumulative'] = _('cumulative').' (kW)';
			$lang['generated'] = _('generated');
			$lang['max'] = _('max');

			$data['lang'] = $lang;
			$data['dayData'] = $dayData;
			break;
		case 'getDetailsGraph':
			$lines = $dataAdapter->getDetailsHistory($devicenum,$date);
				
			$dayData = new DayDataResult();
			$dayData->data = $lines['details'];
			$dayData->labels = $lines['labels'];
			$dayData->switches = $lines['switches'];
			$dayData->max = $lines['max'];

			$lang = array();
			$lang['showHideGroups'] = _('Show or hide graph groups:');
			$lang['P'] = _('Power');
			$lang['V'] = _('Voltage');
			$lang['A'] = _('Amps');
			$lang['F'] = _('Frequency');
			$lang['R'] = _('Ratio');
			$lang['T'] = _('Temperature');
			$lang['E'] = _('Efficiency');

			$data['lang'] = $lang;
			$data['dayData'] = $dayData;
			break;
		case 'getPeriodFilter':
			$dayData = new DayDataResult();
			$options = array();
			if($type=="all" ){
				$options[] =array( "value" => "Today","name"=> _("Day"));
				$options[] =array( "value" => "Week","name"=> _("Week"));
				$options[] =array( "value" => "Month","name"=> _("Month"));
				$options[] =array( "value" => "Year","name"=> _("Year"));
			}else{
				$options[] =array( "value" => "Today","name"=> _("Day"));
			}
			
			
			foreach ($config->devices as $device){
				//if($device->graphOnFrontend){
				$data['devices'][] = array('id'=>$device->id,'name'=>$device->name);
				//}
			}
			$lang = array();
			$lang['date'] = _('date');
			$lang['inv'] = _('inv');
			$lang['periode'] = _('periode');
			$lang['previous'] = _('previous');
			$lang['next'] = _('next');
			$dayData->success = true;
			$data['lang'] = $lang;
			$data['options'] = $options;
			$data['dayData'] = $dayData;
			break;
		case 'getDetailsSwitches':
			$dayData = new DayDataResult();
			$lang = array();
			$lang['showHideGroups'] = _('Show or hide graph groups:');
			$lang['P'] = _('Power');
			$lang['V'] = _('Voltage');
			$lang['A'] = _('Amps');
			$lang['F'] = _('Frequency');
			$lang['R'] = _('Ratio');
			$lang['T'] = _('Temperature');
			$lang['E'] = _('Efficiency');
			$dayData->success = true;
			$data['lang'] = $lang;
			$data['dayData'] = $dayData;
				
			break;
		case 'getProductionGraph':
			(!$year)?$year=date("Y"):$year=$year;
			(!$devicenum)?$devicenum=1:$devicenum=$devicenum;
			
			$lines = $dataAdapter->getYearSumPowerPerMonth($devicenum, $year);

			$dayData = new DayDataResult();
			$dayData->data = $lines['energy']->points;
			$dayData->ticks = (isset($lines['energy']->ticks)) ? $lines['energy']->ticks : 10;
			$lang['month'] = _('month');
			$lang['expected'] = _('expected');
			$lang['harvested'] = _('harvested');
			$lang['difference'] = _('difference');
			$lang['cum'] = _('cum.');
			$dayData->success = true;
			$data['lang'] = $lang;
			$data['dayData'] = $dayData;
			break;
		case 'getPageYearValues':
			$maxEnergy=array();
			$energy=array();
			$maxPower=array();
			$minMaxEnergyYear=array();
			
			foreach ($config->devices as $device){
				if($device->type == 'production'){
					$maxEnergy[] = $dataAdapter->getYearMaxEnergyPerMonth($device->id,$date,$device);
					$energy[] = $dataAdapter->getYearEnergyPerMonth($device->id,$date,$device);
					$maxPower[] = $dataAdapter->getYearMaxPowerPerMonth($device->id,$date,$device);
					$maxMinEnergyYear[] = $dataAdapter->getMaxMinEnergyYear($device->id,$date,$device);
				}
			}
			$lang = array();
			$lang['today'] 			= _("Today");
			$lang['maxGridPower'] 	= _("Max Grid Power");
			$lang['date'] 			= _("date");
			$lang['selectPeriod']	= _("Select period");
			$lang['totalKWh'] 		= _("TotalKWh");
			$lang['inv'] 			= _("Inv");
			$lang['kwh'] 			= _("kwh");
			$lang['historyValues'] 	= _("History values");
			$lang['loading'] 		= _("loading")."...";
			$lang['watt'] 			= _("watt");
			$lang['Year']			= _("Year");
			$lang['valuesGroupedByMonthYearText'] = _("The values below are grouped by Month of the selected Year");

			$data['lang'] = $lang;
			$dayData = new DayDataResult();
			$dayData->data = array(
					"maxPower"=>$maxPower,
					"energy"=>$energy,
					"minMaxEnergy"=>$maxMinEnergyYear,
					"maxEnergy"=>$maxEnergy,
			);
			$dayData->success = true;

			$data['yearData'] = $dayData;
			break;
		case 'getPageMonthValues':
			$dayData = array();
			foreach ($config->devices as $device){
				if ($device->type == "production") {
					$maxPower = $dataAdapter->getMonthMaxPowerPerDay($device->id, $date);
					$maxEnergy = $dataAdapter->getMonthEnergyPerDay($device->id, $date);
					$minMaxEnergyMonth = $dataAdapter->getMaxMinEnergyMonth($device->id,$date);

					$deviceData = array(
							"maxPower"=>$maxPower,
							"maxEnergy"=>$maxEnergy,
							"minMaxEnergy"=>$minMaxEnergyMonth,
							"device"=>$device
					);
						
					$dayData[] = $deviceData;
				}
			}

			$lang = array();
			$lang['today'] 			= _("Today");
			$lang['maxGridPower'] 	= _("Max Grid Power");
			$lang['date'] 			= _("date");
			$lang['selectPeriod']	= _("Select period");
			$lang['totalKWh'] 		= _("TotalKWh");
			$lang['inv'] 			= _("Inv");
			$lang['kwh'] 			= _("kwh");
			$lang['historyValues'] 	= _("History values");
			$lang['loading'] 		= _("loading")."...";
			$lang['watt'] 			= _("watt");
			$lang['Month']			= _("Month");
			$lang['month']			= strtolower(_("Month"));
			$lang['valuesGroupedByDayMonthText'] = _("The values below are grouped by Day of the selected Month");
				
			$data['success'] = true;
			$data['lang'] = $lang;
			$data['monthData'] = $dayData;
			break;
		case 'getPageTodayValues':
			$maxEnergy = array();
			$maxPower = array();
			foreach ($config->devices as $device){
				if($device->type == 'production'){
					$maxEnergy[] = $dataAdapter->getDayEnergyPerDay($device);
					$maxPower[] = $dataAdapter->getDayMaxPowerPerDay($device);
				}
			}

			$dayData = new DayDataResult();
			$dayData->data = array(
					"maxPower"=>$maxPower,
					"maxEnergy"=>$maxEnergy,
			);
			$dayData->success = true;
			$data['dayData'] = $dayData;
				
			$lang = array();
			$lang['today'] 			= _("Today");
			$lang['maxGridPower'] 	= _("Max Grid Power");
			$lang['date'] 			= _("date");
			$lang['time'] 			= _("time");
			$lang['totalKWh'] 		= _("TotalKWh");
			$lang['inv'] 			= _("Inv");
			$lang['kwh'] 			= _("kwh");
			$lang['historyValues'] 	= _("History values");
			$lang['loading'] 		= _("loading")."...";
			$lang['watt'] 			= _("watt");
			$data['lang'] = $lang;
			break;
		case 'getCompareFilters':
			$monthYear = $dataAdapter->getYearsMonthCompareFilters($config);
			$lang = array();
			$lang['inverter'] 		= _("inverter");
			$lang['compare'] 		= _("compare");
			$lang['to'] 			= _("to");
			$lang['expected'] 		= _("expected");
			$data['lang'] = $lang;
			
			
			foreach ($config->devices as $device){
				if($device->type == "production"){
					$inverter[] = array("name"=>$device->name,"id"=>$device->id);
				}
			}
				
			$dayData = new DayDataResult();
			$dayData->month = $monthYear['month'];
			$dayData->year = $monthYear['year'];
			$dayData->inverters = $inverter;

			$dayData->success = true;
			$data['dayData'] = $dayData;
				
			break;
		case 'getCompareGraph':
			$whichMonth = Common::getValue('whichMonth', 0);
			$whichYear = Common::getValue('whichYear', 0);
			$compareMonth = Common::getValue('compareMonth', 0);
			$compareYear = Common::getValue('compareYear', 0);

			foreach ($config->devices as $device){
				$inverter[] = array("name"=>$device->name,"id"=>$device->id);
			}
			$compareService = new CompareService();
			$dayData = new DayDataResult();
			$options['deviceNum'] = $devicenum;
			$options['type'] = $type;
			$options['date'] = $date;
			$options['mode'] = 'frontend';
			//$data['graph'] = $graphService->loadGraph($options);
			$lines = $compareService->getCompareGraph($devicenum, $whichMonth,$whichYear,$compareMonth,$compareYear);
				
			$lang = array();
			$lang['today'] = _("Today");
			$lang['maxGridPower'] = _("Max Grid Power");
			$lang['date'] = _("date");
			$lang['totalKWh'] = _("TotalKWh");
			$lang['inv'] = _("Inv");
			$lang['kwh'] = _("kwh");
			$lang['historyValues'] = _("History values");
			$lang['loading'] = _("loading")."...";
			$lang['watt'] = _("watt");
			$lang['month'] = _('month');
			$lang['expected'] = _('expected');
			$lang['CumHarvested'] = _('cum.')." "._('harvested');
			$lang['CumExpected'] = _('cum.')." "._('expected');
			$lang['harvested'] = _('harvested');
			$lang['difference'] = _('difference');
			$lang['CumDifference'] =  _('cum.')." "._('difference');
			$data['lang'] = $lang;

			$dayData = new DayDataResult();
			$dayData->inverters = $inverter;
			$dayData->data = array(
					"which"=>$lines['whichBeans']->points,
					"compare"=>$lines['compareBeans']->points,
					"diff"=>$lines['whichCompareDiff']
			);
			$dayData->type = $lines['type'];
			$dayData->success = true;
			$data['dayData'] = $dayData;
			break;
		case 'getHistoryValues':
			($devicenum!=0) ? $devicenum = $devicenum : $devicenum = 0;
			$history = $dataAdapter->getDayHistoryPerRecord($devicenum,$config);
			$c = 0;

			$dayData = new DayDataResult();
			$dayData->history = $history;
			$dayData->success = true;
			$data['dayData'] = $dayData;
			break;
		case 'getPageLiveValues':
			$lang = array();
			$lang['someFigures'] 	= _("Some Figures*");
			$lang['by']			 	= _("By");
			$lang['status']			= _("status");
			$lang['IT'] 			= _("IT");
			$lang['total'] 			= _("Total");
			$lang['AvgDay']	 	= _("Avg. Day");
			$lang['today'] 			= _("Today");
			$lang['week'] 			= _("Week");
			$lang['month'] 			= _("Month");
			$lang['year'] 			= _("Year");
			$lang['overall'] 		= _("overall");
			$lang['inv'] 			= _("Inv.");
			$lang['grid'] 		= _("grid");
			$lang['time'] 			= _("Time");
			$lang['inverter'] 		= _("Inverter");
			$lang['power'] 			= _("Power");
			$lang['w'] 			= _("W");
			$lang['ratio'] 			= _("ratio");
			$lang['overallTotal']	= _("overall**");
			$lang['KWHKWP']			= _("kWh/kWp");
			$lang['allFiguresAreInKWH']= _("* All figures are in kWh");
			$lang['overallTotalText']= _("** Incl. initial kWh");
				
			$lang['offline'] = _("offline");
			$lang['online'] = _("online");
			$lang['standby'] = _("standby");	
			$data['lang'] = $lang;
			break;
		case 'getPageIndexLiveValues':
			$indexValues = $dataAdapter->readPageIndexLiveValues($config);
			$lang['DCPower'] = _("DC Power");
			$lang['ACPower'] = _("AC Power");
			$lang['Efficiency'] = _("Efficiency");
			// Get Gauge Max
			$avgGP = $dataAdapter->getAvgPower($config);
			($avgGP['recent']<=0) ? $avgGPRecent = 1 : $avgGPRecent = $avgGP['recent'];
			$gaugeMaxPower = ceil( ( ($avgGPRecent*1.1)+100) / 100 ) * 100;
			$data['lang'] = $lang;
			$data['devices'] = $indexValues['devices'];
			$data['maxGauges'] = $gaugeMaxPower;
			$data['sumInverters'] = $indexValues['sum'];
			break;
		case 'getPageIndexBlurLiveValues':
			$indexValues = $dataAdapter->readPageIndexLiveValues($config);
			$data['sumInverters'] = $indexValues['sum'];
			break;
		case 'getPageIndexTotalValues':
			$lang = array();
			$lang['someFigures'] 	= _("Some Figures*");
			$lang['by']			 	= _("By");

			$lang['total'] 			= _("Total");
			$lang['AvgDay']	 		= _("Avg. Day");
			$lang['today'] 			= _("Today");
			$lang['week'] 			= _("Week");
			$lang['month'] 			= _("Month");
			$lang['year'] 			= _("Year");
			$lang['overall'] 		= _("overall");

			$lang['ratio'] 			= _("ratio");
			$lang['overallTotal']	= _("overall**");
			$lang['KWHKWP']			= _("kWh/kWp");
			$lang['allFiguresAreInKWH']= _("* All figures are in kWh");
			$lang['overallTotalText']= _("** Incl. initial kWh");

			$data['IndexValues'] = $dataAdapter->readPageIndexData();
			$data['lang'] = $lang;
			break;
		case "fireHook":
			$hookname = Common::getValue("name", "");
			if ($hookname != "") {
				echo ("Fire hook: " . $hookname);
				HookHandler::getInstance()->fire($hookname);
			}
			break;
		case "installSmartMeter":
			$SmartMeterAddon = new SmartMeterAddon();
			$SmartMeterAddon->installAddon();
			//var_dump($versions);
		case "kostal":
			$kostal = new KostalPiko('python /home/marco/piko/Piko_dev.py --host=hansenmieke.dlinkddns.com --port=9996', '-csv -q', $port, $comoption, false);
			$data['live'] = $kostal->getLiveData();
				
			break;
		case "save-graph":
			$graph = new Graph();
			$graphService = new GraphService();
			$graph->name = Common::getValue("name", "");
			$graphService->save($graph);
			//var_dump($versions);
			break;
		case "loadDaily":
			$graph = new Graph();
			$graphService = new GraphService();
			$data = $graphService->loadDaily();
			break;
		case "phpinfo":
			$phpinfo = new SMASpotWSL('/home/pi/smaspot/bin/Release/./SMAspot', '/home/pi/smaspot/bin/Release/test.cfg', '', '', false);
			//var_dump($phpinfo->phpInfo());
			$weather = new WeatherService();
			$averages = $weather->saveDailyAverages();

			break;
		default:
			break;
	}
	

	echo json_encode($data);
} catch (Exception $e) {
	// get ErrorMessage
	$errorMessage = $e->getMessage();
	// log ErrorMessage
	HookHandler::getInstance()->fire("onDebug",$errorMessage);

	// set JSON responses
	$data = array();
	$data["success"] = false;
	$data["message"] = $errorMessage;
	echo json_encode($data);
}
exit();
?>