<?php
require_once(CD.DS.'reconnect'.DS.'reconnect.php');
class storageDriver_mysql extends reconnect{
	/*
	* sorage driver for database connections via mysql
	* 
	* usage:
	* $url="mysql://user:password@host:port";
	* $data=system::SM()->get($url)->database->table->select()->query()->getAssoc();
	**/
}
?>