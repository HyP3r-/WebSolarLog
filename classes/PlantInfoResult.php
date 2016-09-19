<?php
/**
 * PlantInfoResult class file
 *
 * PHP Version 5.2
 *
 * @category  Classes
 * @package   Results
 * @author    Martin Diphoorn <martin@diphoorn.com>
 */

/**
 * PlantInfoResult
 *
 * @category  Classes
 * @package   Results
 * @author    Martin Diphoorn <martin@diphoorn.com>
 * @access    public
 * @since     File available since Release 0.0.0
 */
class PlantInfoResult extends BaseResult
{

    // values
    public $valueSYSID;
    public $valuePLANT_POWER;
    public $valueLOCATION;
    public $valueCO2;
    public $valueCO2v;
    public $valueKWHP;
    public $valueUpdtd;
    public $valueEvents;
    public $valueInverter;

    //language
    public $langEVENTS;
    public $langINVERTERINFO;
    public $langTOTALPROD;
    public $langECOLOGICALINFOB;
    public $langPLANTINFO;
    public $langLOCATION;
    public $langCOUNTER;
    public $langPLANTPOWER;


    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->resulttype = "PlantInfoResult";
    }

}

?>