<?php
class fileConfigHandler extends defaultClass{
	private $filename=false;
	private $edited=false;
	
	public function __construct($filename=false){
		if(!(self::is_configFile($filename)))
			throw new StorageException($filename." isn't a valid configFile");
		$this->filename=$filename;
		$this->db=parse_ini_file($filename,true);
		if($this->db === false)
			throw new StorageException("Couldn't parse ".$filename);
	}
	public function __destruct(){
		$this->live=false;
		$this->write();
		//save data to file
	}
	public function get($key, $sec='general'){
		return((array_key_exists($sec,$this->db))?((array_key_exists($key,$this->db[$sec]))?$this->db[$sec][$key]:false):false);
	}
	public function set($value, $key, $sec='general'){
		$this->edited=true;
		$this->db[$sec][$key]=$value;
		system::LOG()->i('config',"Setting '".$sec."->".$key."' to '".$value."' in file '".$this->filename."'");
		$this->write();// in case of class-destruction
	}
	private function write(){
		if($this->live || !$this->edited)
			return;
		system::LOG()->v('config',"Gathering changes for file '".$this->filename."'");
		$res = array();
		foreach($this->db as $key => $val)
		{
			if(is_array($val))
			{
				$res[] = EOL."[".$key."]";
				foreach($val as $skey => $sval)
					$res[] = $skey." = ".(is_numeric($sval) ? $sval : '"'.str_replace('"','\"',$sval).'"');
			}
			else
				$res[] = $key." = ".(is_numeric($val) ? $val : '"'.str_replace('"','\"',$val).'"');
		}
		system::LOG()->d('config',"Writing changes to file '".$this->filename."'");
		mod::fileWrite($this->filename, utf8_encode("; <?php die(); ?>".EOL.implode(EOL, $res).EOL),0);
	}
	
	public static final function is_configFile($filename=false){
		if(is_file($filename))
		{
			if(preg_match("/^; <\?php die\(\); \?>([\r\n]{1,2})+([\w]+ = (\"([^\r\n\"]|[\\\][\"])*\"|\d+)([\r\n]{1,2})+)*(([\r\n]{1,2})*\[[\w]+\]([\r\n]{1,2})+([\w]+ = (\"([^\r\n\"]|[\\\][\"])*\"|\d+)([\r\n]{1,2})+)*)*\$/im",@file_get_contents($filename)))
			{
				return true;
			}
		}
		return false;
	}
}
?>