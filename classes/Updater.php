<?php
class Updater {
    public static $url = "http://svn.code.sf.net/p/websolarlog/code/";
    public static $urlReleases = "http://www.websolarlog.com/json/";
    public static $problems = array();
    public static $basepath = "../tmp";

    function __construct() {
    }

    function __destruct() {
    }

    /**
     * Checks if we can do auto update
     */
    public static function isUpdateable(){
        self::$problems = array(); // Reset the problems
        $result = true;
        if (!extension_loaded('svn')) {
        	$distro = explode("\n",shell_exec('cat /etc/lsb-release'));
        	self::$problems[] = "SVN module not found. Try to install php5-svn package under ". (is_array($distro) ? $disto[0] : $distro) . ".";
            $result = false;
        }
        
       // $distro = explode("\n",shell_exec('cat /etc/lsb-release'));
        //self::$problems[] = "SVN module not found. Try to install php5-svn package under ".$distro['DISTRIB_DESCRIPTION'].".";
        //$result = false;
        return $result;
    }


    /**
     * Retrieves the available versions
     * @param boolean $experimental
     * @return array
     */
    public static function getVersions($showExperimental = false, $showBeta = false) {
    	$json = file_get_contents(self::$urlReleases . "newReleases.php");
    	$jsonVersions = json_decode($json, TRUE);
    	$versions = array();
    	
    	foreach($jsonVersions['stable'] as $stable){
    		if ($stable['display']) {
    			$versions['stable'][] = $stable;
    		}
    	}
    	foreach($jsonVersions['beta'] as $beta){
    		if ($beta['display'] && $showBeta) {
    			$svn = svn_ls($beta['path']);
    			$versions['beta'][] = $beta;
    		}
    	}
    	 
        if ($jsonVersions['trunk'][0]['display'] && $showExperimental) {
            $trunk = svn_ls(self::$url);
            $versions['trunk'][0] = $jsonVersions['trunk'][0];																																																																					
            $versions['trunk'][0]['timestamp'] = $trunk['trunk']['time_t'];
            $versions['trunk'][0]['revision'] = $trunk['trunk']['created_rev'];
            $versions['trunk'][0]['path'] = self::$url."trunk/";
        }

        return $versions;
    }

    /**
     * Checks/Creates the folders
     * return boolean
     */
    public static function prepareCheckout() {
        if (!Common::checkPath(self::$basepath)) {
            return false;
        }

        // We need to have an temp folder if it exist we need to remove it
        if (is_dir(self::$basepath . "/export")) {
            Common::rrmdir(self::$basepath . "/export");
        }

        // Try to create the temp folder
        return Common::checkPath(self::$basepath . "/export");
    }

    /**
     * Do a checkout off the given version
     * @param string $urlsvn
     * @param int $revision 
     * @return boolean
     */
    public static function doCheckout($urlsvn, $revision=-1) {
        // Try to do an export
        return svn_export ($urlsvn, self::$basepath . "/export", false, $revision);
    }

    /**
     * Deploy the version checkedout
     */
    public static function copyToLive() {
        // We dont want to copy everything, so specify which dirs we dont want
        $skipDirs = Array( "data", "database", "scripts", "tmp", "log","systemPhotos");
        $source = self::$basepath . "/export/";
        $target = "../";

        foreach (scandir($source) as $file) {
            // Skip files we can read and the dot(dot) folders
            if (!is_readable($source.$file) || $file == '.' || $file == '..') continue;
            
            if(!is_dir($source.$file)){
            	Common::checkPath($target . $file);
            }
            
            if (is_dir($source.$file) && !in_array($file, $skipDirs) ) {
                // Remove the target dir before updating it
                Common::rrmdir($target . $file);

                // Make sure the target dir is available, always create it
                Common::checkPath($target . $file);

                // Copy all files over
                Common::xcopy($source . $file, $target . $file);
            }
            if (is_file($source.$file))  {
                copy($source . $file, $target . $file);
            }
        }

        // Update a few script files
        copy($source . "scripts/worker.php", $target . "scripts/worker.php");
        copy($source . "scripts/server.php", $target . "scripts/server.php");
        copy($source . "scripts/wsl.sh", $target . "scripts/wsl.sh");        
    }
}