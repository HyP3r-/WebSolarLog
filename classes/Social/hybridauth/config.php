<?php
	#AUTOGENERATED BY HYBRIDAUTH 2.1.0 INSTALLER - Monday 10th of December 2012 11:02:27 PM

/*!
* HybridAuth
* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
* (c) 2009-2012, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
*/

// ----------------------------------------------------------------------------------------
//	HybridAuth Config file: http://hybridauth.sourceforge.net/userguide/Configuration.html
// ----------------------------------------------------------------------------------------

return 
	array(
		"base_url" => $callback."/classes/Social/hybridauth/", 

		"providers" => array ( 
			// openid providers
			"OpenID" => array (
				"enabled" => true
			),

			"AOL"  => array ( 
				"enabled" => true 
			),

			"Yahoo" => array ( 
				"enabled" => true,
				"keys"    => array ( "id" => "", "secret" => "" )
			),

			"Google" => array ( 
				"enabled" => true,
				"keys"    => array ( "id" => "", "secret" => "" )
			),

			"Facebook" => array ( 
				"enabled" => true,
				"keys"    => array ( "id" => "", "secret" => "" )
			),

			"Twitter" => array ( 
				"enabled" => true,
				"keys"    => array ( "key" => "idYGAJncvuakWv0P0HVp7Q", "secret" => "qJilSF1fmxTZOI7M8ixqWfmAPXDYDLwSCPDWfpE0" ) 
			),

			// windows live
			"Live" => array ( 
				"enabled" => true,
				"keys"    => array ( "id" => "", "secret" => "" ) 
			),

			"MySpace" => array ( 
				"enabled" => true,
				"keys"    => array ( "key" => "", "secret" => "" ) 
			),

			"LinkedIn" => array ( 
				"enabled" => true,
				"keys"    => array ( "key" => "", "secret" => "" ) 
			),

			"Foursquare" => array (
				"enabled" => true,
				"keys"    => array ( "id" => "", "secret" => "" ) 
			),
		),

		// if you want to enable logging, set 'debug_mode' to true  then provide a writable file by the web server on "debug_file"
		"debug_mode" => true,

		"debug_file" => "/home/marco/twitter.debug.log"
	);
