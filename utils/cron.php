<?php
error_reporting(E_ALL);

sleep(10); // Sleep 10 seconds as the other one	is still running (minimize overlap)

$startTime = time(); // start time in seconds

$docRoot = dirname(dirname(__FILE__));
require_once $docRoot . '/classes/classloader.php';
Session::initialize();

$historyUpdateRate = 5 * 60; // 5 minute refreshrate
$historyStartTime = Util::createTimeForWholeInterval($historyUpdateRate);

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

// PVoutput every 2,5 minutes 
QueueServer::getInstance()->add(new QueueItem(time() + 20, "PvOutputAddon.onJob","", true, 70));

// This one is needed for the cron script only !!! (More then 60 seconds, else we could mis an history record)
QueueServer::getInstance()->add(new QueueItem(time() + 70, "Common.exitCronProcess", "", false, 0, false));

// Start the queue server
QueueServer::getInstance()->start();

?>