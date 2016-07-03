<?php
class Panel
{
    public $id;
    public $inverterId;
    public $deviceId;
    public $description;
    public $roofOrientation;
    public $roofPitch;
    public $amount;
    public $wp;

    /**
     * Constructor
     */
    function __construct()
    {
        $this->id = -1;
        $this->inverterId = 0;
        $this->deviceId = 0;
        $this->description='';
        $this->roofOrientation='';
        $this->roofPitch='';
        $this->amount=0;
        $this->wp=0;
    }
}