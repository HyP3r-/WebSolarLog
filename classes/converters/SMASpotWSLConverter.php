<?php
class SMASpotWSLConverter
{
    /**
     * Converts the result of getData to an Live object
     * @param string $inputLine
     * @return Live or null
     */
	
	
	/*
	1 WSL_START;
	2 Datetime;
	3 Pdc1;
	4 Pdc2;
	5 Idc1;
	6 Idc2;
	7 Udc1;
	8 Udc2;
	9 Pac1;
	10 Pac2;
	11 Pac3;
	12 Iac1;
	13 Iac2;
	14 Iac3;
	15 Uac1;
	16 Uac2;
	17 Uac3;
	18 calPdcTot;
	19 TotalPac;
	20 calEfficiency;
	21 EToday;
	22 ETotal;
	23 GridFreq;
	24 OperationTime;
	25 FeedInTime;
	26 BT_Signal;
	27 DeviceStatus;
	28 GridRelayStatus;
	29 WSL_END;
	 */
    public static function toLive($inputLine)
    {    	
        // Check if the input line is valid
        if ($inputLine == null || trim($inputLine) == "") {
        	//HookHandler::getInstance()->fire("onDebug", "Input of SMASPotWSLConverter is empty");
            return null;
        }

        // Split on a serie of spaces (not one)
        $data = preg_split("/;/",$inputLine);
        
        // Check if the record is okay
    	if(($data[0]!="WSL_START") || ($data[count($data)-1]!="WSL_END")){
    		HookHandler::getInstance()->fire("onDebug", "No valid SMAspot START or END found:".print_r($data,true));
			throw new ConverterException("not a OK response from  SMASpotWSL:\r\n".print_r($inputLine,true));
        }
        
        
        $live = new Live();
        $live->type = 'production';
        if (!empty ($data[1])) {
        	$values= self::liveLineToValues($data[1],'SMASpotDateTime');
        	$live->time = $values[0];
        }

        if (!empty ($data[26])) {
        	$live->status = $data[26];
        }
        
        if (!empty ($data[20])) {
        	$live->KWHDay = self::liveLineToValues($data[20]);
        }
        
        if (!empty ($data[21])) {
        	$live->KWHT = self::liveLineToValues($data[21]);
        }

        
        if (!empty ($data[4])) {
        	$live->I1A = self::liveLineToValues($data[4]);
        }
        if (!empty ($data[6])) {
        	$live->I1V = self::liveLineToValues($data[6]);
        }
        
        if ($data[2]>0) {
        	$live->I1P = self::liveLineToValues($data[2]);
        }else{
        	if($live->I1A > 0 && $live->I1V > 0){
        		$live->I1P = ($live->I1A * $live->I1V);
        	}
        }
        

        if (!empty ($data[5])) {
        	$live->I2A = self::liveLineToValues($data[5]);
        }
        if (!empty ($data[7])) {
        	$live->I2V = self::liveLineToValues($data[7]);
        }
        
        if ($data[3] > 0) {
        	$live->I2P = self::liveLineToValues($data[3]);
        }else{
        	if($live->I2A > 0 && $live->I2V > 0){
        		$live->I2P = ($live->I2A * $live->I2V);
        	}
        }
        

        if (!empty ($data[11])) {
        	$live->GA = self::liveLineToValues($data[11]);
        }
        if (!empty ($data[14])) {
        	$live->GV = self::liveLineToValues($data[14]);
        }
        
        if ($data[8] > 0) {
        	$live->GP = self::liveLineToValues($data[8]);
        }else{
        	if($live->GA > 0 && $live->GV >0){
        		$live->GP = ($live->GA * $live->GV);
        	}
        }        

        ($live->I1P && $live->I2P) ? $live->IP = $live->I1P+$live->I2P : $live->IP = $live->I1P;

        if (!empty ($data[19])) {
        	$live->EFF = self::liveLineToValues($data[19]);
        }

        if (!empty ($data[22])) {
        	$live->FRQ = self::liveLineToValues($data[22]);
        }

        // This line is only valid if GP and KWHT are filled with data
        if (empty($live->KWHT) || empty($live->GP)) {
        	if(Util::isSunDown()==false){
        		HookHandler::getInstance()->fire("onDebug", "No valid Live :: missing KWHT of GP".print_r($live,true));
        	}
        	return null;
        }
        return $live;
    }
    
    
    public static function liveLineToValues($line,$type='float'){
    	$result = array();
    	switch ($type){
    		case 'float':
    			$float = str_replace(",",".",$line);
    			$result = (float)$float;
    			break;
    		case 'string':
    			$splitLine = preg_split("/:/",$line);
    			$result = trim($splitLine[1]);
    			break;
	   		case 'SMASpotDateTime':
	   			// default value;
	   			$result = time();
	   			
				// split on "D-M-Y h:i:s"
	   	    	preg_match('/(\d{2})\/(\d{2})\/(\d{4}) (\d{2})\:(\d{2})\:(\d{2})/i', $line, $matches,0);
	   	    	// check if the format matched
	   	    	if(count($matches)>0){
	   	    		// it looks like we have a match, so lets create a timestamp
	   	    		$result =  Util::getTimestampOfDate($matches[4],$matches[5],$matches[6],$matches[1],$matches[2],$matches[3]);
	   	    	}else{
	   	    		// it look liks we do NOT have a match... lets try the following;
	   	    		// split on "Y-M-D h:i:s"
	   	    		preg_match('/(\d{4})\-(\d{2})\-(\d{2}) (\d{2})\:(\d{2})\:(\d{2})/i', $line, $matches,0);
	   	    		$result =  Util::getTimestampOfDate($matches[4],$matches[5],$matches[6],$matches[1],$matches[2],$matches[3]);	   	    		
	   	    	}
	   	    	
	   			break;
    	}
    	return $result; 
    }
}