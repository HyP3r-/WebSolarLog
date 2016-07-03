<?php
class SMABlueToothConverter
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
        $dataSplit = preg_split("/\n/",$inputLine);

		foreach ($dataSplit as $value) {
			// remove the connection tries from the array
			
			if(!preg_match("/^Connecting /",$value)){
		    	$data[] = $value;
			}
		}
        // Check if the record is okay
		if(!preg_match("/^Done/",trim($data[count($data)-1]))){
			throw new ConverterException("not a OK response from SMABlueTooth204:\r\n".print_r($inputLine,true));
        }
        
        $live = new Live();
        $live->type = 'production';
        if (!empty ($data[13])) {
        	$values= self::liveLineToValues($data[13],'SMASpotDateTime');
        	$live->time = $values[0];
        }

        if (!empty ($data[21])) {
        	$values= self::liveLineToValues($data[21],'string');
        	$live->status = $values[0];
        }
        
        if (!empty ($data[23])) {
        	$values= self::liveLineToValues($data[23]);
        	$live->KWHDay = (float)$values[0];
        }
        
        if (!empty ($data[24])) {
        	$values= self::liveLineToValues($data[24]);
        	$live->KWHT = (float)$values[0];
        }
        
        if (!empty ($data[28])) {
        	$values= self::liveLineToValues($data[28]);
            $live->I1P = (float)$values[0]*1000;
            $live->I1V = (float)$values[1];
            $live->I1A = (float)$values[2];
        }
        
        if (!empty ($data[29])) {
        	$values= self::liveLineToValues($data[29]);
        	$live->I2P = (float)$values[0]*1000;
        	$live->I2V = (float)$values[1];
        	$live->I2A = (float)$values[2];
        }
        
        if (!empty ($data[31])) {
        	$values= self::liveLineToValues($data[31]);
        	$live->GP = (float)$values[0]*1000;
        	$live->GV = (float)$values[1];
        	$live->GA = (float)$values[2];
        }
        
        ($live->I1P && $live->I2P) ? $live->IP = $live->I1P+$live->I2P : $live->IP = $live->I1P;

		if($live->GP > 0 AND $live->IP > 0){
        	// We can calculate the Efficienty DC>AC in %
			$live->EFF = (float)round(($live->GP/$live->IP)*100,4);
        }

        if (!empty ($data[35])) {
        	$values= self::liveLineToValues($data[35]);
        	$live->FRQ = (float)$values[0];
        }

        // This line is only valid if GP and KWHT are filled with data
        if (empty($live->KWHT) || empty($live->GP)) {
        	return null;
        }
        return $live;
    }
    
    
    public static function liveLineToValues($line,$type='float'){
    	$result = array();
    	switch ($type){
    		case 'float':
    			$splitLine= preg_split("/:/",$line);
    			if(count($splitLine)>1){
    				unset($splitLine[0]);
    			}
    	    	foreach ($splitLine as $string){
    				preg_match_all('/[0-9.]/i',$string,$array);
    				$result[]=implode($array[0]);
    			}
    			break;
    		case 'string':
    			$splitLine = preg_split("/:/",$line);
    			$result[] = trim($splitLine[1]);
    			break;
	   		case 'SMASpotDateTime':
	   			$splitLine= preg_split("/: /",$line);
	   	    	preg_match('/(\d{2})\/(\d{2})\/(\d{4}) (\d{2})\:(\d{2})\:(\d{2})/i', $splitLine[1], $matches,0);
	   	    	$result[] =  Util::getTimestampOfDate($matches[4],$matches[5],$matches[6],$matches[1],$matches[2],$matches[3]);
	   			break;
    	}
    	return $result; 
    }
}