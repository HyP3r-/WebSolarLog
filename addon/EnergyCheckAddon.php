<?php
class EnergyCheckAddon {
	private $adapter;
	private $config;
	
	function __construct() {
		$this->adapter = PDODataAdapter::getInstance();
		$this->config = Session::getConfig();
	}
	
	function __destruct() {
		$this->config = null;
		$this->adapter = null;
	}
	
	public function checkEnergy() {
		$lines = file("testdata/energy.dat");
		$this->checkInverterData($lines);
	}

	private function checkInverterData($lines) {
		$beginstamp = microtime();
		echo("energy lines found: " . count($lines) . "\n");
		
		$arEnergyInverter = array();
		foreach ($lines as $line) {
			//echo ($line . "\n");
			$parts = preg_split('/\s+/', $line);
			$value = ((double) $parts[1]) * 10; // We need to multiply this value by 10 (maybe not for all inverters)
			if ($parts[0] != "") {
				$arEnergyInverter[] = array("STDE"=>$parts[0], "value"=>$value, "time"=>strtotime($parts[0]));
			}	
		}
		
		//print_r($arEnergyInverter); 		
		
		$end = reset($arEnergyInverter);
		$begin = end($arEnergyInverter);
		
		// Get 10 days before and after in the database
		$beginTime = mktime(0,0,0,date('m', $begin['time']),date('d', $begin['time'])-10,date('y', $begin['time']));
		$endTime = mktime(23,59,59,date('m', $end['time']),date('d', $end['time'])+10,date('y', $end['time']));
		
		$sql = "SELECT * FROM energy WHERE time >= :beginDate AND time <= :endDate";
		$arEnergyDB = R::getAll($sql, array($beginTime, $endTime));
		echo("energy db lines found: " . count($arEnergyDB) . "\n");
		foreach ($arEnergyInverter as $energyInverter) {
			$day = date('d', $energyInverter['time']);
			$month = date('m', $energyInverter['time']);
			$year = date('y', $energyInverter['time']);
			$kwh = (double) $energyInverter['value'];
			$found = false;
			foreach ($arEnergyDB as $energyDB) {
				if (date('y', $energyDB['time']) == $year && date('m', $energyDB['time']) == $month && date('d', $energyDB['time']) == $day) {
					$dbkwh = (double) $energyDB['KWH'];
					//echo ("date: " . date('y m d', $energyDB['time']) . " --> ");
					//echo("match \n");
					if ($kwh !== $dbkwh) {
						echo ("date: " . date('Y-m-d H:i:s', $energyInverter['time']) . " --> difference : " . $kwh . " <> " . $dbkwh . "\n");
					}
					echo ("date: " . date('Y-m-d H:i:s', $energyInverter['time']) . " --> test: " . $kwh . " <> " . $dbkwh . "\n");
					
					
					$found = true;
					break;
					
				}
			}
			if ($found == false) {
				echo ("date: " . date('Y-m-d H:i:s', $energyInverter['time']) . " --> " . $kwh . " kwh , record not found \n");
			}
		}
		echo (microtime() - $beginstamp);
	}
	
	
	/*
	 
	  R::getAll("SELECT INV,MAX ( kwh ) AS kWh, strftime ( '%d-%m-%Y' , date ( time , 'unixepoch' ) ) AS date
					FROM energy 
					WHERE time > :beginDate 
					AND time < :endDate 
					GROUP BY strftime ( 'd%-%m-%Y' , date ( time , 'unixepoch' ) ) 
					order by time DESC",array(':endDate'=>$beginEndDate['endDate'],':beginDate'=>$beginEndDate['beginDate']));
					
	*/
}