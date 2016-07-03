<?php
class CsvDataAdapter extends CsvWriter {

	
	/**
	 *
	 *
	 * LiveInfo
	 *
	 *
	 */
	
	
	/**
	 * write the live info to the file
	 * @param int $invtnum
	 * @param Live $live
	 */
	public function writeLiveInfo($invtnum, Live $live) {
		$filename = Util::getLiveTXT($invtnum);
		$this->writeCsvData($filename, $this->getLiveCsvString($live));
	}

	/**
	 * read the live info from an file
	 * @param int $invtnum
	 * @return Live
	 */
	public function readLiveInfo($invtnum) {
		$filename = Util::getLiveTXT($invtnum);
		$lines = $this->readCsvData($filename);
		return $this->parseCsvToLive($lines[0]);
	}

	/**
	 * will remove the live file
	 */
	public function dropLiveInfo($invtnum) {
		unlink(Util::getLiveTXT($invtnum));
	}

	public function parseCsvToLive($csv) {
		// Convert comma to dot
		$csv = str_replace(",", ".", $csv);

		$fields = explode(";", $csv);
		$live = new Live();
		$live->SDTE = Util::getUTCdate($fields[0]) * 1000;
		$live->I1V = $fields[1];
		$live->I1A = $fields[2];
		$live->I1P = $fields[3];
		$live->I2V = $fields[4];
		$live->I2A = $fields[5];
		$live->I2P = $fields[6];
		$live->GV = $fields[7];
		$live->GA = $fields[8];
		$live->GP = $fields[9];
		$live->FRQ = $fields[10];
		$live->EFF = $fields[11];
		$live->INVT = $fields[12];
		$live->BOOT = $fields[13];
		$live->KWHT = $fields[14];

		return $live;
	}

	public function getLiveCsvString(Live $live) {
		$line = "" . $live->SDTE . ";" . $live->I1V . ";" . $live->I1A . ";" . $live->I1P . ";" .
				$live->I2V . ";" . $live->I2A . ";" . $live->I2P . ";" . $live->GV . ";" . $live->GA . ";" . $live->GP . ";" .
				$live->FRQ . ";" . $live->EFF . ";" . $live->INVT . ";" . $live->BOOT . ";" . $live->KWHT;
		return str_replace(".", ",", $line); // Convert the dots to comma
	}
	
	/**
	 *
	 *
	 * Daily
	 *
	 *
	 */
	
	/**
	 * write the daily data to a file
	 * @param int $invtnum
	 * @param Live $live
	 */
	public function writeDailyData($invtnum, Day $day) {
		$filename = Util::getDailyTXT($invtnum);
		$this->writeCsvData($filename, $this->getDailyDataCsvString($live));
	}
	
	/**
	 * will remove the live file
	 */
	public function dropDailyData($invtnum) {
		unlink(Util::getDailyTXT($invtnum));
	}
	
	
	/**
	 * read the daily data from a CSV file
	 * @param string $date
	 * @param int $invtnum
	 * @return Day
	 */
	public function readDailyData($date,$invtnum) {
		$filename = Util::getDailyDataCSV($date,$invtnum);
		$lines = $this->readCsvData($filename);
		return $this->parseCsvToDailyData($lines);
	}
	
