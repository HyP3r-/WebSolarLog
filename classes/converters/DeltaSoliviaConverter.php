<?php
class DeltaSoliviaConverter
{

    /**
     * Converts the result of getData to an Live object
     * @param string $inputLine
     * @return Live or null
     */
    public static function toLive($inputLine)
    {
        // Check if the input line is valid
        if ($inputLine == null || trim($inputLine) == "" || trim($inputLine) == "No response from inverter - shutdown?"  ) {
        	return null;
        }
        // Split on a serie of spaces (not one)
        $data = preg_split("/[[:space:]]+/",$inputLine);
        
        // data[0] = date-time format YYYYMMDD-HH:MM:SS 
        // data[1] = DC Volts 1 
        // data[2] = DC Current 1
        // data[3] = DC Power 1
        // data[4] = DC Volts 2
        // data[5] = DC Current 2
        // data[6] = DC Power 2
        // date[7] = AC Volts
        // data[8] = AC Current
        // data[9] = AC Power
        // data[10] = AC Frequency
        // data[11] = Inverter Efficiency
        // data[12] = DC Temp
        // data[13] = AC Temp
        // data[14] = Total AC kWh exported today
        // data[15] = "OK"

        // Check if the record is okay
        if (!empty($data[14]) && trim($data[15]) != "OK") {
			throw new ConverterException("not a OK response from DeltaSolivia:\r\n".print_r($inputLine,true));
        }

        $live = new Live();
        $live->type = 'production';
        // data[0] = date time YYYYMMDD-HH:MM:SS
        if (!empty ($data[0])) { 
            $live->SDTE = $data[0];
            $live->time = strtotime(substr($data[0], 0, 4)."-".substr($data[0], 4, 2)."-".substr($data[0], 6, 2)." ".substr($data[0], 9, 2).":".substr($data[0], 12, 2).":".substr($data[0], 15, 2));
             //print "live->SDTE=".$live->SDTE;
             //print "live->time=".$live->time;
        }
        // data[1] = DC Volts 1
        if (!empty ($data[1])) {
            $live->I1V = $data[1];
            //print "live->I1V=".$live->I1V;
        }
        // data[2] = DC Current 1
        if (!empty ($data[2])) {
            $live->I1A = $data[2];
            //print "live->I1A=".$live->I1A;
        }
        // data[3] = DC Power 1
        if (!empty ($data[3])) {
            $live->I1P = $data[3];
            //print "live->I1P=".$live->I1P;
        }
        // data[4] = DC Volts 2
        if (!empty ($data[4])) {
            $live->I2V = $data[4];
            //print "live->I2V=".$live->I2V;
        }
        // data[5] = DC Current 2
        if (!empty ($data[5])) {
            $live->I2A = $data[5];
            //print "live->I2A=".$live->I2A;
        }
        // data[6] = DC Power 2
        if (!empty ($data[6])) {
            $live->I2P = $data[6];
            //print "live->I2P=".$live->I2P;
        }
        // data[7] = AC Volts 
        if (!empty ($data[7])) {
            $live->GV = $data[7];
            //print "live->GV=".$live->GV;
        }
        // data[8] = AC Current
        if (!empty ($data[8])) {
            $live->GA = $data[8];
            //print "live->GA=".$live->GA;
        }
        // data[9] = AC Power
        if (!empty ($data[9])) {
            $live->GP = $data[9];
            //print "live->GP=".$live->GP;
        }
        // data[10] = AC Frequency 
        if (!empty ($data[10])) {
            $live->FRQ = $data[10];
            //print "live->FRQ=".$live->FRQ;
        }
        // data[11] = Inverter Efficiency 
        if (!empty ($data[11])) {
            $live->EFF = $data[11];
            //print "live->EFF=".$live->EFF;
        }
        // data[12] = DC Temp 
        if (!empty ($data[12])) {
            $live->INVT = $data[12];
            //print "live->INVT=".$live->INVT;
        }
        // data [13] = AC Temp 
        if (!empty ($data[13])) {
            $live->BOOT = $data[13];
            //print "live->BOOT=".$live->BOOT;
        }
        // data [14] =  Total AC kWh exported today 
        if (!empty ($data[14])) {
            $live->KWHT = $data[14];
            //print "live->KWHT=".$live->KWHT;
        }
        
        // This line is only valid if GP and KWHT are filled with data
        if (empty($live->KWHT) || empty($live->GP)) {
                return null;
        }

        return $live;
    }
    
    public static function toDeviceHistory($line) {
        // Delta Solivia inverter do not support this
        // in the future  may be able to import
        // Now kWh
        // Day kWh
        // Week kWh
        // Month kWh
        // Year kWh
        // Total kWh
    	return null;
    }
}
