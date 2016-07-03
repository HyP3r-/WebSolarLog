<?php
class MasterVoltConverter
{

    /**
     * Converts the result of getData to an Live object
     * @param string $inputLine
     * @return Live or null
     */
    public static function toLive($inputLine)
    {
    	// Input Data from Mastervolt is as follows
    	// 451 1,38 622 233 2,55 580 50,00 42 13,33 748 NoError
    	// This represents the values as
    	//Input Voltage   Input Current  Input Power   Output Voltage     Output Current    Output Power    Output Frequency    Inverter Temperature    Watt Total today    Runtime Today   Error Code
    	//echo("Line from solget.sh is = ".$inputLine." \n");
    	 
        // Check if the input line is valid
        if ($inputLine == null || trim($inputLine) == "") {
            echo("Input from Converter is null");
            return null;
        }

        // Split on a serie of spaces (not one)
        $data = preg_split("/[[:space:]]/",$inputLine);

        // Check if the record is okay
//        if (!empty($data[22]) && trim($data[22]) != "OK") {
//            return null;
//        }

        $live = new Live();
        $live->type = 'production';

        // getting timestamp from system time
        $timezone = date_default_timezone_get();
        //echo("The current server timezone is: " . $timezone."\n");
        $live->time =  Util::getTimestampOfDate(date('H', time()),date('i', time()),date('s', time()),date('d', time()),date('m', time()),date('Y', time()));
        //echo("The live Timestamp is: " . $live->time."\n");
        
        if (!empty ($data[0])) {
            $live->I1V = $data[0];
        }
        if (!empty ($data[1])) {
            $live->I1A = $data[1];
        }
        if (!empty ($data[2])) {
            $live->I1P = $data[2];
        }
        //echo("The live Power is: " . $live->I1P."\n");
        
        // second string is 0
        $live->I2P = 0;
        $live->I2V = 0;
        $live->I2A = 0;
        if (!empty ($data[3])) {
            $live->GV = $data[3];
        }
        if (!empty ($data[4])) {
            $live->GA = $data[4];
        }
        if (!empty ($data[5])) {
            $live->GP = $data[5];
        }
        if (!empty ($data[6])) {
            $live->FRQ = $data[6];
        }
        if (!empty ($data[7])) {
            $live->INVT = $data[7];
        }
		//echo("Inverter temperature: ".$live->INVT." \n");
        if (!empty ($data[8])) {
            $live->KWHT = $data[8];
        }

        $live->EFF = number_format(($live->GP/$live->I1P)*100, 2, '.', '');
        //echo("Efficency:".$live->EFF." \n" );
        
        //data not given by converter is set to 0
       	$live->BOOT = 0;
        
        

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
	   		case 'MastervoltDateTime':
	   			//$splitLine= preg_split("/ /",$line);
	   	    	preg_match('/(\d{2})\/(\d{2})\/(\d{4}) (\d{2})\:(\d{2})\:(\d{2})/i', $line, $matches,0);
	   	    	$result =  Util::getTimestampOfDate($matches[4],$matches[5],$matches[6],$matches[1],$matches[2],$matches[3]);
	   			break;
    	}
    	return $result; 
    }
    }
