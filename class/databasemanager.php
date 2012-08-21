<?php
class databaseManager extends defaultClass{
	public function __construct(){
		$this->loadDefault();
	}
	public function loadDefault(){
		$entryc=intval(system::C()->dbconfig->general->EntryCount);
		if($entryc>=1){
			$defaults=array();
			for($i=0;$i<$entryc;$i++){
				$conf=system::C()->dbconfig->get('db_'.$i);
				$entry=array();
				$entry['name']=$conf->name;
				$entry['scheme']=$conf->scheme;
				$entry['host']=$conf->host;
				$entry['port']=$conf->port;
				$entry['user']=$conf->user;
				$entry['pass']=$conf->pass;
				$entry['db']=$conf->db;
				$defaults[]=$entry;
			}
			foreach($defaults as $i=>$entry){
				$this->open($entry);
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
	public function isOpen($data=false){
		return ($data!==false && is_string($data) && isset($this->db[$data]))?true:false;
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