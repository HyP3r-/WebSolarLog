<?php
class GraphDataService {
	// Deprecated //
	public static $tblInverter = "inverter";
	// Deprecated //
	public static $tbl = "history";

	/**
	 * Load an object from the database
	 * @param int $id
	 * @return History
	 */

	public static function GraphDayPoints($args) {
		$device = $args[1];
		$date = $args[2];
		$type = $args[3];
		$graphPoints = array();
		if($args[1]->type=="production"){
			if($args[3]=='Today'){
				$data = self::readTablesPeriodValues($device, self::$tbl, $type, $date);
				$graphPoints = self::DayBeansToGraphPoints($device,$data['beans'], time());
			}
		}
		return $graphPoints;
	}

	/**
	 *
	 * @param unknown_type $beans
	 */
	public static function  DayBeansToGraphPoints($device,$beans,$startDate){
		
		$graph = new Graph();
		$i=0;
		$firstBean = array();
		$preBean = array();

		$KWHT = 0;
		$kWhkWp = 0;
		$lastDays = new LastDays();

		foreach ($beans as $bean){
			if ($i==0){
				$firstBean = $bean;
				$preBean = $bean;
				$preBeanUTCdate = $bean['time'];
			}
			$UTCdate = $bean['time'];
			$UTCtimeDiff = $UTCdate - $preBeanUTCdate;
			$cumPower = round(($bean['KWHT']-$firstBean['KWHT'])*1000,0);
			// 09/30/2010 00:00:00
			$avgPower = Formulas::calcAveragePower($bean['KWHT'], $preBean['KWHT'], $UTCtimeDiff,0,0);
			$graph->points['cumPowerWh-'.$device->id][] = array (  $UTCdate ,$cumPower);
			$graph->points['avgPowerW-'.$device->id][] = array (  $UTCdate ,$avgPower);
			$preBeanUTCdate = $bean['time'];
			$preBean = $bean;
			$i++;
		}


		if($i>0){
				
			if($cumPower>0 AND $device->plantpower > 0){
				$kWhkWp = number_format(($cumPower/1000) / ($device->plantpower/1000),2,',','');
			}else{
				$kWhkWp = number_format(0,2,',','');
			}

			if($cumPower >= 1000){
				$cumPower = number_format($cumPower /=1000,2,',','');
				$cumPowerUnit = "kWh";
			}else{
				$cumPowerUnit = "W";
			}

			$graph->metaData['KWH']=array('cumPower'=>$cumPower,'KWHTUnit'=>$cumPowerUnit,'KWHKWP'=>$kWhkWp);
		}
		
		return $graph;
	}

	/**
	 *
	 * @param unknown $invtnum
	 * @param unknown $table
	 * @param unknown $type
	 * @param unknown $startDate
	 * @return unknown
	 */

	public static function readTablesPeriodValues($device, $table, $type, $startDate){
		$count = 0;
		// get the begin and end date/time
		$beginEndDate = Util::getBeginEndDate($type, $count,$startDate);

		$energyBeans = R::getAll("
				SELECT *
				FROM history
				WHERE time > :beginDate AND  time < :endDate AND inv = :inv ORDER BY id",
				array(':beginDate'=>$beginEndDate['beginDate'],':endDate'=>$beginEndDate['endDate'], ':inv'=>$device->id));

		//see if we have atleast 1 bean, else we make one :)
		(!$energyBeans) ? $energyBeans[0] = array('time'=>time(),'KWH'=>0,'KWHT'=>0) : $energyBeans = $energyBeans;

		return array('device'=>$device,'beans'=>$energyBeans);
	}


	private function toBean($object, $bObject) {
		$bObject->INV = $object->INV;
		$bObject->deviceId = $object->deviceId;
		$bObject->SDTE = $object->SDTE;
		$bObject->time = $object->time;
		$bObject->dayNum = $object->dayNum;

		$bObject->I1V = round($object->I1V,3);
		$bObject->I1A = round($object->I1A,3);
		$bObject->I1P = round($object->I1P,3);
		$bObject->I1Ratio = round($object->I1Ratio,3);

		$bObject->I2V = round($object->I2V,3);
		$bObject->I2A = round($object->I2A,3);
		$bObject->I2P = round($object->I2P,3);
		$bObject->I2Ratio = round($object->I2Ratio,3);

		$bObject->I3V = round($object->I3V,3);
		$bObject->I3A = round($object->I3A,3);
		$bObject->I3P = round($object->I3P,3);
		$bObject->I3Ratio = round($object->I3Ratio,3);

		$bObject->GV = round($object->GV,3);
		$bObject->GA = round($object->GA,3);
		$bObject->GP = round($object->GP,3);

		$bObject->GV2 = round($object->GV2,3);
		$bObject->GA2 = round($object->GA2,3);
		$bObject->GP2 = round($object->GP2,3);

		$bObject->GV3 = round($object->GV3,3);
		$bObject->GA3 = round($object->GA3,3);
		$bObject->GP3 = round($object->GP3,3);

		$bObject->IP = round($object->IP);
		$bObject->ACP = round($object->ACP,3);

		$bObject->FRQ = round($object->FRQ,3);
		$bObject->EFF = round($object->EFF,3);
		$bObject->INVT = round($object->INVT,3);
		$bObject->BOOT = round($object->BOOT,3);
		$bObject->KWHT = round($object->KWHT,3);
		$bObject->pvoutput = $object->pvoutput;
		$bObject->pvoutputErrorMessage = $object->pvoutputErrorMessage;
		return $bObject;
	}

	private function toObject($bObject) {
		$object = new History();
		$object->id = $bObject->id;
		$object->INV = $bObject->INV;
		$object->deviceId = $bObject->deviceId;
		$object->SDTE = $bObject->SDTE;
		$object->time = $bObject->time;
		$object->dayNum = $bObject->dayNum;

		$object->I1V = $bObject->I1V;
		$object->I1A = $bObject->I1A;
		$object->I1P = $bObject->I1P;
		$object->I1Ratio = $bObject->I1Ratio;

		$object->I2V = $bObject->I2V;
		$object->I2A = $bObject->I2A;
		$object->I2P = $bObject->I2P;
		$object->I2Ratio = $bObject->I2Ratio;

		$object->I3V = $bObject->I3V;
		$object->I3A = $bObject->I3A;
		$object->I3P = $bObject->I3P;
		$object->I3Ratio = $bObject->I3Ratio;

		$object->GV = $bObject->GV;
		$object->GA = $bObject->GA;
		$object->GP = $bObject->GP;

		$object->GV2 = $bObject->GV2;
		$object->GA2 = $bObject->GA2;
		$object->GP2 = $bObject->GP2;

		$object->GV3 = $bObject->GV3;
		$object->GA3 = $bObject->GA3;
		$object->GP3 = $bObject->GP3;

		$object->IP = $bObject->IP;
		$object->ACP = $bObject->ACP;

		$object->FRQ = $bObject->FRQ;
		$object->EFF = $bObject->EFF;
		$object->INVT = $bObject->INVT;
		$object->BOOT = $bObject->BOOT;
		$object->KWHT = $bObject->KWHT;
		$object->pvoutput = $bObject->pvoutput;
		$object->pvoutputErrorMessage = $bObject->pvoutputErrorMessage;
		return $object;
	}
}
?>