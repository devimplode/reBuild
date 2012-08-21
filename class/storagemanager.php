<?php
class storageManager extends defaultClass{
	public function __construct(){
		$this->loadDefaultStorage();
	}
	
	public function registerStorage($storage){
		if(isset($this->db[$storage->getName()]))
			return false;
		$this->db[$storage->getName()]=array('type'=>$storage->getType(),'storage'=>&$storage);
		return true;
	}
	public function open($name,$type,$connection){
		if(isset($this->db[$name]))
			return false;
		try{
			$re=$this->registerStorage(new storage($name,$type,$connection,false));
		}
		catch(StorageException $e){return false;}
		return $re;
	}
	public function isOpen($name){
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
			$this->open('storage.default.config','fileConfig',CONFIGDIRECTORY.'storage.default'.EXT);
			$entryc=intval(system::C()->get('storage.default')->general->EntryCount);
			if($entryc>=1){
				$defaults=array();
				for($i=0;$i<$entryc;$i++){
					$conf=system::C()->get('storage.default')->get('storage_'.$i);
					$entry=array();
					$entry['name']=$conf->name;
					$entry['path']=$conf->path;
					$entry['type']=$conf->type;
					$defaults[]=$entry;
				}
				foreach($defaults as $i=>$entry){
					try{
						$this->open($entry['name'],$entry['type'],CONFIGDIRECTORY.$entry['path'].EXT);
					}
					catch(StorageException $e){
						system::LOG()->w('StorageException',"Couldn't load default storage object: '".$entry['name']."' - ".$e->getMessage(),500);
					}
				}
			}
		}
		catch(StorageException $e){}
	}
}
?>