	public function parseCsvToDailyData($lines) {
	
		$points = array();
	
		$oldTime = 0;
		$oldKWHT = 0;
		$diffTime = 0;
		foreach ($lines as $line) {
			$line = str_replace("\n", "", $line); // remove line endings
			$line = str_replace(",", ".", $line); // Convert comma to dot
			$fields = explode(";", $line); // Create an array of fields
	
			// 20120623-05:16:00
			$rawdatetime = explode('-', $fields[0]);
			$year = substr($rawdatetime[0], 0, 4);
			$month = substr($rawdatetime[0], 4, 2);
			$day = substr($rawdatetime[0], 6, 2);
			$hour = substr($rawdatetime[1], 0, 2);
			$minute = substr($rawdatetime[1], 3, 2);
			$second = substr($rawdatetime[1], 6, 2);
				
			$kwht = $fields[14];
	
			// Convert to epoch date
			$UTCdate =  Util::getUTCdate($fields[0]);
	
			// Check time difference
			$diffTime = $UTCdate - $oldTime;
			if ($diffTime != 0) {
				$AvgPOW = Formulas::calcAveragePower($oldKWHT, $kwht, $diffTime);
			} else {
				$AvgPOW=0;
			}
	
			// Only add Points if the value changed
			if ($kwht - $oldKWHT != 0) {
				$points[] = array($year."-".$month."-".$day." ".$hour.":".$minute , $AvgPOW);
			}
	
			$oldTime = $UTCdate;
			$oldKWHT = $kwht;
	
		}
	
		$points[] = array(0,count($lines));
	
		// Calculate total kwht
		$firstFields = explode(';', str_replace(",", ".", $lines[0]));
		$lastFields = explode(';', str_replace(",", ".", $lines[count($lines) - 1]));
		$day = new Day();
		$day->points=$points;
		$day->KWHT=round($lastFields[14] - $firstFields[14], 2);
		return $day;
	}
	
	public function getDailyDataCsvString(Day $day) {
		$line = 'test';
		return str_replace(".", ",", $line); // Convert the dots to comma
	}
	
	/**
	 *
	 *
	 * LastDays
	 *
	 *
	 */
	
	/**
	 * write the daily data to a file
	 * @param int $invtnum
	 * @param Live $live
	 */
	public function writeLastDaysData($invtnum, Day $day) {
		$filename = Util::getLastDaysTXT($invtnum);
		$this->writeCsvData($filename, $this->getLastDaysDataCsvString($live));
	}
	
	/**
	 * will remove the live file
	 */
	public function dropLastDaysData($invtnum) {
		unlink(Util::getLastDaysTXT($invtnum));
	}
	
	
	/**
	 * read the daily data from a CSV file
	 * @param string $date
	 * @param int $invtnum
	 * @return Day
	 */
	public function readLastDaysData($year ,$invtnum) {
		$filename = Util::getLastDaysCSV($year, $invtnum);
		$lines = $this->readCsvData($filename);
		return $this->parseCsvToLastDaysData($lines);
	}
	
	public function parseCsvToLastDaysData($lines) {
		$config = new Config;

		$points = array();

		$j=0;
		$h=1;
		$day_num=0;

		while ($day_num<$config->PRODXDAYS) {
			//$lines=file($dir.$output[$cntcsv-$h]);
			//$countalines = count($lines);
			
			// Digging into the array
			$array = explode(";",$lines[$day_num]);
		
			$year = substr($array[0], 0, 4);
			$month = substr($array[0], 4, 2);
			$day = substr($array[0], 6, 2);

			$UTCdate = $year."-".$month."-".$day;
			$array[1] = str_replace(",", ".", $array[1]);
			$production=round(($array[1]*$config->CORRECTFACTOR),1);
			//$production = str_replace(".",",",$production);
			$points[$day_num] = array ($UTCdate, $production);
			$day_num++;
		}
		
		$lastDays = new LastDays();
		$lastDays->points=$points;
		return $lastDays;
	}
	
	public function getLastDaysDataCsvString(Day $day) {
		$line = 'test';
		return str_replace(".", ",", $line); // Convert the dots to comma
	}
	
	
	/**
	 * 
	 * 
	 * MaxPower
	 * 
	 * 
	 */
	
	

	/**
	 * write the max power today to the file
	 * @param int $invtnum
	 * @param MaxPowerToday $mpt
	 */
	public function writeMaxPowerToday($invtnum, MaxPowerToday $mpt) {
		$filename = Util::getDataDir($invtnum)."/infos/pmaxotd.txt";
		$this->writeCsvData($filename, $this->getMaxPowerTodayCsvString($mpt));
	}

