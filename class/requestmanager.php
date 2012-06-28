<?php
/*Requests

www.example.com/anchor[.php]
www.example.com/[index[.php]]?arg*1=val*1&arg*n=val*n... GET
www.example.com/abstract/url/only/seperated/by/regex/registration

*/

class requestManager extends defaultClass{
	private $request=array();
	private $get=array();
	private $post=array();
	private $file=array();
	
	public function __construct(){
		//get Request
		$this->analyzeRequest();
		//load config
		
				
	}
	private function analyzeRequest(){
		$this->request['host']=$_SERVER['HTTP_HOST'];
		$this->request['remote_addr']=$_SERVER['REMOTE_ADDR'];
		$this->request['client']=$_SERVER['REMOTE_ADDR'];//alias
		$this->request['remote_port']=$_SERVER['REMOTE_PORT'];
		$this->request['client_port']=$_SERVER['REMOTE_PORT'];//alias
		$this->request['method']=$_SERVER['REQUEST_METHOD'];
		$this->request['time']=$_SERVER['REQUEST_TIME'];
		$this->request['uri']=(mb_strstr('/',$_SERVER['REQUEST_URI'])===0)?mb_substr($_SERVER['REQUEST_URI'],1):$_SERVER['REQUEST_URI'];
		
		if(isset($_SERVER['REDIRECT_STATUS'])){
			$this->request['redirected']=true;
			$this->request['query']=(isset($_SERVER['REDIRECT_QUERY_STRING']))?$_SERVER['REDIRECT_QUERY_STRING']:$_SERVER['QUERY_STRING'];
			$this->request['url']=$_SERVER['REDIRECT_URL'];
		}
		else{
			$this->request['query']=$_SERVER['QUERY_STRING'];
		}
		
		$this->request['anchor']=preg_filter("/^\/([\w\._]+)(([\/]|[?]).*)*/","$1",$_SERVER['REQUEST_URI']);
		if($this->request['anchor']!=NULL)
		switch(mb_substr($this->request['anchor'], mb_strrpos($this->request['anchor'],'.')+1)){
			case 'php':
				$this->request['anchor_type']='php';
				break;
			case 'png':
				$this->request['anchor_type']='png';
				break;
			case 'jpg':
				$this->request['anchor_type']='jpg';
				break;
			case 'js':
				$this->request['anchor_type']='js';
				break;
			default:
				$this->request['anchor_type']='other';
		}
		
		$this->get=$_GET;
		$this->post=$_POST;
		$this->request['get']=&$this->get;
		$this->request['post']=&$this->post;
		
	}
	public function processRequest(){
		if(isset($this->request['anchor']))
			switch($this->request['anchor_type']){
				case 'php':
					//check for ports
					if(system::SM()->get('system.config')->get('Ports','RequestConf')=="on")
						$this->loadPorts();
					break;
				case 'jpg':
				case 'png':
					if(file_exists(SD.DS.'public'.DS.$this->request['anchor'])){
						// directly output
						header("Pragma: public");
						header('Content-type: '.mod::getFileContentType(SD.DS.'public'.DS.$this->request['anchor']));
						header("Content-Transfer-Encoding: binary");
						header("Content-Length: ".filesize(SD.DS.'public'.DS.$this->request['anchor'])); 
						//output file
						readfile(SD.DS.'public'.DS.$this->request['anchor']);
					}
					break;
				default:
			}
		else{
			$portsDir=system::SM()->get('system.config')->get('PortsDirectory','RequestConf');
			$defaultPort=system::SM()->get('system.config')->get('DefaultPort','RequestConf');
			if($defaultPort!==false && $portsDir!==false)
				if(file_exists(RD.DS.$portsDir.DS.$defaultPort))
					include(RD.DS.$portsDir.DS.$defaultPort);
		}
	}
	private function loadPorts(){
		if(!isset($this->request['anchor']) && $this->request['anchor_type']=='php')
			return;
		$portsDir=system::SM()->get('system.config')->get('PortsDirectory','RequestConf');
		if($portsDir!==false){
			$portsDir=RD.DS.$portsDir;
			if(file_exists($portsDir.DS.$this->request['anchor'])){
				include($portsDir.DS.$this->request['anchor']);
			}
		}
	}
	public function request($what){
		if(isset($this->request[$what]))
			return $this->request[$what];
		return false;
	}
	public function get($what,$type='string'){
		if(isset($this->get[$what])){
			switch($type){
				case'int':
					return intval($this->get[$what]);
					break;
				case'float':
					return floatval($this->get[$what]);
					break;
				case'bool':
					return (!empty($this->get[$what]))?true:false;
					break;
				default:
				case'string':
					return (string) $this->get[$what];
			}
		}
		return false;
	}
}
?>