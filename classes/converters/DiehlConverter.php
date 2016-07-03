<?php
class DiehlConverter
{
	const _Status	= 'eNEXUS_0001[s:1,t:17]';//Status
	const _Mode		= 'eNEXUS_0002[s:1,t:17]';//Mode

	const _I1A		= 'eNEXUS_0005[s:1,t:17]';//MPP1 Amps
	const _I1V		= 'eNEXUS_0006[s:1,t:17]';//MPP1 Volt
	const _I1P		= 'eNEXUS_0007[s:1,t:17]';//MPP1 Power
	
	/*
	const _I2A		= 'eNEXUS_0066[s:17,t:1,p:2]';//MPP2 Amps
	const _I2V		= 'eNEXUS_0009[s:17,t:1,p:2]';//MPP2 Volt
	const _I2P		= 'eNEXUS_0064[s:17,t:1,p:2]';//MPP2 Power
		
	const _I3A		= 'eNEXUS_0066[s:17,t:1,p:3]';//MPP3 Amps
	const _I3V		= 'eNEXUS_0009[s:17,t:1,p:3]';//MPP3 Volt
	const _I3P		= 'eNEXUS_0064[s:17,t:1,p:3]';//MPP3 Power
	*/
	
	const _GA 		= 'eNEXUS_0008[s:1,t:17]'; //Grid Amps
	const _GV		= 'eNEXUS_0009[s:1,t:17]'; //Grid Power
	const _GP		= 'eNEXUS_0010[s:1,t:17]'; //Grid Volt
	
	
	const _FRQ		= 'eNEXUS_0046[s:1,t:17]'; // Frequency
	const _INVT		= 'eNEXUS_0045[s:1,t:17]'; // Inverter Temperature
	const _KWHT		= 'eNEXUS_0043[s:1,t:17,n:4]'; // kWhTotal
	
	/**
	 * Converter the JSON mode response to a WSL status 
	 * 
	 * @param unknown $inputLine
	 * @return NULL|unknown
	 */
	public static function toStatus($inputLine){
		// Check if the input line is valid
		if ($inputLine == null || trim($inputLine) == "") {
			//throw new ConverterException("Empty STATUS response from Diehl:\r\n".print_r($inputLine,true));
			return null;
		}
		
		// loop through reponse
		foreach (json_decode($inputLine) as $key => $value){
			// if value == array
			if(is_array($value)){
				// loop through value
				foreach ($value as $keys => $values){
					// if value == _mode 
					if($values->path == self::_Mode AND $values->value != ''){
						// set mode  
						if($values->value == 0){
							$mode = 1; // offline
							return $mode;
						}elseif($values->value == 1){
							$mode = 9; // online
							return $mode;
						}
					}
				}
			}
		}
		
	}
	
	/*
	 * At this moment we don't use the Status of the Dhiel (normal,error,warning,etc)
	 * 
	public static function toStatus($inputLine){
		// Check if the input line is valid
		if ($inputLine == null || trim($inputLine) == "") {
			return null;
		}
	
		foreach (json_decode($inputLine) as $key => $value){
			if(is_array($value)){
				foreach ($value as $keys => $values){
					if($values->path == self::_Status AND $values->value != ''){ $mode['status'] = $values->value;}
				}
			}
		}
		return $mode;
	}
	*/
	
	public static function toMode($inputLine) {
		// TODO :: implement this method
		return 0; // Try to detecht
	}
	
    /**
     * Converts the result of getData to an Live object
     * @param string $inputLine
     * @return Live or null
     */
    public static function toLive($inputLine)
    {
        // Check if the input line is valid
        if ($inputLine == null || trim($inputLine) == "") {
        	throw new ConverterException("Empty LIVE response from Diehl:\r\n".print_r($inputLine,true));
            return null;
        }
        $live = new Live();
        $live->type = 'production';
        foreach (json_decode($inputLine) as $key => $value){
        	if(is_array($value)){
        		foreach ($value as $keys => $values){
			        $live->time = strtotime(date("d-m-Y H:i:s"));
				    if($values->path == self::_I1A AND $values->value>0){ $live->I1A = ($values->value/1000);}
			        if($values->path == self::_I1V AND $values->value>0){ $live->I1V = ($values->value/10);}
			        if($values->path == self::_I1P AND $values->value>0){ $live->I1P = ($values->value);}
					/*
					 * 
			        if($values->path == self::_I2A AND $values->value>0){ $live->I2A = ($values->value/1000);}
			        if($values->path == self::_I2V AND $values->value>0){ $live->I2V = ($values->value/10);}
			        if($values->path == self::_I2P AND $values->value>0){ $live->I2P = ($values->value);}

			        if($values->path == self::_I3A AND $values->value>0){ $live->I3A = ($values->value/1000);}
			        if($values->path == self::_I3V AND $values->value>0){ $live->I3V = ($values->value/10);}
			        if($values->path == self::_I3P AND $values->value>0){ $live->I3P = ($values->value);}
					*
					*/
			        if($values->path == self::_GA AND $values->value>0){ $live->GA = ($values->value/1000);}
			        if($values->path == self::_GP AND $values->value>0){ $live->GP = ($values->value);}
        			if($values->path == self::_GV AND $values->value>0){ $live->GV =($values->value/10);}
        			if($values->path == self::_FRQ AND $values->value>0){ $live->FRQ =($values->value/100);}
        			if($values->path == self::_INVT AND $values->value>0){ $live->INVT =($values->value/100);}
        			
        			if($values->path == self::_KWHT AND $values->value>0){ $live->KWHT =($values->value/1000);}
        			
        			// if GP(GridPower) AND I1P (MPP 1 Power) is bigger than 0
        			if($live->GP > 0 AND $live->I1P > 0){
        				// We can calculate the Efficienty DC>AC in %
	        			$live->EFF = ($live->GP/$live->I1P)*100;
        			}
        		}
        	}
        }
        
		//$live->FRQ = $data[10]; $live->INVT = $data[12]; $live->BOOT = $data[13];
		        
        // This line is only valid if GP and KWHT are filled with data
        if (empty($live->KWHT) || empty($live->GP)) {
        	//HookHandler::getInstance()->fire("onDebug", "DIEHL didn't return KWHT or GP is empty! We need these values for calculations");
        	return null;
        }

        return $live;
    }
}