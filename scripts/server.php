<?php
error_reporting(E_ALL);
// clear old session logs


$docRoot = dirname(dirname(__FILE__));
require_once $docRoot . '/classes/classloader.php';
Session::initialize();

// Check if there is already an worker running
$pid = new Pid(dirname(__FILE__));
if($pid->isAlreadyRunning) {
	echo "Already running.\n";
	exit;
}

$energyUpdateRate = 10 * 60; // 10 minute refreshrate
$energyStartTime = Util::createTimeForWholeInterval($energyUpdateRate);

$infoUpdateRate = 24 * 60 * 60; // add 24 hours
$infoStartTime = Util::createOnceADayJob("12", "00"); // Only run at 12:00

$alarmUpdateRate = 15 * 60; // every quarter
$alarmStartTime = Util::createTimeForWholeInterval($alarmUpdateRate);

$historyDataUpdateRate = 24 * 60 * 60;
$historyDataStartTime = Util::createOnceADayJob("12", "30"); // Only run at 12:30

// Create the device jobs
foreach (Session::getConfig()->devices as $device) {
	// TODO
 	$historyUpdateRate = (empty($device->historyRate)) ? 300 : $device->historyRate; // prevent faster interval then 60sec.
	$historyStartTime = Util::createTimeForWholeInterval($historyUpdateRate);
	
	QueueServer::getInstance()->add(new QueueItem(time(), "DeviceHandler.handleLive", array($device), true, $device->refreshTime));
	QueueServer::getInstance()->add(new QueueItem($historyStartTime, "DeviceHandler.handleHistory", array($device), true, $historyUpdateRate));
	QueueServer::getInstance()->add(new QueueItem($energyStartTime, "DeviceHandler.handleEnergy", array($device), true, $energyUpdateRate));
	QueueServer::getInstance()->add(new QueueItem($infoStartTime, "DeviceHandler.handleInfo", array($device), true, $infoUpdateRate));
	QueueServer::getInstance()->add(new QueueItem($alarmStartTime, "DeviceHandler.handleAlarm", array($device), true, $alarmUpdateRate));
	QueueServer::getInstance()->add(new QueueItem($historyDataStartTime, "DeviceHandler.handleDeviceHistory", array($device), true, $historyDataUpdateRate));
}

//special job for running the cache totals
$cacheInputRate = Session::getConfig()->cacheInputRate; 
$cacheJobUpdateRate = (empty($cacheInputRate) OR $cacheInputRate < 60) ? 60 : $cacheInputRate; // prevent faster interval then 60sec.
$cacheStartTime = Util::createTimeForWholeInterval($cacheJobUpdateRate);
QueueServer::getInstance()->add(new QueueItem($cacheStartTime, "HookHandler.fireFromQueue", array("onCacheJob"), true, $cacheJobUpdateRate));

$fastJobUpdateRate = 60; // Every minute
$fastJobStartTime = Util::createTimeForWholeInterval($fastJobUpdateRate);
QueueServer::getInstance()->add(new QueueItem($fastJobStartTime, "HookHandler.fireFromQueue", array("onFastJob"), true, $fastJobUpdateRate));

$regularJobUpdateRate = 30 * 60; // Every 30 minutes
$regularJobStartTime = Util::createTimeForWholeInterval($regularJobUpdateRate);
QueueServer::getInstance()->add(new QueueItem($regularJobStartTime, "HookHandler.fireFromQueue", array("onRegularJob"), true, $regularJobUpdateRate));

$slowJobUpdateRate = 60 * 60; // Every hour
$slowJobStartTime = Util::createTimeForWholeInterval($slowJobUpdateRate);
QueueServer::getInstance()->add(new QueueItem($slowJobStartTime, "HookHandler.fireFromQueue", array("onSlowJob"), true, $slowJobUpdateRate));

// Add job to refresh the config object every 2 minutes
QueueServer::getInstance()->add(new QueueItem(Util::createTimeForWholeInterval(120), "Common.refreshConfig", "", true, 120));

// Add job to check for restart every 5 minutes
// TODO :: When we support db queue then we should not check for it
QueueServer::getInstance()->add(new QueueItem(Util::createTimeForWholeInterval(300), "Common.checkRestart", "", true, 300));

// Janitor clean 1x per uur
QueueServer::getInstance()->add(new QueueItem(Util::createTimeForWholeInterval(3600), "JanitorRest.clean", "", true, 3600));

// PVoutput every 2,5 minutes 
QueueServer::getInstance()->add(new QueueItem(Util::createTimeForWholeInterval(75), "PvOutputAddon.onJob","", true, 75));

// PVoutput Join WSL Team every day@00:15 and repeat it every 6 hours
$PVoutputJoinTeamUpdateRate = 6 * 60 * 60;
$PVoutputJoinTeamStartTime = Util::createOnceADayJob("00", "15"); // Only run at 00:15
QueueServer::getInstance()->add(new QueueItem($PVoutputJoinTeamStartTime, "PvOutputAddon.joinAllDevicesToTeam","", true, $PVoutputJoinTeamUpdateRate));


// run Janitor DBcheck every day@01:00 
$checkDataBaseStartTime = Util::createOnceADayJob("01", "00"); // Only run at night
QueueServer::getInstance()->add(new QueueItem($checkDataBaseStartTime, "JanitorRest.DbCheck", "", true, 24 * 60 * 60));

// Start the queue server
QueueServer::getInstance()->start();


?>