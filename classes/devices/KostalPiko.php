<?php

Class KostalPiko implements DeviceApi
{
    private $ADR;
    private $DEBUG;
    private $PATH;

    private $device;
    private $communication;
    private $useCommunication = false;

    function __construct($path, $address, $debug)
    {
        $this->ADR = $address;
        $this->DEBUG = $debug;
        $this->PATH = $path;
    }

    function setCommunication(Communication $communication, Device $device)
    {
        $this->communication = $communication;
        $this->device = $device;
        $this->useCommunication = true;
    }

    /**
     * @see DeviceApi::getState()
     */
    public function getState()
    {
        return 0; // Try to detect, as it will die when offline
    }

    public function getAlarms()
    {
        if ($this->DEBUG) {
            return rand(0, 9);
        } else {
            $output = trim($this->execute(" -s -q"));
            $lines = explode("\n", $output);
            if ($lines[1] != "0") {
                return $lines[1];
            } else {
                return "";
            }
        }
    }

    public function getData()
    {
        if ($this->DEBUG) {
            return "PRO,Piko,1,1.3.0,20130730
TIM,2013-07-30T13:22:35.801768,17419h59m39s,8411h39m19s
INF,90xxxKBNxxxxx,Piko_name,192.168.1.10,81,1,PIKO 8.3,2,3
STA,3,Running-MPP,28,---L123,0
ENE,11629195,13803
PWR,4760,4531,95.2
DC1,596.2,3.97,2370,51.21,94e0,4009
DC2,614.9,3.88,2390,51.21,94e0,c00a
DC3,0.0,0.00,0,51.29,94c0,0003
AC1,241.6,6.31,1528,60.14,8540
AC2,236.4,6.16,1466,60.14,8540
AC3,243.5,6.27,1537,60.07,8560
PRT,PIKO-Portal,01h48m49s
HST,00h07m36s,00h15m00s";
        } else {
            return trim($this->execute(" -csv -q"));
        }
    }

    public function getLiveData()
    {
        $data = $this->getData();
        $live = KostalPikoConverter::toLive($data);
        HookHandler::getInstance()->fire("onDebug", print_r($live, true));
        return $live;
    }

    public function getInfo()
    {
        if ($this->DEBUG) {
            return "PIKO 8.3 90342JCO0001K";
        } else {
            $output = $this->execute(" -m -r -q");
            $lines = explode("\n", $output);
            return $lines[2] . " " . $lines[0] . " " . $lines[3];
        }
    }

    public function getHistoryData()
    {
        // not supported
        return null;
    }

    public function syncTime()
    {
        // not supported
        return null;
    }


    public function doCommunicationTest()
    {
        $result = false;
        $data = $this->execute(" -csv -q");
        if ($data) {
            $result = true;
        }

        return array("result" => $result, "testData" => $data);
    }


    private function execute($options)
    {
        $cmd = "";
        if ($this->useCommunication === true) {
            $cmd = $this->communication->uri . " " . $this->communication->optional . " " . $options . " ";
        } else {
            $cmd = $this->PATH . " " . $options;
        }

        $exec = shell_exec($cmd);
        HookHandler::getInstance()->fire("onDebug", print_r($exec, true));
        return $exec;
    }
}

?>
