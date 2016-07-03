<?php
class Event {
	public $id;
    public $INV;
    public $deviceId;
    public $SDTE;
    public $time;
    public $type;
    public $event;
    public $alarmSend;
    
    // @Transient
    public $eventHTML;

    function __construct($deviceId, $date, $type, $event) {
        $this->INV = $deviceId;
        $this->deviceId = $deviceId;
        $this->SDTE = date("Ymd H:i:s", $date);
        $this->time = $date;
        $this->type = $type;
        $this->event = $event;
        $this->alarmSend = false;
    }
}
?>