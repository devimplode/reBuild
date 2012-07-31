<?php
if(dirname(__FILE__)!=CD){die();}
class system{
	protected static $db;

	function __construct(){
		self::$db=array('classes'=>array(),'aliases'=>array());
		self::register($this);
		function __autoload($className){
			if(file_exists(CD.DS.mb_strtolower($className).EXT))
				require_once(CD.DS.mb_strtolower($className).EXT);
			return;
		}
		$this->initSubsystems();
	}
	protected function initSubsystems(){
		self::load('logManager');
		self::registerAlias('logManager','LOG');
		self::load('storageManager');
		self::registerAlias('storageManager','SM');
		self::SM()->loadDefaultStorage();
		self::load('databaseManager');
		self::registerAlias('databaseManager','DB');
		self::DB()->loadDefault();
		self::load('eventManager');
		self::registerAlias('eventManager','EM');
		self::load('requestManager');
		self::registerAlias('requestManager','RM');
		self::RM()->processRequest();
	}

	public final static function __callStatic($functname,$args){
		if(isset(self::$db['classes'][$functname]))
			return self::$db['classes'][$functname];
		elseif(isset(self::$db['aliases'][$functname]))
			return self::$db['classes'][self::$db['aliases'][$functname]];
		else
			return false;
	}
	public final static function load($classname){
		if(isset(self::$db['classes'][$classname]))
			return true;
		return self::register(new $classname);
	}
	public final static function register($class){
		if(is_object($class)){
			$name=get_class($class);
			if($name!=false){
				self::$db['classes'][$name]=$class;
				return true;
			}
		}
		return false;
	}
	public final static function registerAlias($classname,$alias){
		if(isset(self::$db['classes'][$classname])){
			self::$db['aliases'][$alias]=$classname;
			return true;
		}
		return false;
	}	
}
?>