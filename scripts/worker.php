<?php
error_reporting(E_ALL);

$docRoot = dirname(dirname(__FILE__));
require_once $docRoot . '/admin/classes/classloader.php';
Session::initialize();

// Check if there is already an worker running
$pid = new Pid(dirname(__FILE__));
if($pid->isAlreadyRunning) {
	echo "Already running.\n";
	exit;
}

// Start the main loop
$workHandler = new WorkHandler();
$count = 0;
while ($count < 3000) {
	if ($count % 10 == 0) {
		checkPauseAndRestart();
		$workHandler = null;
		$workHandler = new WorkHandler();
	}
	try {
		$workHandler->start();
	} catch (Exception $e) {
		echo ($e->getMessage());
	}
	$count++;
	sleep(1);
	
}
echo (date("Ymd His") . "\tAuto restart worker\n");
exit;


function checkPauseAndRestart() {
	$config = Session::getConfig(true);
	if ($config->pauseWorker) {
		while ($config->pauseWorker) {
			sleep(5);
			$config = Session::getConfig(true);
			echo("Worker paused\n");
		}
	}
	if ($config->restartWorker) {
		$config->restartWorker = false;
		PDODataAdapter::getInstance()->writeConfig($config);
		sleep (1);
		exit("Restarting worker\n");
	}
} 
?>