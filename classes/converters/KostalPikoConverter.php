<?php
class KostalPikoConverter
{

    /**
     * Converts the result of getData to an Live object
     * @param string $inputLine
     * @return Live or null
     */
    public static function toLive($inputLine)
    {
        // Check if the input line is valid
        if ($inputLine == null || trim($inputLine) == "") {
            return null;
        }

        // Split on a serie of spaces (not one)
        $dataLines = preg_split("/[\s]+/",$inputLine);

        foreach ($dataLines as $line){
        	$data[] = preg_split("/[,]+/",$line);
        }
        HookHandler::getInstance()->fire("onDebug", "KostalPikoConverterData:".print_r($data,true));
        
        // Check if the record is okay
        if (!empty($data[22]) && trim($data[22]) != "OK") {
        	throw new ConverterException("not a OK response from KostalPiko:\r\n".print_r($inputLine,true));
        }
        
        $live = new Live();
        $live->type = 'production';
		
        foreach ($data as $line){
        	if (strtolower($line[0])=="tim") {
        		$lineArr = explode('.',$line[1]);
        		preg_match('/(\d{4})\-(\d{2})\-(\d{2})T(\d{2})\:(\d{2})\:(\d{2})/i', $lineArr[0], $matches,0);
        		$result =  Util::getTimestampOfDate($matches[4],$matches[5],$matches[6],$matches[3],$matches[2],$matches[1]);
        		$live->time = $result;
        	}
        	if (strtolower($line[0])=="dc1") {
        		$live->I1V = $line[1];
        		$live->I1A = $line[2];
        		$live->I1P = $line[3];
        	}
        	if (strtolower($line[0])=="dc2") {
        		$live->I2V = $line[1];
        		$live->I2A = $line[2];
        		$live->I2P = $line[3];
        	}
        	/**
        	 *** Third string fix
        	 */
        	if (strtolower($line[0])=="dc3") {
        		$live->I3V = $line[1];
        		$live->I3A = $line[2];
        		$live->I3P = $line[3];
        	}

        	if (strtolower($line[0])=="ac1") {
        		$live->GV = $line[1];
        		$live->GA = $line[2];
        		$live->GP = $line[3];
        	}
        	if (strtolower($line[0])=="ac2") {
        		$live->GV2 = $line[1];
        		$live->GA2 = $line[2];
        		$live->GP2 = $line[3];
        	}
        	/**
        	 *** 3phase fix
        	 */
        	if (strtolower($line[0])=="ac3") {
        		$live->GV3 = $line[1];
        		$live->GA3 = $line[2];
        		$live->GP3 = $line[3];
        	}

        	if (strtolower($line[0])=="ene") {
        		$live->KWHT = $line[1]/1000;
        	}
        }
        
        $live->BOOT = 0;
        $live->INVT = 0;
        // not available
        $live->FRQ = 0;
        
        $live->IP = $live->I1P + $live->I2P + $live->I3P;
  		$live->ACP = $live->GP + $live->GP2 + $live->GP3;

        if($live->IP > 0 AND $live->ACP > 0){
        	$live->EFF = (float)round(($live->ACP/$live->IP)*100,4);
        }

        
        // This line is only valid if GP and KWHT are filled with data
        if (empty($live->KWHT) || empty($live->ACP)) {
        	HookHandler::getInstance()->fire("onDebug", "Piko.py didn't return KWHT or GP is empty! We need these values for calculations");
        	return null;
        }
        HookHandler::getInstance()->fire("onDebug", "Live data by PikoKostalConverter\r\n".print_r($live,true));        
        return $live;
    }
    
    public static function toDeviceHistory($line) {
		// Split the line based on multiple spaces between two values
    	$parts = preg_split('/\s+/', $line);
    	if (count($parts) < 2) {
    		return null;
    	}

    	// Create the object
    	$deviceHistory = new DeviceHistory();
    	$deviceHistory->amount = (double) $parts[1];
    	$deviceHistory->time = ($parts[0] != "") ? strtotime($parts[0]) : 0;
    	
    	// Only return if we have an valid time
    	if ($deviceHistory->time > 0) {
    		return $deviceHistory;
    	}
    	return null;
    }
}