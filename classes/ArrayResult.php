<?php
/**
 * ArrayResult class file
 *
 * PHP Version 5.2
 *
 * @category  Classes
 * @package   Results
 * @author    Martin Diphoorn <martin@diphoorn.com>
 */

/**
 * ArrayResult
 *
 * @category  Classes
 * @package   Results
 * @author    Martin Diphoorn <martin@diphoorn.com>
 * @access    public
 * @since     File available since Release 0.0.0
 */
class ArrayResult extends BaseResult
{
    public $data;

    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();

        $this->resulttype = "ArrayResult";
        $this->data = array();
    }
}
?>