<?php

class DeviceService {

    public static $tbl = "inverter";
    public $panelService;

    function __construct() {
        $this->panelService = new PanelService();
        HookHandler::getInstance()->add("onJanitorDbCheck", "DeviceService.janitorDbCheck");
    }

    /**
     * Save the object to the database
     * @param Device $object
     * @return Device
     */
    public function save(Device $object) {
        $bObject = ($object->id > 0) ? R::load(self::$tbl, $object->id) : R::dispense(self::$tbl);
        $bObject = $this->toBean($object, $bObject);
        $object->id = R::store($bObject);
        return $object;
    }

    /**
     * Load an object from the database
     * @param int $id
     * @return Device
     */
    public function load($id) {
        $bObject = R::load(self::$tbl, $id);
        if ($bObject->id > 0) {
            $object = $this->toObject($bObject);
        }
        return isset($object) ? $object : new Device();
    }

    public function checkCommunicationUsed($communicationId) {
        $bObjects = R::find(self::$tbl, ' 	communicationId = ' . $communicationId);
        $objects = array();
        foreach ($bObjects as $bObject) {
            $objects[] = $this->toObject($bObject);
        }
        return $objects;
    }

    /**
     * Delete an object from the database
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        // load bean to delete
        $bObject = R::load(self::$tbl, $id);
        $object = $this->toObject($bObject);
        if ($object->type == "production") {
            $panelService = new PanelService();

            // remove all panels of this device
            foreach ($this->panelService->getArrayByDevice($object) as $panel) {
                $panelService->delete($panel->id);
            }
        }
        $bObject = R::load(self::$tbl, $id);
        // trash the bean
        R::trash($bObject);
        // check if bean still there and return result.
        return (R::load(self::$tbl, $id)->id > 0) ? false : true;
    }

    /**
     * Retrieves all devices
     * @return Array of Device
     */
    public function getAllDevices() {
        $bObjects = R::find(self::$tbl);
        $objects = array();
        foreach ($bObjects as $bObject) {
            $objects[] = $this->toObject($bObject);
        }
        return $objects;
    }

    /**
     *
     * @param int $deviceId
     * @param int $mode
     */
    public function setTestMode($deviceId, $mode = false) {
        //load device
        $device = $this->load($deviceId);

        //set testMode
        $device->testMode = $mode;

        //save device
        $this->save($device);
        return true;
    }

    /**
     * Retrieves all active devices
     * @return Array of Device
     */
    public function getActiveDevices() {
        $bObjects = R::find(self::$tbl, ' active = 1 ');
        $objects = array();
        foreach ($bObjects as $bObject) {
            $objects[] = $this->toObject($bObject);
        }
        return $objects;
    }

    public function getSupportedDevices() {
        return array(
            array('value' => 'AURORA', 'type' => 'production', 'name' => 'Aurora'),
            array('value' => 'DeltaSolivia', 'type' => 'production', 'name' => 'DeltaSolivia'),
            array('value' => 'Diehl-ethernet', 'type' => 'production', 'name' => 'Diehl Ethernet'),
            array('value' => 'DutchSmartMeter', 'type' => 'metering', 'name' => 'Dutch Smart Meter'),
            array('value' => 'DutchSmartMeterRemote', 'type' => 'metering', 'name' => 'Dutch Smart Meter Remote'),
            array('value' => 'SmartMeterAmpyRemote', 'type' => 'metering', 'name' => 'Smart Meter Ampy Remote'),
            array('value' => 'KostalPiko', 'type' => 'production', 'name' => 'Kostal Piko'),
            array('value' => 'MasterVolt', 'type' => 'production', 'name' => 'MasterVolt'),
            array('value' => 'SoladinSolget', 'type' => 'production', 'name' => 'SoladinSolget'),
            array('value' => 'Open-Weather-Map', 'type' => 'weather', 'name' => 'Open Weather Map'),
            array('value' => 'SMA-RS485', 'type' => 'production', 'name' => 'SMA RS485'),
            array('value' => 'SMA-BT-WSL', 'type' => 'production', 'name' => 'SMA-spot-2.0.6 BlueTooth'),
            array('value' => 'OMNIK', 'type' => 'production', 'name' => 'Omnik')
        );
    }

