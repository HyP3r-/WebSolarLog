<?php
/**
 * BaseResult class file
 *
 * PHP Version 5.2
 *
 * @category  Classes
 * @package   Results
 * @author    Martin Diphoorn <martin@diphoorn.com>
 */

/**
 * BaseResult
 *
 * @category  Classes
 * @package   Results
 * @author    Martin Diphoorn <martin@diphoorn.com>
 * @access    public
 * @since     File available since Release 0.0.0
 */
class BaseResult
{
    public $success;
    public $resulttype;
    public $message;
    public $target;
    public $returnAction;

    /**
     * Constructor
     */
    function __construct()
    {
        $this->resulttype = "BaseResult";
        //$this->target = Common::getValue("target", "");
        //$this->returnAction = Common::getValue("returnAction", "");
    }
}

?>