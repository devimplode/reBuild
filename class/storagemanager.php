<?php
class storageManager extends defaultClass{
	public function __construct(){
		
	}
	
	public function registerStorage($storage){
		if(isset($this->db[$storage->getName()]))
			return false;
		$this->db[$storage->getName()]=array('type'=>$storage->getType(),'storage'=>&$storage);
		return true;
	}
	public function get($name){
		return (isset($this->db[$name]))?($this->db[$name]['storage']->getHandler()):false;
	}
	public function close($name){
		$this->db[$name]['storage']->close();
	}
	public function loadDefaultStorage(){
		try{
			new storage('system.config','fileConfig',CONFIGDIRECTORY.'system'.EXT);
		}
		catch(StorageException $e){
			system::LOG()->e('StorageException',"Couldn't load default storage objects: ".$e->getMessage(),500);
			return false;
		}
	}
}
?>