    /**
     * change the status off the device
     * @param int $status // 1=active, 0=sleep
     * @param Device $device // Device object
     * @return boolean // changed yes or no;
     */
    function changeDeviceStatus(Device $device, $state) {
        // get the device
        $freshDevice = $this->load($device->id);

        // look if we have a bean
        if ($freshDevice == null || $freshDevice->id != $device->id) {
            // If we can't find the bean there is a serious problem exit!
            HookHandler::getInstance()->fire("onError", "changeDeviceStatus: Could not find device for id:" . $device->id);
            return null;
        }

        // check if we are going to change the device status
        $changed = false;
        if ($freshDevice->state != $state) {
            // oo we are going to change the device, so we set it to TRUE
            $changed = true;
            // change the bean to the new status for this device
            $freshDevice->state = $state;

            //Store the bean with the new device status
            $this->save($freshDevice);
        }
        return $changed;
    }

    public function janitorDbCheck() {
        HookHandler::getInstance()->fire("onDebug", "DeviceService janitor DB Check");
        // Get an device and save it, to make sure al fields are available in the database
        $objects = $this->getAllDevices();
        $object = $objects[0]; // first
        $bObject = R::load(self::$tbl, $object->id);

        // Check if we have an active field on the old object
        $setAllActive = (!isset($bObject['active']));

        // Save it
        R::store($this->toBean($object, $bObject));

        // Set all to active
        if ($setAllActive) {
            R::exec("UPDATE inverter SET active = 1");
        }

        R::exec("UPDATE inverter SET deviceId = INV");
        R::exec("CREATE INDEX history_deviceId ON 'history' ( 'deviceId' ) ;");
    }

    private function toBean($object, $bObject) {
        $bObject->active = $object->active;
        $bObject->deviceApi = $object->deviceApi;
        $bObject->type = $object->type;
        $bObject->name = $object->name;
        $bObject->description = $object->description;
        $bObject->liveOnFrontend = $object->liveOnFrontend;
        $bObject->graphOnFrontend = $object->graphOnFrontend;
        $bObject->initialkwh = $object->initialkwh;
        $bObject->producesSince = $object->producesSince;
        $bObject->expectedkwh = $object->expectedkwh;
        $bObject->communicationId = $object->communicationId;
        $bObject->comAddress = $object->comAddress;
        $bObject->comLog = $object->comLog;
        $bObject->syncTime = $object->syncTime;
        $bObject->pvoutputEnabled = ($object->pvoutputEnabled != "") ? $object->pvoutputEnabled : $bObject->pvoutputEnabled;
        $bObject->pvoutputApikey = $object->pvoutputApikey;
        $bObject->pvoutputSystemId = $object->pvoutputSystemId;
        $bObject->pvoutputWSLTeamMember = $object->pvoutputWSLTeamMember;
        $bObject->sendSmartMeterData = ($object->sendSmartMeterData != "") ? $object->sendSmartMeterData : true;
        $bObject->pvoutputAutoJoinTeam = ($object->pvoutputAutoJoinTeam != "") ? $object->pvoutputAutoJoinTeam : true;

        $bObject->state = $object->state;
        $bObject->testMode = $object->testMode;
        $bObject->refreshTime = (isset($object->refreshTime) && $object->refreshTime != "") ? $object->refreshTime : $bObject->refreshTime;
        $bObject->historyRate = (isset($object->historyRate) && $object->historyRate != "") ? $object->historyRate : $bObject->historyRate;

        $bObject->expectedJAN = $object->expectedJAN;
        $bObject->expectedFEB = $object->expectedFEB;
        $bObject->expectedMAR = $object->expectedMAR;
        $bObject->expectedAPR = $object->expectedAPR;
        $bObject->expectedMAY = $object->expectedMAY;
        $bObject->expectedJUN = $object->expectedJUN;
        $bObject->expectedJUL = $object->expectedJUL;
        $bObject->expectedAUG = $object->expectedAUG;
        $bObject->expectedSEP = $object->expectedSEP;
        $bObject->expectedOCT = $object->expectedOCT;
        $bObject->expectedNOV = $object->expectedNOV;
        $bObject->expectedDEC = $object->expectedDEC;

        return $bObject;
    }

