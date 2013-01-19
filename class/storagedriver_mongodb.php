<?php
require_once(CD.DS.'reconnect'.DS.'reconnect.php');
class storageDriver_mongodb extends reconnect{
	/*
	* sorage driver for database connections via mongodb
	* 
	* usage:
	* $url="mongodb://user:password@host:port";
	* $data=system::SM()->get($url)->database->table->select()->query()->getAssoc();
	**/
}
?>