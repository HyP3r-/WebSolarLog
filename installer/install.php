<?php
$url = "http://svn.code.sf.net/p/websolarlog/code/tags/";

// Check if php-svn module has been installed
if (!function_exists("svn_ls") || !is_writable("..")) {
?>
<p>We could not continue with the installer</p>
<p>Check the following on your system:</p>
<ul>
	<li>Make sure that all files and folder are writable by php.</li>
    <li>Make sure you have installed php-svn (apt-get install php5-svn on debian/ubuntu)</li>
</ul>
<?php 
	exit();
}

$step = getValue("step",0);
$experimental = getValue("experimantal", false);

$versions = array();
if ($step == 0) {
	$tags = svn_ls($url);
	foreach ($tags as $tag) {
		if ($experimental) {
			$versions[] = array('name'=>$tag['name'],'revision'=>$tag['created_rev'],'experimental'=>true);
		} else {
			if (startsWith($tag['name'], "release") || startsWith($tag['name'], "stable")) {
				$versions[] = array('name'=>$tag['name'],'revision'=>$tag['created_rev'], 'experimental'=>false);
			}
		}
	}
}

if ($step == 1) {
    $version = getValue('version','',0);
    $svnurl = $url . $version;
    echo ($svnurl);

    prepareCheckout();
    $export_result = doCheckout($svnurl);

    if ($export_result) {
        copyToLive();

        // Cleanup
        prepareCheckout();
        
        // Check if database folder exists
        checkPath("../database");
        checkPath("../log");
        
        echo ("Update done <br />");
        exit();
    } else {
        echo ("export failed<br />Stopped update process");
        exit();
    }
}
?>
<html>
<head>
</head>
<body>
<?php
  // ### select the version to be installed
 if ($step == 0) {
?>
<form>
<input type="hidden" name="step" value="1" />
<ul>
<?php
    foreach ($versions as $version) {
        $name = $version['name'];
        echo('<li><input type="checkbox" name="version[]" value="'.$name.'"/>'.$name.'</li>');
    }
?>
</ul>
<button type="submit">Updaten</button>
</form>
<?php
 } // en off version selection
?>
<?php
function prepareCheckout() {
    // We need to have an temp folder if it exist we need to remove it
    if (is_dir("temp/export")) {
        ob_flush();
        rrmdir("temp/export");
    }

    // Try to create the temp folder
    checkPath("temp/export");
}

function doCheckout($urlsvn) {
    // Try to do an export
    return svn_export ($urlsvn , "temp/export", false );
}

function copyToLive() {
    // We dont want to copy everything, so specify which dirs we dont want
    $skipDirs = Array( "data","database","installer" );
    $source = "temp/export/";
    $target = "../";

    foreach (scandir($source) as $file) {
        // Skip files we can read and the dot(dot) folders
        if (!is_readable($source.'/'.$file) || $file == '.' || $file == '..') continue;
        if (is_dir($source.$file) && !in_array($file, $skipDirs) ) {
            // Remove the target dir before updating it
            rrmdir($target . $file);

            // Make sure the target dir is available, always create it
            checkPath($target . $file);

            // Copy all files over
            xcopy($source . $file, $target . $file);
        }
        if (is_file($source.$file))  {
            copy($source . $file, $target . $file);
        }
    }
}
?>
</body>
</html>
<?php 
function getValue($name, $default = "", $index = -1)
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

function rrmdir($dir) {
	if (!is_dir($dir)) return; // Only handle dirs that exist
	foreach(glob($dir . '/*') as $file) {
		if(is_dir($file)) {
			rrmdir($file);
		} else {
			unlink($file);
		}
	}
	rmdir($dir);
}

function checkPath($path)
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

function xcopy($src,$dest)
{
	foreach  (scandir($src) as $file) {
		if (!is_readable($src.'/'.$file) || $file == '.' || $file == '..') continue;
		if (is_dir($src.'/'.$file)) {
			mkdir($dest . '/' . $file);
			xcopy($src.'/'.$file, $dest.'/'.$file);
		} else {
			copy($src.'/'.$file, $dest.'/'.$file);
		}
	}
}

function startsWith($haystack, $needle)
{
	$length = strlen($needle);
	return (substr($haystack, 0, $length) === $needle);
}
?>