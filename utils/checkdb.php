<?php
set_time_limit (0); // No time limt
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once '../admin/classes/classloader.php';

$starttime = time();

// Initialize adapter and config
$adapter = PDODataAdapter::getInstance();
$config = Session::getConfig();

echo ("If you get an memory exception, just restart it will continue<br/>");
echo ("Most off the queries have limit 1000, so its safe to run this script multiple times.<br/><br/>");


// Search for empty time values
$tableString = "energy,history,event,pMaxOTD";
$tables = explode(",", $tableString);
foreach ($tables as $table) {
    echo ("Converting " . $table . "......... ");
    $beans =  R::find($table, "time = 0 OR time = '' OR time is null limit 1000");
    $i = 0;
    R::begin();
    foreach ($beans as $bean) {
        if ($bean['time'] == null || $bean['time'] == 0 || trim($bean['time']) == "") {
            $time = Util::getUTCdate($bean['SDTE']);
            if ($time == 0) {
                $time = strtotime(trim($bean['SDTE']));
            }
            $bean['time'] = $time;
            R::store($bean);
            $i++;
            // Flush after 50 lines
            if ($i % 500 == 0) {
                R::commit();
                R::begin();
            }
        }
    }
    R::commit();
    echo ("Updated " . $i . " time records<br/>");
}

// Search for empty CO2 values
$beans =  R::find("energy", "	co2 = 0 OR co2 = '' OR co2 is null limit 1000");
$i = 0;
R::begin();
foreach ($beans as $bean) {
	if ($bean['co2'] == null || $bean['co2'] == '') {
		$bean['co2'] = Formulas::CO2kWh($bean['KWH'], Session::getConfig()->co2kwh);
		R::store($bean);
		$i++;
	}
	// Flush after 50 lines
	if ($i % 500 == 0) {
		R::commit();
		R::begin();
	}
}
R::commit();
echo ("Updated " . $i . " co2 records<br/>");

// Search for empty ratio values
$result = 1000;
$total = 0;
while ($result == 1000) {
	$result = checkHistoryRatio();
	$total = $total + $result;
}
echo ("Updated " . $total . " ratio records<br/>");


$secs = time() - $starttime;
echo ("Script duration (m:s): " . ($secs / 60 % 60) . ":" . ($secs % 60) . "<br/>" );

exit();

function checkHistoryRatio() {
	$beans =  R::find("history", " I1Ratio = '' OR I1Ratio is null OR I2Ratio = '' OR I2Ratio is null limit 1000");
	$i = 0;
	R::begin();
	foreach ($beans as $bean) {
		// Calculate the ratio fields
		$IP = $bean['I1P']+$bean['I2P'];
		
		// Prevent division by zero error
		if (!empty($IP)) {
			$bean['I1Ratio'] = ($bean['I1P']/$IP)*100;
			$bean['I2Ratio'] = ($bean['I2P']/$IP)*100;
		} else {
			$bean['I1Ratio'] = 0;
			$bean['I2Ratio'] = 0;
		}
		R::store($bean);
		$i++;
	
		// Flush after 50 lines
		if ($i % 500 == 0) {
			R::commit();
			R::begin();
		}
	}
	R::commit();
	return $i;
}

?>