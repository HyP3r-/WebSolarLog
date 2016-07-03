<?php
/**
 * classloader file
 *
 * PHP Version 5.2
 *
 * @category  Kernel
 * @package   classloader
 * @author    Martin Diphoorn <martin@diphoorn.com>
 */


/**
 * Try to load the class
 *
 * @param $classname
 *
 * @return void
 */
function wsl_autoloader($classname)
{
    global $current_module;
    $docRoot = dirname(dirname(__FILE__));

    if ($classname == "") {
        exit("Could not autoload empty classname!");
    }

	// Gather the dirs we need to check
	$classdirs = Array( "/classes", "/classes/objects", "/classes/devices", "/classes/services", "/classes/converters", "/classes/exceptions",  "/addon", "/rest" );

    foreach ($classdirs as $classdir) {
        // Check the domain model
        $filename = $docRoot . $classdir . "/" . $classname . ".php";
        if (file_exists($filename)) {
            require_once($filename);
            return;
        }
    }

    // plugins
    if ($classname === "R" || substr($classname, 0, strlen("RedBean")) === "RedBean") {
        require_once $docRoot ."/classes/redbean.php";
        return;
    }
    
    // plugins
    if ($classname === "Dropbox" ||  substr($classname, 0, strlen("Dropbox")) === "Dropbox") {
    	require_once $docRoot."/classes/Dropbox.php";
    	return;
    }
    
    // plugins
    if ($classname === "Hybrid_Auth" ||  substr($classname, 0, strlen("Hybrid")) === "Hybrid") {
    	//echo $docRoot."/classes/HybridAuth.php";
    	require_once $docRoot."/classes/Hybrid/Hybrid_Auth.php";
    	return;
    }
    
    if (substr($classname, 0, strlen("Model")) === "Model") {
		// We don't handle Model classes, let RedBean do that
        return;
    }
    if ($classname === "PHPMailer") {
        require_once $docRoot . "/classes/phpmailer/class.phpmailer.php";
        require_once $docRoot . "/classes/phpmailer/class.smtp.php";
        return;
    }

    exit("ClassLoader::Basic::Could not autoload: " . $classname. ", " .$docRoot. " is used as documentRoot");
}
spl_autoload_register('wsl_autoloader');

// error handler function
function errorHandlerWSL($errno, $errstr, $errfile, $errline) {
	// Do we need to handle this error as defined by the settings
	if (!(error_reporting() & $errno)) {
		return;
	}
	
	switch ($errno) {
		case E_USER_ERROR:
			$msg = "FATAL error ($errno) in $errfile [$errline] :: $errstr";
			HookHandler::getInstance()->fire("onError", $msg);
			exit(1);
        	break;
		case E_USER_WARNING:
			$msg = "WARNING error ($errno) in $errfile [$errline] :: $errstr";
			HookHandler::getInstance()->fire("onWarning", $msg);
			break;
		case E_USER_NOTICE:
			$msg = "NOTICE error ($errno) in $errfile [$errline] :: $errstr";
			HookHandler::getInstance()->fire("onDebug", $msg);
			break;
		default:
			$msg = "OTHER error ($errno) in $errfile [$errline] :: $errstr";
			HookHandler::getInstance()->fire("onDebug", $msg);
			break;
	}
	
	/* Don't execute PHP internal error handler */
	return true;
}
$old_error_handler = set_error_handler("errorHandlerWSL");
?>