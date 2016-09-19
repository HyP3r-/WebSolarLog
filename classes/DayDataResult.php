<?php
/**
 * DayDataResult class file
 *
 * PHP Version 5.2
 *
 * @category  Classes
 * @package   Results
 * @author    Martin Diphoorn <martin@diphoorn.com>
 */

/**
 * DayDataResult
 *
 * @category  Classes
 * @package   Results
 * @author    Martin Diphoorn <martin@diphoorn.com>
 * @access    public
 * @since     File available since Release 0.0.0
 */
class DayDataResult extends BaseResult
{

    // data
    public $data; // datapoints
    public $valueKWHT; // kiloWattHour total 

    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();

        $this->resulttype = "DayDataResult";
    }

}

?>