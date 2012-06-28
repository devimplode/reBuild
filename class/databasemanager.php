<?php
class databaseManager extends defaultClass{
	public function __construct(){
	}
	public function loadDefault(){
		if(!system::SM()->has('db.config'))
			try{
				new storage('db.config','fileConfig',CONFIGDIRECTORY.'dbconfig'.EXT);
			}
			catch(StorageException $e){
			}
		if(system::SM()->has('db.config')){
			$conf=system::SM()->get('db.config');
			$entryc=intval($conf->get('EntryCount'));
			if($entryc>=1){
				$defaults=array();
				for($i=0;$i<$entryc;$i++){
					$entry=array();
					$entry['name']=$conf->get('name','db_'.$i);
					$entry['scheme']=$conf->get('scheme','db_'.$i);
					$entry['host']=$conf->get('host','db_'.$i);
					$entry['port']=$conf->get('port','db_'.$i);
					$entry['user']=$conf->get('user','db_'.$i);
					$entry['pass']=$conf->get('pass','db_'.$i);
					$entry['db']=$conf->get('db','db_'.$i);
					$defaults[]=$entry;
				}
				foreach($defaults as $i=>$entry){
					$this->open($entry);
				}
			}
		}
	}
	public function get($data=false){
		if(!$data || !is_string($data))
			return false;
		if(isset($this->db[$data])){
			if($this->db[$data]['data']['db']!==false)
				return $this->db[$data]['db']->selectDB($this->db[$data]['data']['db']);
			else
				return $this->db[$data]['db'];
		}
		return false;
	}
	public function open($data=false){
		if(!$data || !is_array($data))
			return false;
		if(isset($this->db[$data['name']]))
			return $this->db[$data['name']]['db'];
		require_once(CD.DS.'reconnect'.DS.'reconnect.php');
		$db = new reconnect($data);
		$this->db[$data['name']]['db']=$db;
		$this->db[$data['name']]['data']=$data;
		if($data['db']!==false)
				return $db->selectDB($data['db']);
			else
				return $db;
	}
}
?>