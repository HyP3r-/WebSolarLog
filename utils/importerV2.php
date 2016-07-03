<?php 
set_time_limit ( 0 ); // no time limit
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once '../classes/classloader.php';
Session::initialize();
// Remove below PRAGMA lines if you don't want to use an sqlite db
// FYI: Everything is only testen on sqlite new database
R::exec("PRAGMA temp_store = 2");
R::exec("PRAGMA ignore_check_constraints = TRUE");
R::exec("PRAGMA journal_mode = MEMORY");
R::exec("PRAGMA locking_mode = EXCLUSIVE");
R::exec("PRAGMA synchronous = OFF");
R::freeze(false);

// Check CLI or browser
$force = false;
if(defined('STDIN') ) {
	//var_dump($argv);
	foreach ($argv as $keyvalue) {
		$arKeyValue = explode("=", $keyvalue);
		if ($arKeyValue[0] === "--force") {
			$force = ($arKeyValue[1] == 'true' || $arKeyValue[1] > 0) ? true : false;
		}		
	}
} else {
	$force = Common::getValue("force", false);
}

$begin = time();
ImporterLog::info("Starting import V2");
$importer = new Importer(Common::getRootPath() . "/data/", $force);
$importer->init();
$importer->start();
ImporterLog::info("Takes " . (time()-$begin) . " seconds");

class Importer {
	private $deviceService;
	
	private $path;
	private $force;
	private $devices;
	
	function __construct($path, $force) {
		$this->path = $path;
		$this->force = $force;
		$this->deviceService = new DeviceService();
	}
	
	public function init() {
		if ($this->force) {
			ImporterLog::info("Force modus is activated!");
		}
		
		// Check if there are already some devices
		$dbDevices = $this->deviceService->getAllDevices();
		if (count($dbDevices) > 0) {
			ImporterLog::info("Fatal error: This import only works with new database, existing devices found!");
			ImporterLog::info("If you are sure what you are doing then you can overwrite this with the option force=true");
			if (!$this->force) {
				exit();
			} else {
				ImporterLog::info("Force mode active, ignoring above fatal error!");
			}
		}
		
		// Save found devices
		try {
			$deviceNames = FileUtil::getDirNamesInFolder($this->path);
			foreach ($deviceNames as $deviceName) {
				if (Common::startsWith($deviceName, 'invt')) {
					$id = str_replace("invt", "", $deviceName);
					
					$dbDevice = $this->deviceService->load($id);
					$dbDevice->id = $id;
					$dbDevice->name = $deviceName;
					$dbDevice->deviceApi = "AURORA";
					$dbDevice->type = "production";
					$dbDevice->active = true;
					$this->deviceService->save($dbDevice);
					ImporterLog::info("Created device for folder: " . $deviceName);
				}
			}
		} catch (RedBean_Exception_SQL $e) {
			ImporterLog::info($e->getMessage());
			exit();
		} catch (Exception $e) {
			ImporterLog::info(get_class($e) . " :: " . $e->getMessage());
			exit();
		}
		
		// Get the config and save it, so we are sure we have an config in the database
		$config = Session::getConfig();
		PDODataAdapter::getInstance()->writeConfig($config);
	}
	
	public function start() {
		$devices = $this->deviceService->getAllDevices();
		foreach ($devices as $device) {
			$importerDevice = new ImporterDevice($this->path, $device, $this->force);
			$importerDevice->start();
		} 
	}
}

class ImporterDevice {
	private $deviceService;
	
	private $force;
	private $device;
	private $basePath;
	
	
	function __construct($path, Device $device, $force) {
		$this->device = $device;
		$this->force = $force;
		$this->basePath = $path . $this->device->name . "/";
		$this->deviceService = new DeviceService();
	}
	
	function start() {
		ImporterLog::info("Starting import off device: " . $this->device->name . "[" . $this->device->id . "]");
		$this->importHistory();
		$this->importEnergy();
	}
	
