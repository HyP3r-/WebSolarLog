<?php
class EffektaConverter
{

    /**
     * Converts the result of getData to an Live object
     * @param string $inputLine
     * @return Live or null
     */
    public static function toLive($inputLine)
    {
    	$inputLine = "20130221-16:29 264 0.6 150 260 0.6 150 226 1.6 280 49.9 93.3 42 149.123 OK";
    	//0  Date-Time
    	//1  STR1-V
    	//2  STR1-C
    	//3  STR1-P
    	//4  STR2-V
    	//5  STR2-C
    	//6  STR2-P
    	//7  Grid-V
    	//8  Grid-C 
    	//9  Grid-P 
    	//10 Grid-Hz 
    	//11 DcAcCvrEff 
    	//12 InvTemp 
    	//13 TotalEnergy 
    	//14 OK
        // Check if the input line is valid
        if ($inputLine == null || trim($inputLine) == "") {
            return null;
        }

        // Split on a serie of spaces (not one)
        $data = preg_split("/[[:space:]]+/",$inputLine);

        // Check if the record is okay
        if (!empty($data[14]) && trim($data[14]) != "OK") {
        	throw new ConverterException("not a OK response from Effekta:\r\n".print_r($inputLine,true));
        }

        $live = new Live();
        $live->type = 'production';
        if (!empty ($data[0])) {
            $live->SDTE = $data[0];
            $live->time = strtotime(substr($data[0], 0, 4)."-".substr($data[0], 4, 2)."-".substr($data[0], 6, 2)." ".substr($data[0], 9, 2).":".substr($data[0], 12, 2).":".substr($data[0], 15, 2));
        }
        if (!empty ($data[1])) {
            $live->I1V = $data[1];
        }
        if (!empty ($data[2])) {
            $live->I1A = $data[2];
        }
        if (!empty ($data[3])) {
            $live->I1P = $data[3];
        }
        if (!empty ($data[4])) {
            $live->I2V = $data[4];
        }
        if (!empty ($data[5])) {
            $live->I2A = $data[5];
        }
        if (!empty ($data[6])) {
            $live->I2P = $data[6];
        }
        if (!empty ($data[7])) {
            $live->GV = $data[7];
        }
        if (!empty ($data[8])) {
            $live->GA = $data[8];
        }
        if (!empty ($data[9])) {
            $live->GP = $data[9];
        }
        if (!empty ($data[10])) {
            $live->FRQ = $data[10];
        }
        if (!empty ($data[11])) {
            $live->EFF = $data[11];
        }
        if (!empty ($data[12])) {
            $live->INVT = $data[12];
        }
        if (!empty ($data[13])) {
            $live->KWHT = $data[13];
        }
        
        // This line is only valid if GP and KWHT are filled with data
        if (empty($live->KWHT) || empty($live->GP)) {
        	//HookHandler::getInstance()->fire("onDebug", "Effekta didn't return KWHT or GP is empty! We need these values for calculations");
        	return null;
        }

        return $live;
    }
}