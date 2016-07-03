<?php
class FileUtil {

    /**
     * Returns the path to last changed file in a directory
     * @param string $dir
     * @param string $suffix
     * @return string
     */
    public static function getLastChangedFileFromDir($dir, $suffix = "") {
        $timestamp = 0;
        $path = "";
        $di = new DirectoryIterator($dir);
        foreach ($di as $fileinfo) {
            if (!$fileinfo->isDot()) {
                if ($suffix == "" || Common::endsWith($fileinfo->getFilename, $suffix)) {
                    if ($fileinfo->getMTime() > $timestamp) {
                        // current file has been modified more recently
                        // than any other file we've checked until now
                        $timestamp = $fileinfo->getMTime();
                        $path = $dir . "/" . $fileinfo->getFilename();
                    }
                }
            }
        }
        return $path;
    }
    
    /**
     * Encodes an object to json and writes it to the give path
     * @param string $filepath
     * @param object $object
     * @param string $mode
     */
    public static function writeObjectToJsonFile($filepath, $object, $mode = 'w') {
    	try {
	    	$fp = fopen($filepath, 'w');
	    	fwrite($fp, json_encode($object));
	    	fclose($fp);
    	} catch (Exception $e) {
    		HookHandler::getInstance()->fire("onError", "writeObjectToJsonFile: " . $e->getMessage());
    	}
    }
    
    /**
     * Get all fileNames in the given Folder
     * @param string $path
     * @return multitype:unknown
     */
    public static function getFileNamesInFolder($path) {
    	$result = array();
    	foreach (scandir($path) as $file) {
    		if (is_file($path.$file))  {
    			$result[] = $file;
    		}
    	}
    	return $result;
    }

    /**
     * Get all dirNames in the given Folder
     * @param string $path
     * @return multitype:unknown
     */
    public static function getDirNamesInFolder($path) {
    	$result = array();
    	foreach (scandir($path) as $file) {
    		if ($file != "." && $file != ".." && is_dir($path.$file))  {
    			$result[] = $file;
    		}
    	}
    	return $result;
    }
    
}
?>