    private function toObject($bObject) {
        $object = new Device();
        if (!isset($bObject)) {
            return $object;
        }
        $object->id = $bObject->id;
        $object->active = $bObject->active;
        $object->deviceApi = $bObject->deviceApi;
        $object->type = $bObject->type;
        $object->name = $bObject->name;
        $object->description = $bObject->description;
        $object->liveOnFrontend = $bObject->liveOnFrontend;
        $object->graphOnFrontend = $bObject->graphOnFrontend;
        $object->initialkwh = $bObject->initialkwh;
        $object->producesSince = $bObject->producesSince;
        $object->expectedkwh = $bObject->expectedkwh;
        $object->communicationId = $bObject->communicationId;
        $object->comAddress = $bObject->comAddress;
        $object->comLog = $bObject->comLog;
        $object->syncTime = $bObject->syncTime;
        $object->pvoutputEnabled = ($bObject->pvoutputEnabled != "") ? $bObject->pvoutputEnabled : $object->pvoutputEnabled;
        $object->pvoutputApikey = $bObject->pvoutputApikey;
        $object->pvoutputSystemId = $bObject->pvoutputSystemId;
        $object->pvoutputWSLTeamMember = $bObject->pvoutputWSLTeamMember;
        $object->sendSmartMeterData = ($bObject->sendSmartMeterData != "") ? $bObject->sendSmartMeterData : $object->sendSmartMeterData;
        $object->pvoutputAutoJoinTeam = ($bObject->pvoutputAutoJoinTeam != "") ? $bObject->pvoutputAutoJoinTeam : $object->pvoutputAutoJoinTeam;

        $object->state = $bObject->state;
        $object->testMode = $bObject->testMode;
        $object->refreshTime = (isset($bObject->refreshTime) && $bObject->refreshTime != "") ? $bObject->refreshTime : $object->refreshTime;
        $object->historyRate = (isset($bObject->historyRate) && $bObject->historyRate != "") ? $bObject->historyRate : $object->historyRate;


        // prevent fast polling on OWM API
        if ($object->deviceApi == "Open-Weather-Map") {
            $object->refreshTime = 600; // force 600sec(10min) to prevent fast data polling on the OWM API
            $object->historyRate = 600; // force 600sec(10min) to prevent fast data polling on the OWM API
        }

        // retrieve by panelService
        $object->panels = $this->panelService->getArrayByDevice($object);
        $object->plantpower = 0;
        foreach ($object->panels as $panel) {
            $object->plantpower += ($panel->amount * $panel->wp);
        }

        $object->expectedJAN = ($bObject->expectedJAN != 'NaN') ? $bObject->expectedJAN : 0;
        $object->expectedFEB = ($bObject->expectedFEB != 'NaN') ? $bObject->expectedFEB : 0;
        $object->expectedMAR = ($bObject->expectedMAR != 'NaN') ? $bObject->expectedMAR : 0;
        $object->expectedAPR = ($bObject->expectedAPR != 'NaN') ? $bObject->expectedAPR : 0;
        $object->expectedMAY = ($bObject->expectedMAY != 'NaN') ? $bObject->expectedMAY : 0;
        $object->expectedJUN = ($bObject->expectedJUN != 'NaN') ? $bObject->expectedJUN : 0;
        $object->expectedJUL = ($bObject->expectedJUL != 'NaN') ? $bObject->expectedJUL : 0;
        $object->expectedAUG = ($bObject->expectedAUG != 'NaN') ? $bObject->expectedAUG : 0;
        $object->expectedSEP = ($bObject->expectedSEP != 'NaN') ? $bObject->expectedSEP : 0;
        $object->expectedOCT = ($bObject->expectedOCT != 'NaN') ? $bObject->expectedOCT : 0;
        $object->expectedNOV = ($bObject->expectedNOV != 'NaN') ? $bObject->expectedNOV : 0;
        $object->expectedDEC = ($bObject->expectedDEC != 'NaN') ? $bObject->expectedDEC : 0;

        return $object;
    }

}

?>