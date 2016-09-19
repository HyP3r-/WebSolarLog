<?php
/**
 * LastDaysDataResult class file
 *
 * PHP Version 5.2
 *
 * @category  Classes
 * @package   Results
 * @author    Martin Diphoorn <martin@diphoorn.com>
 */

/**
 * LastDaysDataResult
 *
 * @category  Classes
 * @package   Results
 * @author    Martin Diphoorn <martin@diphoorn.com>
 * @access    public
 * @since     File available since Release 0.0.0
 */
class LastDaysDataResult extends BaseResult
{

    // data
    public $data; // datapoints

    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();

        $this->resulttype = "LastDaysDataResult";
    }

}

?>