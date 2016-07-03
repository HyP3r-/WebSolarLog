<?php
class Pid {
	protected $filename;
	public $isAlreadyRunning = false;
	 
	function __construct($directory) {
		$this->filename = $directory . '/' . basename($_SERVER['PHP_SELF']) . '.pid';
		if(is_writable($this->filename) || is_writable($directory)) {
			if(file_exists($this->filename)) {
				$pid = (int)trim(file_get_contents($this->filename));
				if(posix_kill($pid, 0)) {
					$this->isAlreadyRunning = true;
				}
			}
		} else {
			die("Cannot write to pid file '$this->filename'. Program execution halted.\n");
		}
		 
		if(!$this->isAlreadyRunning) {
			$pid = getmypid();
			file_put_contents($this->filename, $pid);
		}
	}

	public function __destruct() {
		if(!$this->isAlreadyRunning && file_exists($this->filename) && is_writeable($this->filename)) {
			unlink($this->filename);
		}
	}
}
?>