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
	public function has($name){
		return (isset($this->db[$name]))?true:false;
	}
	public function get($name){
		return (isset($this->db[$name]))?($this->db[$name]['storage']->getHandler()):false;
	}
	public function close($name){
		$this->db[$name]['storage']->close();
	}
	public function loadDefaultStorage(){
		try{
			new storage('storage.default.config','fileConfig',CONFIGDIRECTORY.'storage.default'.EXT);
			$entryc=intval($this->get('storage.default.config')->get('EntryCount'));
			if($entryc>=1){
				$defaults=array();
				for($i=0;$i<$entryc;$i++){
					$entry=array();
					$entry['name']=$this->get('storage.default.config')->get('name','storage_'.$i);
					$entry['path']=$this->get('storage.default.config')->get('path','storage_'.$i);
					$entry['type']=$this->get('storage.default.config')->get('type','storage_'.$i);
					$defaults[]=$entry;
				}
				foreach($defaults as $i=>$entry){
					try{
						new storage($entry['name'],$entry['type'],CONFIGDIRECTORY.$entry['path'].EXT);
					}
					catch(StorageException $e){
						system::LOG()->w('StorageException',"Couldn't load default storage object: '".$entry['name']."' - ".$e->getMessage(),500);
					}
				}
			}
		}
		catch(StorageException $e){}
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