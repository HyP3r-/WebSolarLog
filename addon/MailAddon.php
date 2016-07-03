<?php
class MailAddon {
	public function onError($args) {
		$subject = "WSL :: Error message";
		$body = $this->createSystemEventReport("error", $args[1]);
		Common::sendMail($subject, $body, Session::getConfig());
	}
	
	public function onWarning($args) {
		$subject = "WSL :: Warning message";
		$body = $this->createSystemEventReport("warning", $args[1]);
		Common::sendMail($subject, $body, Session::getConfig());
	}
	
	public function onInverterStartup($args) {
		/*
		$hookname = $args[0];
		$device = $args[1];
		if (Session::getConfig()->emailReports) {
			Common::sendMail("WSL :: Startup", "Startup test", Session::getConfig());
		}
		*/
	}
	
	public function onInverterShutdown($args) {
		$hookname = $args[0];
		$device = $args[1];
		HookHandler::getInstance()->fire("onDebug",__METHOD__."::End day Report:: Inverter Shutdown");
		HookHandler::getInstance()->fire("onDebug",__METHOD__."::End day Report::args::".print_r($args,true));
		if (Session::getConfig()->emailReports) {
			HookHandler::getInstance()->fire("onDebug",__METHOD__."::End day Report:: Config says; you may send!");
			$title = "WSL :: Shutdown inverter " . $device->name;
			$body = $this->createReport($device);

			Common::sendMail($title, $body, Session::getConfig());		
		}else{
			HookHandler::getInstance()->fire("onDebug",__METHOD__."::End day Report:: Config says; you may NOT send!");
		}
	}
	
	public function onInverterError($args) {
		if (Session::getConfig()->emailAlarms) {
			$subject = "WSL :: Error message";
			$body = $this->createInverterEventReport($args[1], "error", $args[2]);
			Common::sendMail($subject, $body, Session::getConfig());
		}	
	}
	
	public function onInverterWarning($args) {
		if (Session::getConfig()->emailEvents) {
			$subject = "WSL :: Warning message";
			$body = $this->createInverterEventReport($args[1], "warning", $args[2]);
			Common::sendMail($subject, $body, Session::getConfig());
		}	
		
	}
	
	private function createInverterEventReport(Device $device, $eventType, $eventText) {
		$report = file_get_contents(Session::getBasePath() . "/reports/email/inverterEvent_en.inc");
		$report = str_replace("{{inverter.name}}", $device->name, $report);
		$report = str_replace("{{event.type}}", $eventType, $report);
		$report = str_replace("{{event.text}}", $eventText, $report);
		return $report;
	}
	
	private function createSystemEventReport($eventType, $eventText) {
		$report = file_get_contents(Session::getBasePath() . "/reports/email/systemEvent_en.inc");
		$report = str_replace("{{event.type}}", $eventType, $report);
		$report = str_replace("{{event.text}}", $eventText, $report);
		return $report;
	}
	
	private function createReport(Device $device) {
		$historyService = new HistoryService();
		
		$adapter = PDODataAdapter::getInstance();
		$maxPowerToday = $adapter->readMaxPowerToday($device->id);
		
		$arHistory = $historyService->getArrayByDeviceAndTime($device, null);
		
		$first = reset($arHistory);
		$last = end($arHistory);
		
		$productionStart = $first->KWHT;
		$productionEnd = $last->KWHT;
		
		// Check if we passed 100.000kWh
		if ($productionEnd < $productionStart) {
			$productionEnd += 100000;
		}
		
		$production = round($productionEnd - $productionStart, 3);
		$maxwatt = round($maxPowerToday->GP, 0);
		
		$report = file_get_contents(Session::getBasePath() . "/reports/email/inverterShutdown_en.inc");
		$report = str_replace("{{inverter.name}}", $device->name, $report);
		$report = str_replace("{{totalkwh}}", $production, $report);
		$report = str_replace("{{maxwatt}}", $maxwatt, $report);
		$report = str_replace("{{maxtime}}", date('H:i', $maxPowerToday->time), $report);
		return $report;
	}
}
?>