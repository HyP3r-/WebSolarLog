<?php
class OmnikConverter {

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


        // Comma seperated
        $data = explode(",", $inputLine);

        // Id,Temp,VPV1,VPV2,VPV3,IPV1,IPV2,IPV3,IAC1,IAC2,IAC3,VAC1,VAC2,VAC3,FAC1,PAC1,FAC2,PAC2,FAC3,PAC3,ETODAY,ETOTAL,HTOTAL
        // NLDN3020137X5092,35.9,170.9,161.7,-1,0.6,0.6,-1,0.8,-1,-1,238.1,-1,-1,50.03,206,-1,-1,-1,-1,15.25,2065.0,2964

        $live = new Live();
        $live->type = 'production';
        $live->time = time();

        // Temperature (BOOT)
        if (!empty($data[1]) && $data[1] != "-1") {
            $live->BOOT = $data[1];
        }

        // VPV1, VPV2, VPV3 => Voltages
        if (!empty($data[2]) && $data[2] != "-1") {
            $live->I1V = $data[2];
        }
        if (!empty($data[3]) && $data[3] != "-1") {
            $live->I2V = $data[3];
        }
        if (!empty($data[4]) && $data[4] != "-1") {
            $live->I3V = $data[4];
        }

        // IPV1, IPV2, IPV3 => AMPERE
        if (!empty($data[5]) && $data[5] != "-1") {
            $live->I1A = $data[5];
        }
        if (!empty($data[6]) && $data[6] != "-1") {
            $live->I2A = $data[6];
        }
        if (!empty($data[7]) && $data[7] != "-1") {
            $live->I3A = $data[7];
        }

        // Calucate P
        if (!empty($live->I1V) && !empty($live->I1A)) {
            $live->I1P = $live->I1V * $live->I1A;
        }
        if (!empty($live->I2V) && !empty($live->I2A)) {
            $live->I2P = $live->I2V * $live->I2A;
        }
        if (!empty($live->I3V) && !empty($live->I3A)) {
            $live->I3P = $live->I3V * $live->I3A;
        }

        // GRID Power, IAC1,IAC2,IAC3
        if (!empty($data[8]) && $data[8] != "-1") {
            $live->GA = $data[8];
        }
        if (!empty($data[9]) && $data[9] != "-1") {
            $live->GA2 = $data[9];
        }
        if (!empty($data[10]) && $data[10] != "-1") {
            $live->GA3 = $data[10];
        }

        // GRID Voltages, VAC1,VAC2,VAC3
        if (!empty($data[11]) && $data[11] != "-1") {
            $live->GV = $data[11];
        }
        if (!empty($data[12]) && $data[12] != "-1") {
            $live->GV2 = $data[12];
        }
        if (!empty($data[13]) && $data[13] != "-1") {
            $live->GV3 = $data[13];
        }

        // GRID Ampere, PAC1, PAC2, PAC3
        if (!empty($data[15]) && $data[15] != "-1") {
            $live->GP = $data[15];
        }
        if (!empty($data[17]) && $data[17] != "-1") {
            $live->GP2 = $data[17];
        }
        if (!empty($data[19]) && $data[19] != "-1") {
            $live->GP3 = $data[19];
        }

        // GRID Freq, FAC1 (FAC2 ,FAC3, are not supported);
        if (!empty($data[14]) && $data[14] != "-1") {
            $live->FRQ = $data[14];
        }

        // KWHT from inverter
        if (!empty($data[21])) {
            $live->KWHT = $data[21];
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