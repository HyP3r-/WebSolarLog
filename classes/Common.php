<?php
/**
 * Common class file
 *
 * PHP Version 5.2
 *
 * @category  Utils
 * @package   Common
 * @author    Martin Diphoorn <martin@diphoorn.com>
 */

/**
 * Common used functions
 *
 * @category  Utils
 * @package   Common
 * @author    Martin Diphoorn <martin@diphoorn.com>
 * @access    public
 * @since     File available since Release 0.0.0
 */
class Common
{

    /**
     * Returns the value posted, requested or the default if not found.
     *
     * @param string  $name Name of the value
     * @param string  $default default return value when nothing found
     * @param integer $index the index of the value (think about table row lines)
     * @return string
     */
    public static function getValue($name, $default = "", $index = -1)
    {
        // First try to get the post value
        if (isset($_POST[$name]) && $_POST[$name] != "") {
            $value = $_POST[$name];
        } else {
            $value = (isset($_GET[$name]) && $_GET[$name] != "") ? $_GET[$name] : $default;
        }

        if (!is_array($value)) {
            $value = htmlspecialchars($value);
        } else {
            if ($index != -1) {
                $value = $value[$index];
                $value = ($value != "") ? $value : $default;
            }
        }

        return $value;
    }

    /**
     * Try to cast the given object to the class_type
     *
     * @param object &$obj       object we want to cast
     * @param string $class_type type we want to convert to
     * @return
     */
    public static function Cast(&$obj, $class_type)
    {
        if (class_exists($class_type, true)) {
            return unserialize(preg_replace("/^O:[0-9]+:\"[^\"]+\":/i", "O:" . strlen($class_type) . ":\"" . $class_type . "\":", serialize($obj)));
        }
    }

    /**
     * Resolve the domain
     *
     * return string url
     */
    public static function getDomain()
    {
        return isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null ;
    }
    
    /**
     * Returns the root path off wsl
     */
    public static function getRootPath() {
    	return dirname(dirname(__FILE__));
    }

    /**
     * Retrieves the defined constants from the environment
     *
     * @param String $prefix Only return keys with this prefix
     * @return array of key->value pairs
     */
    public static function getDefinedConstants($prefix)
    {
        $found = array();
        foreach (get_defined_constants() as $key => $value) {
            if (substr($key, 0, strlen($prefix)) == $prefix) {
                $found[$key] = $value;
            }
        }
        return $found;
    }

