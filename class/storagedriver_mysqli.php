<?php
require_once(CD.DS.'reconnect'.DS.'reconnect.php');
class storageDriver_mysqli extends reconnect{
	/*
	* sorage driver for database connections via mysqli
	* 
	* usage:
	* $url="mysqli://user:password@host:port";
	* $data=system::SM()->get($url)->database->table->select()->query()->getAssoc();
	**/
}
?>