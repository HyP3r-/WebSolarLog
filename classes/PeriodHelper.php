<?php

class PeriodHelper
{
    /**
     * Check if the period has been passed
     * @param string $name
     * @param int $interval in minutes
     * @return boolean
     */
    public static function isPeriodJob($name, $interval)
    {
        $currenttime = time();
        $jobname = 'job_' . $name;

        // Retrieve record
        $bean = R::findOne('periodhelper', ' name = :name', array(':name' => $jobname));
        if (!$bean) {
            $bean = R::dispense('periodhelper');
            $bean->name = $jobname;
            $bean->lastrun = 0;
        }

        if (($currenttime - $bean->lastrun) > ($interval * 60)) {
            // Cut the seconds off the currenttime
            $bean->lastrun = $currenttime - ($currenttime % 60);
            R::store($bean);
            return true;
        } else {
            // not yet time
            return false;
        }
    }
}