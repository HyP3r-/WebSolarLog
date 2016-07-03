<?php
/**
 * BaseResult class file
 *
 * PHP Version 5.2
 *
 * @category  Classes
 * @package   Results
 * @author    Martin Diphoorn <martin@diphoorn.com>
 * @license   http://www.diphoorn.com/licenses/license.php Commercial License
 * @link      http://www.diphoorn.com/
 */

/**
 * BaseResult
 *
 * @category  Classes
 * @package   Results
 * @author    Martin Diphoorn <martin@diphoorn.com>
 * @license   http://www.diphoorn.com/licenses/license.php Commercial License
 * @link      http://www.diphoorn.com/
 * @access    public
 * @since     File available since Release 0.0.0
 */
class Formulas
{

    /**
     * Calculate the Power Efficency
     * @param $gridPower
     * @param $COEF
     */
    public static function calcPowerEfficency($gridPower, $COEF, $decimals = 2) {
        $COEF = ($COEF > 1) ? 1 : $COEF; // Only allow efficency of max 100%
        return round($gridPower * $COEF, $decimals);
    }

    /**
     * Calculates the KiloWattHours for this day
     * @param $kiloWattHourStart
     * @param $kiloWattHourStop
     * @param $COEF
     */
    public static function calcKiloWattHourDay($kiloWattHourStart, $kiloWattHourStop, $COEF, $decimals = 0) {
        return round( (($kiloWattHourStop - $kiloWattHourStart) * 1000 * $COEF) / 1000, $decimals);
    }
    
    /**
     * Calculates the average power over a given time
     * @param $kiloWattHourStart
     * @param $kiloWattHourStop
     * @param $timeDifference
     * @param $decimals
     */
    public static function calcAveragePower($kiloWattHourStart, $kiloWattHourStop, $timeDifference, $decimals = 1) {
    	if ($timeDifference == 0) return 0; // Prevent division by zero
    	return round((((($kiloWattHourStart-$kiloWattHourStop) * 3600) / $timeDifference) * 1000), $decimals);
    }
    

    /**
     * Calculates the amount of co2 for the specified kwh
     * @param $kiloWattHour
     * @param $configCO2KWH
     * @param $decimals
     * @return string
     */
    public static function CO2kWh($kiloWattHour, $configCO2KWH, $decimals = 1) {
    	return self::calculateCO2($kiloWattHour, $configCO2KWH, $decimals);
    }

    /**
     * Calculates the amount of co2 for the specified liter gas
     * @param $litreGas
     * @param $configCO2gas
     * @param $decimals
     * @return string
     */
    public static function CO2gas($litreGas, $configCO2gas, $decimals = 1) {
    	return self::calculateCO2($litreGas, $configCO2gas, $decimals);
    }

    /**
     * Calculate the co2 based on amount and ratio
     * @param $amount
     * @param $ratio
     * @param $decimals
     * @return string
     */
    private static function calculateCO2($amount, $ratio, $decimals = 1) {
    	$CO2=(($amount/1000)*$ratio);
    	if ($CO2>1000) {
    		$CO2 = number_format(($CO2/1000), 3, ",", "")." Tonnes";
    	}else {
    		$CO2 = number_format(($CO2),1, ",", "")." Kg";
    	}
    	
    	return $CO2;
    }
    
}

?>