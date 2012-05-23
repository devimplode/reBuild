<?php
if(dirname(__FILE__)!=CD){die();}
class system{
	protected static $system;
	protected static $eventManager;
	protected static $requestManager;
	protected static $storageManager;
	protected static $logManager;
	
	
	function __construct(){
		self::$system = $this;
		function __autoload($className){
			if(file_exists(CD.DS.mb_strtolower($className).EXT))
				require_once(CD.DS.mb_strtolower($className).EXT);
			return;
		}
		$this->initSubsystems();
	}
	
	protected function initSubsystems(){
		self::$logManager = new logManager();
		self::$storageManager = new storageManager();
		self::$storageManager->loadDefaultStorage();
		self::$eventManager = new eventManager();
		self::$requestManager = new requestManager();
		self::$requestManager->processRequest();
	}
	
	public final static function getEventManager(){
		return self::$eventManager;
	}
	public final static function EM(){
		return self::getEventManager();
	}
	public final static function getRequestManager(){
		return self::$requestManager;
	}
	public final static function RM(){
		return self::getRequestManager();
	}
	public final static function getLogManager(){
		return self::$logManager;
	}
	public final static function LOG(){
		return self::getLogManager();
	}
	public final static function getStorageManager(){
		return self::$storageManager;
	}
	public final static function SM(){
		return self::getStorageManager();
	}
}
?>