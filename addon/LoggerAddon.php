<?php
class LoggerAddon {
	public function onError($args) {
		$message = $args[0] . " - " . $args[1];
		$this->write2file("error", $message);
	}
	
	public function onWarning($args) {
		$message = $args[0] . " - " . $args[1];
		$this->write2file("warning", $message);
	}
	
	public function onInfo($args) {
		$message = $args[0] . " - " . $args[1];
		$this->write2file("info", $message);
	}

	public function onDebug($args) {
		if (Session::getConfig()->debugmode) {
			$message = $args[0] . " - " . $args[1];
			$this->write2file("debug", $message);
		}
	}

	public function onInverterStartup($args) {
		$message = $args[0] . " - " . $args[1]->name;
		$this->write2file("debug", $message);
	}

	public function onInverterShutdown($args) {
		$message = $args[0] . " - " . $args[1]->name;
		$this->write2file("debug", $message);
	}
	
	public function onInverterError($args) {
		$message = $args[0] . " - " . $args[1]->name . " - " . $args[2];
		$this->write2file("error", $message);
	}

	public function onInverterWarning($args) {
		$message = $args[0] . " - " . $args[1]->name . " - " . $args[2];
		$this->write2file("warning", $message);
	}
	
	private function write2file($level, $message) {
		$logPath = Common::getRootPath() . "/log";
		$fileName = $logPath . "/wsl.log";
		
		$this->checkLogFile($fileName);
		
		// Check if the log directory is available
		if (!is_dir($logPath)) {
			if (!mkdir($logPath)) {
				throw new SetupException("Log directory is not available and we can't create it!<br>" . $logPath."<br>Please create the directory and make it writable with chmod.");
			}
		}
		
		// Check if the file exists and if we can write to the file
		if (file_exists($fileName) && !is_writable($fileName)) {
			throw new SetupException("Log file is not writable " . $fileName . ".<br>Please make it writable with chmod.");
		}
		
		$message = str_replace("\n", " ", $message);
		$logmsg = date("Ymd His") . "\t" . $level . "\t" . $message . "\n";
		
		$fh = fopen($fileName, "a+");
		if ($fh) {
			fwrite($fh, $logmsg);
			fclose($fh);
		}
	}
	
	private $maxLogFileSize = 10; // MB
	private $useCompression = true;
	
	private function checkLogFile($filename) {
		// Check if log file exists
		if (!file_exists($filename)) {
			$this->createLogFile($filename);
			return;
		}
		
		// Check if max log file size is enabled
		if ($this->maxLogFileSize > 0) {
			$sizeOfFile = filesize($filename) / pow(1024, 2); // MB
			if ($sizeOfFile > $this->maxLogFileSize) {
				$archive = $filename . "." . date("Ymd.His");
				rename ($filename, $archive);
				$this->createLogFile($filename);
				
				// Do we need to zip the archive?
				if ($this->useCompression) {
					file_put_contents("compress.zlib://".$archive . ".gz", file_get_contents($archive));
					unlink($archive);
				}
			}
		}
	}
	
	private function createLogFile($filename) {
		touch($filename);
		chmod($filename, octdec("0766"));
	}
}
?>