	/**
	 * read the MaxPowerToday from an file
	 * @param int $invtnum
	 * @return MaxPowerToday
	 */
	public function readMaxPowerToday($invtnum) {
		$filename = Util::getDataDir($invtnum)."/infos/pmaxotd.txt";
		$lines = $this->readCsvData($filename);
		if ($lines === false) {
			return new MaxPowerToday(); // File not found
		}
		return $this->parseCsvToMaxPowerToday($lines[0]);
	}

	/**
	 * will remove the max power today file
	 * @param int $invtnum
	 */
	public function dropMaxPowerToday($invtnum) {
		unlink(Util::getDataDir($invtnum)."/infos/pmaxotd.txt");
	}

	public function parseCsvToMaxPowerToday($csv) {
		// Convert comma to dot
		$csv = str_replace(",", ".", $csv);

		$fields = explode(";", $csv);
		$mpt = new MaxPowerToday();
		$mpt->SDTE = Util::getUTCdate($fields[0]) * 1000;
		$mpt->GP = $fields[1];

		return $mpt;
	}

	public function getMaxPowerTodayCsvString(MaxPowerToday $mpt) {
		$line = "" . $mpt->SDTE . ";" . $mpt->GP;
		return str_replace(".", ",", $line); // Convert the dots to comma
	}
	
	/**
	 *
	 *
	 * History
	 *
	 *
	 */
	
	
	/**
	 * add the live info to the history
	 * @param int $invtnum
	 * @param Live $live
	 * @param string date
	 */
	public function addHistory($invtnum, Live $live, $date) {
		$filename = Util::getDataDir($invtnum) . "/csv/" . $date . ".csv";
		$this->appendCsvData($filename, $this->getLiveCsvString($live) . "\n");
	}

	/**
	 * Read the history file
	 * @param int $invtnum
	 * @param string $date
 	 * @return array<Live> $live (No Live but BEAN object!!)
     */
     // TODO :: There's no Live object returned....?!
    
	public function readHistory($invtnum, $date) {
		$result = array();
		$filename = Util::getDataDir($invtnum)."/csv/" . $date . ".csv";
		$lines = $this->readCsvData($filename);
		foreach ($lines as $line) {
			$result[] = $this->parseCsvToLive($line);
		}
		return $result;
	}

	/**
	 * Return the amount off history records
	 * @param int $invtnum
	 * @param string $date
	 * @return int $count
	 */
	public function getHistoryCount($invtnum, $date) {
		$filename = Util::getDataDir($invtnum)."/csv/" . $date . ".csv";
		$result = $this->readCsvData($filename);
		if ($result === false) {
			return 0; // File not found
		}
		return count($result);
	}

	/**
	 * add an energy line
	 * @param int $invtnum
	 * @param MaxPowerToday $energy
	 * @param int $year
	 */
	public function addEnergy($invtnum, MaxPowerToday $energy, $year) {
		$filename = Util::getDataDir($invtnum) . "/production/energy" . $year . ".csv";
		$this->appendCsvData($filename, $this->getMaxPowerTodayCsvString($energy) . "\n");
	}

	/**
	 * add the alarm to the events
	 * @param int $invtnum
	 * @param Alarm $alarm
	 */
	public function addAlarm($invtnum, Alarm $alarm) {
		$filename = Util::getDataDir($invtnum) . "/infos/alarms.txt";
		$this->appendCsvData($filename, $this->getAlarmCsvString($alarm) . "\n");
	}

	/**
	 * Read the events file
	 * @param int $invtnum
	 * @return array<Alarm> $alarm
	 */
	public function readAlarm($invtnum) {
		$result = array();
		$filename = Util::getDataDir($invtnum) . "infos/events.txt";
		$lines = $this->readCsvData($filename);
		foreach ($lines as $line) {
			$result[] = $this->parseCsvToAlarm($line);
		}
		return $result;
	}

	public function getAlarmCsvString(Alarm $alarm) {
		return "" . $alarm->time . ";" . $alarm->alarm;
	}

	public function parseCsvToAlarm($csv) {
		$fields = explode(";", $csv);
		$alarm = new Alarm();
		$alarm->time = $fields[0];
		$alarm->alarm = $fields[1];
		return $alarm;
	}
}