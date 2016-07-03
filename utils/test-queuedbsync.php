<?php
error_reporting(E_ALL);

define('checkaccess', TRUE);

$docRoot = dirname(dirname(__FILE__));
require_once $docRoot . '/classes/classloader.php';
Session::initialize();

$item = new QueueItem(time(), "HookHandler.fireFromQueue", "DB Sync test item", true, 10, true);
//QueueServer::addItemToDatabase($item);
$item->dbId = 8;
QueueServer::removeItemFromDatabase($item);
?>