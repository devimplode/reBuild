<?php
class storageManager extends defaultClass{
	/**
	 * @value: array($entryId=>$link)
	 */
	protected $db_access=array();

	public function __construct(){}

	// creates new storage handler
	public function open($link){
		try{
			if(!is_a($link,'link'))
				$link=new link($link);
			if($link->get('scheme')!==false){
				$class = 'storageDriver_'.$link->get('scheme');
				
				$link->fragment=$entryId=count($this->db);
				
				$entryHandler=new $class($link);
				
				$this->db[$entryId]=array('link'=>$link,'state'=>true,'handler'=>$entryHandler);
				$this->db_access[$entryId]=$this->getBaseLink($link);
				return $entryHandler;
			}
		}
		catch(StorageException $e){}
		catch(Exception $e){}
		return(false);
	}
	public function isOpen($link){
		if(!is_a($link,'link'))
			$link=new link($link);
		$id = $this->getLastStorageId($link);
		if(isset($this->db[$id]))
			return(true);
		return(false);
	}
	// returns the last storage handler for the given link or creates a first one
	public function get($link){
		if(!is_a($link,'link'))
			$link=new link($link);
		$id=$this->getLastStorageId($link);
		if($id!==false){
			//found a storage
			//remove the old one...
			unset($this->db_access[$id]);
			//and put it at the end
			$this->db_access[$id]=$this->getBaseLink($link);
			//update link
			$link->fragment=$id;
			$storage=$this->db[$id]['handler'];
		}
		else
			$storage=$this->open($link);
		return($storage);
	}
	public function close($link){
		if(!is_a($link,'link'))
			$link=new link($link);
		$id=$this->getLastStorageId($link);
		if($id!==false){
			// call the close()-method of the storage
			if($this->db[$id]['handler']->close()){
				// and remove this storage entry
				unset($this->db[$id],$this->db_access[$id],$link->fragment);
				return(true);
			}
		}
		return(false);
	}
	// returns id of the specified or last used storage handler for a given link
	public function getLastStorageId($link){
		if(!is_a($link,'link'))
			$link=new link($link);
		// get the fragment from the given link
		$id=$link->get('fragment');
		if($id!==false){
			// did we like this fragment?
			if(isset($this->db[$id]))
				return($id);
			// no, we didn't - remove it, so we can skip this part the next time
			$link->remove('fragment');
		}
		// beginn the search at the end
		end($this->db_access);
		while($entry=each($this->db_access)){
			// compare them
			// btw: both values are baseLinks of the type link - they haven't a fragment
			if($entry['value']==$link){
				// found!!! - we will take it
				// but keep in mind we didn't write the $entry['key'] (the $id) into the $link->fragment
				return($entry['key']);
			}
			// and go backwards
			prev($this->db_access);
		}
		return(false);
	}
	// returns the last used storage handler for a given link
	public function getLastStorage($link){
		if(!is_a($link,'link'))
			$link=new link($link);
		$id = $this->getLastStorageId($link);
		if(isset($this->db[$id]) && isset($this->db[$id]['handler']))
			return $this->db[$id]['handler'];
		return(false);
	}
	/**
	 * Helper
	 */
	// cuts of the fragment part
	public function getBaseLink($link){
		if(is_a($link,'link')){
			$link=clone $link;
			$link->remove('fragment');
			return($link);
		}
		return(false);
	}
	
}
?>