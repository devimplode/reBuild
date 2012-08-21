<?php
class config implements Iterator{
	/**
	 * Usage:
	 * value=system::C()->file->sec->var;
	 * system::C()->file->sec->var=value;
	 * 
	 * also you can use Iterator-Functions like:
	 * foreach(system::C() as $name=>$file){}
	 */
	private $state=false;//false=not initialized; 0=base; 1=file; 2=section; 3=variable;
	private $handler=false;
	private $path=false;
	private $parts=false;
	private $IteratorPos=0;
	private $db=array();
	private $indexdb=array();
	public function __construct($path=false,$handle=false){
		if($path===false && $handle===false){
			//base object
			//system::C()
			$this->state=0;
			$this->loadIndex();
			return;
		}
		elseif(isset($path) && is_string($path) && $path!='' && (($handle instanceOf fileConfigHandler) || ($handle instanceOf config))){
			$parts=explode('->',$path,3);
			$this->state=count($parts);
			if($this->state>=1){
				$this->parts=$parts;
				$this->path=$path;
				$this->handler=$handle;
				$this->loadIndex();
				return;
			}
		}
		throw new Exception();
	}
	public function loadIndex(){
		if($this->state===false)
			return;
		switch($this->state){
			case 0:
				$t=new DirectoryIterator(CONFIGDIRECTORY);
				foreach($t as $id=>$file){
					if(!$file->isDot()){
						$name=$file->getFilename();
						if(substr($name,(strlen(EXT)*-1))==EXT)
							$name=substr($name,0,(strlen(EXT)*-1));
						if(!in_array($name,$this->indexdb))
							$this->indexdb[]=$name;
					}
				}
				unset($t);
				$this->indexdb=array_merge($this->indexdb,array_diff(array_keys($this->db),$this->indexdb));
				break;
			case 1:
				//load file
				$t=$this->handler->getConfig();
				if($t===false)
					break;
				foreach($t as $key=>$sec){
					if(!in_array($key,$this->indexdb))
						$this->indexdb[]=$key;
				}
				$this->indexdb=array_merge($this->indexdb,array_diff(array_keys($this->db),$this->indexdb));
				break;
			case 2:
				//load section
				$t=$this->handler->getSection($this->parts[1]);
				if($t===false)
					break;
				foreach($t as $key=>$var){
					if(!in_array($key,$this->indexdb))
						$this->indexdb[]=$key;
				}
				break;
			default:
		}
		$this->indexdb=array_values($this->indexdb);
		return;
	}
	public function __get($name){
		return $this->get($name);
	}
	public function get($name){
		if($this->state===false)
			return false;
		switch($this->state){
			case 0:
				//get file
				if(isset($this->db[$name])){
					return $this->db[$name];
				}
				try{
					$this->db[$name]=new config($name,new fileConfigHandler(CONFIGDIRECTORY.$name.EXT));
					$this->loadIndex();
					return $this->db[$name];
				}
				catch(StorageException $e){}
				return false;
				break;
			case 1:
				//get section
				if(isset($this->db[$name])){
					return $this->db[$name];
				}
				try{
					$this->db[$name]=new config($this->path.'->'.$name,$this->handler);
					$this->loadIndex();
					return $this->db[$name];
				}
				catch(StorageException $e){}
				return false;
				break;
			case 2:
				//get variable
				//system::C()->system->RM->setting
				return $this->handler->get($name,$this->parts[1]);
				break;
			default:
				return false;
		}
	}
	public function __set($name,$args){
		$this->set($name,$args);
	}
	public function set($name,$args){
		if($this->state===false)
			return false;
		switch($this->state){
			case 0:
				//set file
				//system::C()->backup=system::C()->system
				if(is_a($args,'Iterator')||is_array($args)){
					$t=true;
					foreach($args as $secID=>$sec){
						$t=($this->get($name)->set($secID,$sec) && $t);
					}
					$this->loadIndex();
					return $t;
				}
				return false;
				break;
			case 1:
				//set section
				//system::C()->system->RM=array(0=>'a')
				if(is_a($args,'Iterator')||is_array($args)){
					$t=true;
					foreach($args as $key=>$value){
						$t=($this->handler->set($value,$key,$name) && $t);
					}
					return $t;
				}
				return false;
				break;
			case 2:
				//set variable
				//system::C()->system->RM->setting=$var
				return $this->handler->set($args,$name,$this->parts[1]);
				break;
			default:
				return false;
		}
	}
	public function __unset($name){
		$this->_unset($name);
	}
	public function del($name){
		return $this->_unset($name);
	}
	public function delete($name){
		return $this->_unset($name);
	}
	public function remove($name){
		return $this->_unset($name);
	}
	public function _unset($name){
		//unset(system::C()->system->general->test);
		//we cant name the function unset, so we give them alias names: del, delete, remove
		if($this->state===false)
			return false;
		switch($this->state){
			case 0:
				if(in_array($name,$this->indexdb,true)!==false){
					$this->indexdb=array_filter($this->indexdb,function($var)use($name){return($var!==$name);});//removing from index
					if(isset($this->db[$name]))
						unset($this->db[$name]);//removing from db and destroying/writing config file
					if(file_exists(CONFIGDIRECTORY.$name.EXT) && is_file(CONFIGDIRECTORY.$name.EXT)){
						if(@unlink(CONFIGDIRECTORY.$name.EXT)){//removing config file
							$this->loadIndex();
							return true;
						}
						$this->loadIndex();
						return false;
					}
					else
						$this->loadIndex();
						return true;
				}
				return false;
				break;
			case 1:
				if(in_array($name,$this->indexdb,true)!==false){
					$this->indexdb=array_filter($this->indexdb,function($var){return($var!==$name);});
					if(isset($this->db[$name]))
						unset($this->db[$name]);
					if($this->handler->removeSection($name)){
						$this->loadIndex();
						return true;
					}
					$this->loadIndex();
				}
				break;
			case 2:
				if(in_array($name,$this->indexdb,true)!==false){
					$this->indexdb=array_filter($this->indexdb,function($var){return($var!==$name);});
					if($this->handler->removeKey($name,$this->parts[1])){
						$this->loadIndex();
						return true;
					}
					$this->loadIndex();
				}
				break;
			default:
		}
		return false;
	}
	public function __isset($name){
		if($this->state===false)
			return false;
		switch($this->state){
			case 0:
				//isset file
				return((isset($this->db[$name]))||(fileConfigHandler::is_configFile(CONFIGDIRECTORY.$name.EXT)))?true:false;
				break;
			case 1:
				//isset section
				if(isset($this->db[$name])){
					return true;
				}
				try{
					$this->db[$name]=new config($this->path.'->'.$name,$this->handler);
					return true;
				}
				catch(StorageException $e){}
				return false;
				break;
			case 2:
				//isset variable
				//system::C()->system->RM->setting
				return $this->handler->valid($name,$this->parts[1]);
				break;
			default:
				return false;
		}
	}
	public function rewind(){
		$this->IteratorPos=0;
	}
	public function current(){
		//return current value
		if($this->state===false)
			return false;
		switch($this->state){
			case 0:
				//get file
				return $this->get($this->key());
				break;
			case 1:
				//get section
				return $this->get($this->key());
				break;
			case 2:
				//get variable
				return $this->get($this->key());
				break;
			default:
				return false;
		}
	}
	public function key(){
		return $this->indexdb[$this->IteratorPos];
	}
	public function next(){
		$this->IteratorPos++;
	}
	public function prev(){
		$this->IteratorPos--;
	}
	public function end(){
		$this->IteratorPos=count($this->indexdb)-1;
	}
	public function valid(){
		return (isset($this->indexdb[$this->IteratorPos]))?true:false;
	}
}
?>