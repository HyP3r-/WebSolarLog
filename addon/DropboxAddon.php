<?php
class DropboxAddon {
	
	public function onInverterShutdown() {
		$dropbox = new Dropbox;
		$config = Session::getConfig();
		//echo "dropboxAddon:<br>";
		//var_dump($dropbox);
		$dbName = explode('/',$config->dbHost);
		$dbName = explode('.',$dbName[count($dbName)-1]);
		$backupFileName = $dbName[0].'_'.date('Ymd').''.date('His').'.backup';

		$dropbox->dropbox->putFile($config->dbHost, $backupFileName);
	}
}
?>