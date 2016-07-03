<?php
class CompareService {
	public static $tbl = "history";
	private $deviceService;


	/**
	 *
	 * @param unknown_type $deviceId
	 * @param unknown_type $whichMonth
	 * @param unknown_type $whichYear
	 * @param unknown_type $compareMonth
	 * @param unknown_type $compareYear
	 */
	public static function getCompareGraph($deviceId,$whichMonth,$whichYear,$compareMonth,$compareYear){
		$dataService = new GraphDataService();
		
		$beans = array();
		$whichBeans = array();
		$compareBeans = array();
		
		$whichDates = array();
		$compareDates = array();

		if($whichMonth >0 AND $whichYear>0){
			if ($compareYear > 1970){
				// get Which beans
					
				$beans = self::getCompareBeans($deviceId,$whichMonth,$whichYear);
				$whichBeans = $beans['line'];

				$beans = self::getCompareBeans($deviceId,$compareMonth,$compareYear);
				$compareBeans = $beans['line'];
				
				// move compareBeans to expectedBeans, so we pass it to JSON.
				$expectedBeans  = $compareBeans;
				$diff = self::getDiffCompare($whichBeans,$expectedBeans);
				$type = "energy vs energy";
			}else{
				// get Which beans				
				$beans = self::getCompareBeans($deviceId,$whichMonth,$whichYear);
				$whichBeans = $beans['line'];				

				//get expected beans
				$expectedBeans = self::expectedMonthProduction($deviceId,$compareMonth);

				$type = "energy vs expected";
				$diff = self::getDiffCompare($whichBeans,$expectedBeans);
				}
		}

		return array(
				"compareBeans"=>self::beansToGraphPoints($expectedBeans),
				"whichBeans"=>self::beansToGraphPoints($whichBeans),
				"whichCompareDiff"=>$diff,
				"type"=>$type
		);
	}



	/**
	 * return a array that can be understand by JQplot
	 * @param array $beans from $this->getGraphPoint()
	 * @return array($beginDate, $endDate);
	 */
	public static function beansToGraphPoints($beans){
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
	
	
	public static function expectedMonthProduction($invtnum,$month,$year=0){
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
	
	
	public static function getDiffCompare($whichBeans,$expectedBeans){
		$whichCount = count($whichBeans);
		$expectedCount = count($expectedBeans);
		if($whichCount>=$expectedCount){
			for ($i = 0; $i < $whichCount; $i++) {
				$diffCumCalc = $whichBeans[$i]['KWH']-$expectedBeans[$i]['KWH'];
				$diffDailyCalc = $whichBeans[$i]['harvested']-$expectedBeans[$i]['harvested'];
	
				$diffcolor = self::rangeBetweenColor($diffCumCalc, $expectedBeans[0]['KWH']);
				$diffHarvestedDayColor = self::rangeBetweenColor($diffDailyCalc, round($expectedBeans[0]['KWH']*0.2,2));
	
				$diff[] = array("diffCumCalc"=>sprintf("%01.2f",(float)$diffCumCalc),"diffDailyCalc"=>$diffDailyCalc,'diffColor'=>$diffcolor,'diffHarvestedColor'=>$diffHarvestedDayColor);
			}
		}else{
			for ($i = 0; $i < $expectedCount; $i++) {
				if (isset($whichBeans[$i]) && isset($expectedBeans[$i])) {
					$diffCumCalc = $whichBeans[$i]['KWH']-$expectedBeans[$i]['KWH'];
					$diffDailyCalc = $whichBeans[$i]['harvested']-$expectedBeans[$i]['harvested'];
	
					$diffcolor = self::rangeBetweenColor($diffCumCalc, $expectedBeans[0]['KWH']);
					$diffHarvestedDayColor = self::rangeBetweenColor($diffDailyCalc, round($expectedBeans[0]['KWH']*0.2,2));
	
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
	public static function rangeBetweenColor($compare, $to, $colors = array('orange','green','red')){
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
	 *
	 * @param unknown $month
	 * @param unknown $year
	 * @return multitype:NULL unknown
	 */
	public static function getCompareBeans($invtnum, $month,$year){
		$dataService = new PDODataAdapter();
		// init
		$counter = 0;
		$dataDays = 0;
		$first = false;
		$getCompareBeans = array();
	
		// get beans for month and year
		$beans = self::readEnergyValues($invtnum, 'month', 1, $year."-".$month."-1");
		// lose one array
		$beans = $beans[0];
		foreach ($beans as $bean) {
			$newBeans[strtotime(date("Y-m-d",$bean['time']))] = $bean;
		}
		// get last KWH value from the Which array
		$dates = $dataService->datesMonthInArray($month,$year);
	
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
	 * read Energy Values
	 * @param int $invtnum
	 * @param str $type can be: today, week, month, year
	 * @param int $count
	 * @param date $startDate
	 * @param str $maxType
	 *
	 */
	public function readEnergyValues($invtnum, $type, $count, $startDate){
		//$deviceService = new DeviceService();
		$config = Session::getConfig();
		$energyBeans = self::readTablesPeriodValues($invtnum, "energy", $type,$startDate);
		$Energy = array();
	
		$Energy['KWH'] = 0;
		$KWHT = 0;
		$cum = 0;
		$deviceService = new DeviceService();
		$invConfig = $deviceService->load($invtnum);
		foreach ($energyBeans as $energyBean){
			
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
	 * Create and run the query to getAll Values for a given Period
	 * @Param string $table
	 * @Param string $type
	 * @Param date $startDate
	 */
	public static function readTablesPeriodValues($invtnum, $table, $type, $startDate){
		$count = 0;
	
		// get the begin and end date/time
		$beginEndDate = Util::getBeginEndDate($type, $count,$startDate);
	
		if ($invtnum > 0){
			$energyBeans = R::getAll("
					SELECT *
					FROM ".$table."
					WHERE INV = :INV AND time > :beginDate AND  time < :endDate
					ORDER BY time",array(':INV'=>$invtnum,':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
		}else{
			$energyBeans = R::getAll("
					SELECT *
					FROM ".$table."
					WHERE time > :beginDate AND  time < :endDate
					ORDER BY time",array(':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate']));
		}
		//see if we have atleast 1 bean, else we make one :)
		(!$energyBeans) ? $energyBeans[0] = array('time'=>time(),'KWH'=>0,'KWHT'=>0) : $energyBeans = $energyBeans;
		return $energyBeans;
	}
}