	private function importEnergy() {
		$first = true;
		$energyService = new EnergyService();
		
		$energyPath = $this->basePath . "production/";
		$files = FileUtil::getFileNamesInFolder($energyPath);
		foreach ($files as $file) {
			if (Common::startsWith($file, "energy") && Common::endsWith($file, ".csv")) {
				$lines = file($energyPath . $file);
				ImporterLog::info("Starting import energy file: " . $file . ' lines: ' . count($lines));
				foreach ($lines as $line) {
					if ($first) {
						$first = false;
						R::freeze(false);
					} else {
						R::freeze(true);				
					}
					$lineParts = explode (",", $line);
					$date = Util::getUTCdate($lineParts[0]);
					$kwh = $lineParts[1];
					
					$energy = new Energy();
					$energy->deviceId = $this->device->id;
					$energy->SDTE = $lineParts[0];
					$energy->time = $date;
					$energy->INV = $this->device->id;
					$energy->KWH = $kwh;
					$energy->KWHT = -1;
					$energy->co2 = Formulas::CO2kWh($kwh, Session::getConfig()->co2kwh);
					
					if ($this->force) {
						// in force mode we can already have lines
						$energyService->addOrUpdateEnergyByDeviceAndTime($energy, $date);
					} else {
						$energyService->save($energy);
					}
				}
			}
		}
	}
	
	private function importHistory() {
		$first = true;
		$historyService = new HistoryService();
		$historyPath = $this->basePath . "csv/";
		$files = FileUtil::getFileNamesInFolder($historyPath);
		$index = 1;
		$total = count($files);
		foreach ($files as $file) {
			if (Common::endsWith($file, ".csv")) {
				$fileDate = str_replace(".csv", "", $file);
				$lines = file($historyPath . $file);
				ImporterLog::info("[".$index."/".$total."] Starting import history file: " . $file . ' lines: ' . count($lines));
				foreach ($lines as $line) {
					//Time,I1V,I1A,I1P,I2V,I2A,I2P,GV,GA,GP,FRQ,EFF,INVT,BOOT,KWHT
					$lineParts = explode (",", $line);
					$date = Util::getUTCdate($fileDate . " " . $lineParts[0]);
					
					$history = new History();
					$history->deviceId = $this->device->id;
					$history->INV = $this->device->id;
					$history->SDTE = date("Ymd-H:i:s",$date);
					$history->time = $date;
					$history->dayNum = date("z", $history->time) + 1;
					$history->I1V = $lineParts[1];
					$history->I1A = $lineParts[2];
					$history->I1P = $lineParts[3];
					$history->I2V = $lineParts[4];
					$history->I2A = $lineParts[5];
					$history->I2P = $lineParts[6];
					$history->I3V = 0;
					$history->I3A = 0;
					$history->I3P = 0;
					$history->GV = $lineParts[7];
					$history->GA = $lineParts[8];
					$history->GP = $lineParts[9];
					
					$history->FRQ = $lineParts[10];
					$history->EFF = $lineParts[11];
					$history->INVT = $lineParts[12];
					$history->BOOT = $lineParts[13];
					$history->KWHT = $lineParts[14];
					
					$history->IP = $history->I1P + $history->I2P + $history->I3P;
					if (!empty($history->IP)) {
						$history->I1Ratio = round(($history->I1P/$history->IP)*100,3);
						$history->I2Ratio = round(($history->I2P/$history->IP)*100,3);
						$history->I3Ratio = round(($history->I3P/$history->IP)*100,3);
					}
					
					// Skip empty/header lines
					if ($history->time > 0 || $history->GV > 0 || $history->GP > 0 || $history->GA > 0) {
						if ($first) {
							$first = false;
							R::freeze(false);
						} else {
							R::freeze(true);
						}						
						$historyService->save($history);
					}
				}
				$index++;
			}
		}		
		
	}
}

class ImporterLog {
	private $linebreakchar;
	private static $instance;
	
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new ImporterLog();
		}
		return self::$instance;
	}
	
	public static function info($message) {
		self::getInstance()->output($message);
	}
	
	function __construct() {
		$this->linebreakchar = (defined('STDIN')) ? "\n" : "<br />";
	}
	
	public function output($message) {
		echo ($message . $this->linebreakchar);
	}
	
	
}
?>