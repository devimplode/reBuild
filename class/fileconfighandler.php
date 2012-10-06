<?php
class fileConfigHandler extends defaultClass{
	private $filename=false;
	private $edited=false;
	
	public function __construct($filename=false){
		$this->filename=$filename;
		if(!self::is_configFile($filename))
			return;
		$db=parse_ini_file($filename,true);
		if($db === false)
			throw new StorageException("Couldn't parse ".$filename);
		foreach($db as $secKey=>$sec){
			$this->db[$secKey]=array();
			foreach($sec as $key=>$val){
				$this->db[$secKey][$key]=(((@unserialize($val)!==false) || ($val===serialize(false)))?unserialize($val):$val);
			}
		}
	}
	public function __destruct(){
		$this->live=false;
		$this->write();
		//save data to file
	}
	public function get($key, $sec='general'){
		return((array_key_exists($sec,$this->db))?((array_key_exists($key,$this->db[$sec]))?$this->db[$sec][$key]:false):false);
	}
	public function getSection($sec='general'){
		return(is_string($sec)&&isset($this->db[$sec]))?$this->db[$sec]:false;
	}
	public function getConfig(){
		return $this->db;
	}
	public function set($value, $key, $sec='general'){
		$this->edited=true;
		if(!isset($this->db[$sec]))
			$this->db[$sec]=array();
		$this->db[$sec][$key]=$value;
		system::LOG()->i('config',"Setting '".$sec."->".$key."' to ".((is_string($value)||is_int($value))?"'".$value."'":((is_object($value))?"<".get_class($value).">":((is_array($value))?'<Array>':'<Object>')))." in file '".$this->filename."'");
		$this->write();// in case of class-destruction
		return true;
	}
	public function valid($key, $sec='general'){
		return((array_key_exists($sec,$this->db))?((array_key_exists($key,$this->db[$sec]))?true:false):false);
	}
	public function removeKey($key, $sec='general'){
		if(isset($this->db[$sec])&&isset($this->db[$sec][$key])){
			unset($this->db[$sec][$key]);
			$this->edited=true;
			system::LOG()->i('config',"Unsetting key '".$sec."->".$key."' in file '".$this->filename."'");
			$this->write();// in case of class-destruction
			return true;
		}
		return false;
	}
	public function removeSection($sec){
		if(isset($this->db[$sec])){
			unset($this->db[$sec]);
			$this->edited=true;
			system::LOG()->i('config',"Unsetting section '".$sec."' in file '".$this->filename."'");
			$this->write();// in case of class-destruction
			return true;
		}
		return false;
	}
	private function write(){
		if($this->live || !$this->edited)
			return;
		system::LOG()->v('config',"Gathering changes for file '".$this->filename."'");
		$this->db=$this->arrayCheckClosures($this->db);
		$res = array();
		foreach($this->db as $key => $val){
			if(is_array($val)){
				$res[] = EOL."[".$key."]";
				foreach($val as $skey => $sval)
					$res[] = $skey.' = "'.str_replace('"','\"',str_replace('\\','\\\\',serialize($sval))).'"';
			}
			else
				$res[] = $key.' = "'.str_replace('"','\"',serialize($val)).'"';
		}
		system::LOG()->d('config',"Writing changes to file '".$this->filename."'");
		mod::fileWrite($this->filename, utf8_encode("; <?php die(); ?>".EOL.implode(EOL, $res).EOL),0);
	}
	private function arrayCheckClosures($arr){
		$ret=array();
		foreach($arr as $key=>$val){
			if(is_array($val))
				$ret[$key]=$this->arrayCheckClosures($val);
			elseif($val instanceOf Closure)
				$ret[$key]=new reClosure($val);
			else
				$ret[$key]=$val;
		}
		return $ret;
	}
	
	public static final function is_configFile($filename=false){
		if(is_file($filename))
			if(preg_match("/^; <\?php die\(\); \?>([\r\n]{1,2})+([\w]+ = (\"([^\r\n\"]|[\\\][\"])*\"|\d+)([\r\n]{1,2})+)*(([\r\n]{1,2})*\[[\w]+\]([\r\n]{1,2})+([\w]+ = (\"([^\r\n\"]|[\\\][\"])*\"|\d+)([\r\n]{1,2})+)*)*\$/im",@file_get_contents($filename)))
				return true;
		return false;
	}
}
?>