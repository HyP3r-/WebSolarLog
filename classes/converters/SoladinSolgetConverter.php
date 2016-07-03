<?php
class SoladinSolgetConverter
{

    /**
     * Converts the result of getData to an Live object
     * @param string $inputLine
     * @return Live or null
     */
    public static function toLive($inputLine)
    {
    	// Input Data from Soladin Solget is as follows
    	/*
    	 * 
    	 * 55,9 0,24 49,97 231 10 24 74,5 813,87 382,51 16481:9 3,87 1,81 16481:9 ERROR description
    	 * 0=55,9	=  Spanning Solarpanelen in volt
    	 * 1=0,24	= Stroom Solarpanelen im Ampere
    	 * 
    	 * 2=49,97	=  Netfrequentie in Hz
    	 * 3=231	=  Netspanning in Volt
    	 * 4=10		= Opbrengst Solarpanelen in watt (realtime)
    	 * 5=24		= Temperatuur Omvormer in graden in Graden
    	 * 6=74,5	= Rendement Omvormer in %
    	 * 7=813,87	= Opgeleverde energie vandaag in kWh  (klopt niet helemaal, hij pakt het totaal van de omvormer)
    	 * 8=382,51	=  co2 uitstoot vermeden in Kg (totale looptijd)
    	 * 9=16481:9= Draaitijd vandaag (klopt niet helemaal)
    	 * 10=3,87	= Totaal opgeleverde energie van de inverter kWh  (totale looptijd omvormer) In het script zit een optie om de stand op "0" te zetten bij een refurbished of gebruikte soladin. Die heb ik ingevuld.
    	 * 11=1,81	= Co2 uitstoot vermeden in Kg (totale looptijd omvormer, zit gelinked aan de totaal opgeleverde energie van hierboven)
    	 * 12=16481:9= totale bedrijfstijd omvormer (totale looptijd omvormer)
    	 * 13=ERROR	= Eventuele error messages, is normaal leeg
    	 */    	 
        // Check if the input line is valid
        if ($inputLine == null || trim($inputLine) == "") {
            echo("Input from Converter is null");
            return null;
        }

        // Split on a serie of spaces (not one)
        $data = preg_split("/[[:space:]]/",$inputLine);

        for ($i = 0; $i < count($data); $i++) {
        	$data[$i] = str_replace(",",".",$data[$i]);
        }
        
        $live = new Live();
        $live->type = 'production';

        // getting timestamp from system time
        $timezone = date_default_timezone_get();
        //echo("The current server timezone is: " . $timezone."\n");
        $live->time =  Util::getTimestampOfDate(date('H', time()),date('i', time()),date('s', time()),date('d', time()),date('m', time()),date('Y', time()));
        //echo("The live Timestamp is: " . $live->time."\n");
        
        if (!empty ($data[0])) {
            $live->I1V = self::liveLineToValues($data[0],"float");
        }
        if (!empty ($data[1])) {
            $live->I1A = self::liveLineToValues($data[1],"float");
        }
        if (!empty ($live->I1V) && !empty($live->I1A)) {
            $live->I1P = self::liveLineToValues(($live->I1V * $live->I1A),"float");
        }else{
        	$live->I1P = 0;
        }
        //echo("The live Power is: " . $live->I1P."\n");
        
        // second string is 0
        $live->I2P = 0;
        $live->I2V = 0;
        $live->I2A = 0;
        if (!empty ($data[3])) {
            $live->GV = self::liveLineToValues($data[3],"float");
        }
        if (!empty ($data[4])) {
            $live->GA = self::liveLineToValues(round((($live->I1P /100) *$data[6])/$data[3],3),"float");
        }
        if ($live->I1P > 0 && !empty ($data[6])){
            $live->GP = self::liveLineToValues((($live->I1P /100) *$data[6]),"float");
        }
        if (!empty ($data[6])) {
            $live->FRQ = self::liveLineToValues($data[2],"float");
        }
        if (!empty ($data[7])) {
            $live->INVT = self::liveLineToValues($data[5],"float");
        }
        if (!empty ($data[8])) {
            $live->KWHT = self::liveLineToValues($data[10],"float");
        }
        if(!empty ($data[6])){
        	$live->EFF = self::liveLineToValues($data[6],"float");
        }
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
	   		case 'SoladinSolgetDateTime':
	   			//$splitLine= preg_split("/ /",$line);
	   	    	preg_match('/(\d{2})\/(\d{2})\/(\d{4}) (\d{2})\:(\d{2})\:(\d{2})/i', $line, $matches,0);
	   	    	$result =  Util::getTimestampOfDate($matches[4],$matches[5],$matches[6],$matches[1],$matches[2],$matches[3]);
	   			break;
    	}
    	return $result; 
    }
}
