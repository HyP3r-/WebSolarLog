<?php
class AuroraConverter
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
        $data = preg_split("/[[:space:]]+/",$inputLine);

        // Check if the record is okay
        if (!empty($data[22]) && trim($data[22]) != "OK") {
			throw new ConverterException("not a OK response from Aurora:\r\n".print_r($inputLine,true));
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
            $live->BOOT = $data[13];
        }
        if (!empty ($data[19])) {
            $live->KWHT = $data[19];
        }
        
        // This line is only valid if GP and KWHT are filled with data
        if (empty($live->KWHT) || empty($live->GP)) {
        	return null;
        }

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