    /**
     * Retrieves the IP Address of the client
     *
     * @since 1.0.1
     * @return string
     */
    public static function getRealIpAddr()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) { //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * Checks if needle is the beginning of the string
     * @param $haystack
     * @param $needle
     * @return boolean
     */
    public static function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    /**
     * Checks if needle is the end of the string
     * @param $haystack
     * @param $needle
     * @return boolean
     */
    public static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

    /**
     * Recursively remove a directory
     * @param $dir
     */
    public static function rrmdir($dir) {
        if (!is_dir($dir)) return; // Only handle dirs that exist
        
        if ($handle = opendir($dir)) {
        	while (false !== ($filename = readdir($handle))) {
        		$file = $dir . "/" . $filename;
	            if ($filename == "." or $filename == "..") {
	            	continue;
	            }
        		
        		if(is_dir($file)) {
	                self::rrmdir($file);
	            } else {
	                if (!unlink($file)) {
	                	HookHandler::getInstance()->fire("onError", "Update error: could not delete file:" . $file . " error: " . self::getLastError());
	                }
	            }
        	}
        	closedir($handle);
        }
        
        if (!rmdir($dir)) {
        	HookHandler::getInstance()->fire("onError", "Update error: could not remove folder:" . $dir . " error: " . self::getLastError());
        }
    }

    /**
     * Recursively copy a directory
     * @param $src
     * @param $dest
     */
    public static function xcopy($src,$dest)
    {
        foreach  (scandir($src) as $file) {
            if (!is_readable($src.'/'.$file) || $file == '.' || $file == '..') continue;
            if (is_dir($src.'/'.$file)) {
                mkdir($dest . '/' . $file);
                self::xcopy($src.'/'.$file, $dest.'/'.$file);
            } else {
                copy($src.'/'.$file, $dest.'/'.$file);
            }
        }
    }


    /**
     * Make sure if the give path is available, else try to create
     *
     * @param string $path
     * @return boolean false if the path is invalid
     */
    public static function checkPath($path)
    {
        // Check if the path is available
        if (!is_dir($path)) {
            if (!mkdir($path)) {
                //echo("Could not create: " . $path);
                return false;
            }
        }

        return true;
    }

    private static $last_mail_count = 0 ;
    private static $last_mail_subject = "";
    private static $last_mail_time = 0;
    
    /**
     * Sends out an email with the given settings
     * @param string $subject
     * @param string $body
     * @return string|boolean
     */
    public static function sendMail($subject, $body, $config) {
    	HookHandler::getInstance()->fire("onDebug",__METHOD__."::Starting with function sendMail()");
    	try {
	        $mail = new PHPMailer();
	        // $mail->SMTPDebug = true; Use this for testing only
	
	        $mail->IsSMTP();  // telling the class to use SMTP
	        $mail->IsHTML(true);
	        $mail->Host = $config->smtpServer;
	        $mail->Port = $config->smtpPort;
	        $mail->FromName = $config->emailFromName;
	        $mail->From = $config->emailFrom;
	        $emails = explode(';', $config->emailTo);
	        
	        foreach($emails as $email) {
	        	$mail->AddAddress($email);
	        }
	
	        if ($config->smtpUser && $config->smtpPassword) {
	            $mail->SMTPAuth = true;
	            $mail->Username = $config->smtpUser;
	            $mail->Password = $config->smtpPassword;
	        }
	
	        if (trim($config->smtpSecurity) != "" && trim($config->smtpSecurity != "none")) {
	            $mail->SMTPSecure = $config->smtpSecurity;
	        }
	        
	        $mail->Subject  = $subject;
	        $mail->Body     = $body;
	        $mail->WordWrap = 50;
	        
	        // Reset if last mail was longer then 30 seconds a go
	        if (time() - self::$last_mail_time > 30) {
	        	self::$last_mail_count = 0;
	        }
	        
	        // Check if it is a loop
	        if (self::$last_mail_subject === $subject) {
	        	self::$last_mail_count++;
	        	HookHandler::getInstance()->fire("onDebug",__METHOD__."::mail loop detected count=" . self::$last_mail_count);
	        } else {
	        	self::$last_mail_subject = $subject;
	        	self::$last_mail_count = 0;
	        }
	        self::$last_mail_time = time();

	        
	        if (self::$last_mail_count > 5) {
	        	HookHandler::getInstance()->fire("onDebug",__METHOD__."::mail blocked more then 5 times withing 30 seconds");
	        	return "mail blocked more then 5 times withing 30 seconds";
	        }
	
	        if(!$mail->Send()) {
	        	HookHandler::getInstance()->fire("onDebug",__METHOD__."::SendEmail error: ".print_r($mail->ErrorInfo,true));
	            return $mail->ErrorInfo;
	        } else {
	            return true;
	        }
    	} catch (Exception $e) {
    		HookHandler::getInstance()->fire("onDebug",__METHOD__."::SendEmail Exception".print_r($e->getMessage(),true));
    		return $e->getMessage();	
    	}
    }


    public static function searchMultiArray($array, $key, $value)
    {
    	$results = array();

    	if (is_array($array))
    	{
    		if (isset($array[$key]) && $array[$key] == $value)
    			$results[] = $array;

    		foreach ($array as $subarray)
    			$results = array_merge($results, self::searchMultiArray($subarray, $key, $value));
    	}

    	return $results;
    }
    
    public static function getShortUrl($url) {
    	$ch = curl_init("http://is.gd/create.php?format=simple&url=" . $url);
    	curl_setopt($ch, CURLOPT_HEADER, 0);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	return curl_exec($ch);
    }
    
    /**
     * This method will be called from the queueServer to refresh the loaded config
     * So we can be sure we are talking to latest settings and devices
     */
    public static function refreshConfig() {
    	Session::getConfig(true);
    }
    
    /**
     * Do we need to restart?
     */
    public static function checkRestart() {
    	if (Session::getConfig()->restartWorker) {
    		$config = Session::getConfig(true); // Retrieve up to date config
    		$config->restartWorker = false;
    		PDODataAdapter::getInstance()->writeConfig($config);
    		R::commit(); // Make sure we de an commit
    		QueueServer::getInstance()->stop();
    		sleep (1);
    		exit("Restarting worker\n");
    	}
    }
    
    /**
     * Exit the current php process, use with care and probably only for QueueItems
     */
    public static function exitProcess() {
    	QueueServer::getInstance()->stop();
    	
    	// Create an Janitor Item
    	$item = new QueueItem(time(), "JanitorRest.DbCheck", null, false, 0, true);
    	QueueServer::addItemToDatabase($item);
    	
    	exit();
    }
    
    /**
     * Exit the current php process, use with care and probably only for QueueItems
     */
    public static function exitCronProcess() {
    	QueueServer::getInstance()->stop();
    	 
    	exit();
    }
    
    /**
     * Do we need to pause?
     * @deprecated
     */
    public static function checkPause() {
    	// Do nothing
    }
    
    /**
     * tell the queueServer it should restart
     */
    public static function createRestartQueueItem () {
    	QueueServer::addItemToDatabase(new QueueItem(time(), "Common.exitProcess", "", false, 0, true));
    }
    
    public static function getLastError() {
    	$error = error_get_last();
    	if (isset($error)) {
    		return $error['type'] . ". " . $error['message'] . " " . $error['file'] . "[" . $error['line'] . "]";
    	}
    }
    
    function avrgArrayValue()
    {
    	$count = func_num_args();
    	$args = func_get_args();
    	return (array_sum($args) / $count);
    }
}
?>