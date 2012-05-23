<?php
class mod{
	private static $db=array();
	public static function __callStatic($functionName,$args){
		if(in_array($functionName,self::$db)){
			return $functionName($args);
		}
		if(file_exists(MD.DS.mb_strtolower($functionName).EXT)){
			require_once(MD.DS.mb_strtolower($functionName).EXT);
			self::$db[]=$functionName;
			return $functionName($args);
		}
		$e = new Exception('Function not found: '.$functionName);
		system::LOG()->wtf('mod','Function not found: '.$functionName, 500, $e);
		throw $e;
	}
}
?>