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
		self::load('config');
		self::registerAlias('config','C');
		self::load('logManager');
		self::registerAlias('logManager','LOG');
		self::load('eventManager');
		self::registerAlias('eventManager','EM');
		self::load('storageManager');
		self::registerAlias('storageManager','SM');
		self::load('requestManager');
		self::registerAlias('requestManager','RM');
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
		if(isset(self::$db['aliases']['EM']))self::EM()->call('onSystemRegister',$class);
		if(is_object($class)){
			$name=get_class($class);
			if($name!=false){
				if(isset(self::$db['aliases']['EM']))self::EM()->call('onSystemRegister.'.$name,$class);
				self::$db['classes'][$name]=$class;
				if(isset(self::$db['aliases']['EM']))self::EM()->call('onSystemRegistered',array($name=>$class));
				if(isset(self::$db['aliases']['EM']))self::EM()->call('onSystemRegistered.'.$name,$class);
				return true;
			}
		}
		return false;
	}
	public final static function registerAlias($classname,$alias){
		if(isset(self::$db['aliases']['EM']))self::EM()->call('onSystemRegisterAlias',array('class'=>$classname,'alias',$alias));
		if(isset(self::$db['aliases']['EM']))self::EM()->call('onSystemRegisterAlias.'.$alias,array('class'=>$classname,'alias',$alias));
		if(isset(self::$db['classes'][$classname])){
			self::$db['aliases'][$alias]=$classname;
			if(isset(self::$db['aliases']['EM']))self::EM()->call('onSystemRegisteredAlias',array('class'=>$classname,'alias',$alias));
			if(isset(self::$db['aliases']['EM']))self::EM()->call('onSystemRegisteredAlias.'.$alias,array('class'=>$classname,'alias',$alias));
			return true;
		}
		return false;
	}	
}
?>