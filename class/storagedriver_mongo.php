<?php
require_once(CD.DS.'reconnect'.DS.'reconnect.php');
class storageDriver_mongo extends reconnect{
	/*
	* sorage driver for database connections via mongo
	* 
	* usage:
	* $url="mongo://user:password@host:port";
	* $data=system::SM()->get($url)->database->table->select()->query()->getAssoc();
	**/
}
?>