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
	private $_requestState=0;
	
	public function __construct(){
		//get Request
		$this->analyzeRequest();
		//load config
		
				
	}
	private function analyzeRequest(){
		$this->request['host_name']=$_SERVER['SERVER_NAME'];//our name
		$this->request['host_addr']=$_SERVER['SERVER_ADDR'];//requested server address
		$this->request['host_port']=$_SERVER['SERVER_PORT'];//our server port
		$this->request['host']=(isset($_SERVER['HTTP_HOST']))?$_SERVER['HTTP_HOST']:$_SERVER['SERVER_ADDR'];//maybe we get a plain request on our page without any host header
		$this->request['host']=($_SERVER['SERVER_PORT']!='80' && ($_SERVER['SERVER_PORT']!='443' && isset($_SERVER['HTTPS'])))?$this->request['host'].':'.$_SERVER['SERVER_PORT']:$this->request['host'];//alias
		$this->request['remote_addr']=$_SERVER['REMOTE_ADDR'];//the client
		$this->request['client']=$_SERVER['REMOTE_ADDR'];//alias of the remote address
		$this->request['remote_port']=$_SERVER['REMOTE_PORT'];
		$this->request['client_port']=$_SERVER['REMOTE_PORT'];//alias
		$this->request['method']=$_SERVER['REQUEST_METHOD'];//the request method: GET/HEAD/POST/PUT
		$this->request['proto']=$_SERVER['SERVER_PROTOCOL'];//the protocol: HTTP/1.1
		$this->request['accept']=(isset($_SERVER['HTTP_ACCEPT']))?$_SERVER['HTTP_ACCEPT']:'';//ACCEPT-Header: image/png
		$this->request['accept_charset']=(isset($_SERVER['HTTP_ACCEPT_CHARSET']))?mb_strtolower($_SERVER['HTTP_ACCEPT_CHARSET']):'utf-8';
		$this->request['accept_encoding']=(isset($_SERVER['HTTP_ACCEPT_ENCODING']))?mb_strtolower($_SERVER['HTTP_ACCEPT_ENCODING']):'';
		$this->request['accept_language']=(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))?mb_strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']):'';
		$this->request['connection']=(isset($_SERVER['HTTP_CONNECTION']))?mb_strtolower($_SERVER['HTTP_CONNECTION']):'';//for example: keep-alive
		$this->request['referer']=(isset($_SERVER['HTTP_REFERER']))?mb_strtolower($_SERVER['HTTP_REFERER']):'';
		$this->request['user_agent']=(isset($_SERVER['HTTP_USER_AGENT']))?mb_strtolower($_SERVER['HTTP_USER_AGENT']):'';//for example: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:10.0.2) Gecko/20120219 Firefox/10.0.2
		$this->request['time']=$_SERVER['REQUEST_TIME'];
		
		$this->request['uri']=urldecode($_SERVER['REQUEST_URI']);
		$this->request['query']=urldecode($_SERVER['QUERY_STRING']);
		
		$this->request['path']=preg_replace("-^(/[/\w\s\._]*/)+([\w\s\._\?#=]*)$-","$1",$this->request['uri']);
		
		$this->request['anchor']=preg_replace("-^(/[/\w\s\._]*/)+([\w\s\._]*)(\?.*)*(#.*)*$-","$2",$this->request['uri']);
		$this->request['anchor_type']=($this->request['anchor']!=NULL && mb_strpos($this->request['anchor'],'.')!==false)?mb_substr($this->request['anchor'], mb_strrpos($this->request['anchor'],'.')+1):false;
		
		$this->get=$_GET;
		$this->post=$_POST;
		$this->request['get']=&$this->get;
		$this->request['post']=&$this->post;
		
		$this->_requestState=1;
	}
	public function processRequest(){
		while($this->_requestState>=1 && $this->_requestState<100){
			$this->_requestState++;
			system::LOG()->v('requestmanager.processor',"_requestState is now on level '".intval($this->_requestState)."'");
			switch($this->_requestState){
				case 2:
					$this->loadRegisteredRequest();
					break;
				case 3:
					//parsing anchor
					$this->loadRegisteredAnchor();
					break;
				case 4:
					$this->loadDefaultPort();
					break;
				case 5:
					if(system::SM()->get('system.config')->get('ShowError','RequestConf')=="on")
						$this->loadErrorPage("404");
					break;
				default:
					//extend this list with more handlers, if you want - and set the default fallback number 5 to higher ranges (up to 99)
			}
		}
		system::LOG()->d('requestmanager.processor',"processRequest halted! internal counter _requestState is on level '".intval($this->_requestState)."'");
	}
	public function finishRequest(){
		//call this function to stop requestManager::processRequest()
		$this->_requestState=-1;
		system::LOG()->v('requestmanager.processor','finishRequest called. switching to requestState -1! request processor should stop after this round.');
	}
	private function loadRegisteredRequest(){
		if(!system::SM()->isOpen('RM.requests'))
			try{new storage('RM.requests','fileConfig',CONFIGDIRECTORY.'RM.requests'.EXT);}catch(StorageException $e){return false;}
		if(!system::SM()->isOpen('RM.requests'))
			return false;
		$conf=system::SM()->get('RM.requests')->getConfig();
		$registeredMatch=false;
			foreach($conf as $id=>$data){
				//parse filter
				$flags=(isset($data['flags']))?$data['flags']:array();
				if(isset($data['filter']) && isset($data['action']) && $this->filterMatch($data['filter'],$flags)){
					$tmp=array('directorys'=>array(),'file'=>$this->request['anchor']); // store temporary stuff here
					foreach($data['action'] as $cmd=>$args){
						switch(mb_strtolower($cmd)){
							case'run':
								if(is_array($args))
									foreach($args as $k=>$v){
										if($v instanceof reClosure){ //0=>function(){system::do(stuff);}
											$v();
										}
										elseif(is_string($v) && function_exists($v) && in_array($v,get_defined_functions())){ //0=>'functionname'
											$v();
										}
										elseif(is_string($k) && is_string($v) && (class_exists($k) && in_array($k,get_declared_classes())) && (method_exists($k,$v) && in_array($v,get_class_methods($k))) ){ //'classname'=>'functionname'
											$k::$v();
										}
									}
								else
									if($args instanceof reClosure){ //0=>function(){system::do(stuff);}
										$args();
									}
									elseif(is_string($args) && function_exists($args) && in_array($args,get_defined_functions())){ //'run'=>'functionname'
										$args();
									}
								break;
							case'include':
								if(is_array($args))
									foreach($args as $file){
										if(is_file($file))
											@include($file);
									}
								elseif(is_string($args)){
									if(is_file($args))
										@include($args);
								}
								break;
							case'header':
								if(is_array($args))
									foreach($args as $k=>$v){
										if(is_string($k) && is_string($v))
											header($k.': '.$v);
										elseif(is_string($v))
											header($v);
									}
								elseif(is_string($args))
									header($args);
								break;
							case'direct':
								if(is_array($args))
									foreach($args as $file){
										if(is_file($file))
											readfile($file);
									}
								elseif(is_string($args)){
									if(is_file($args))
										readfile($args);
								}
								break;
							case'lookup':
								if(is_array($args))
									foreach($args as $dir){
										if(is_string($dir) && is_dir($dir))
											$tmp['directorys'][]=$dir;
										elseif(is_string($dir) && is_dir(RD.DS.$dir))
											$tmp['directorys'][]=RD.DS.$dir;
										elseif(is_string($dir) && is_dir(SD.DS.$dir))
											$tmp['directorys'][]=SD.DS.$dir;
									}
								else{
									if(is_string($args) && is_dir($args))
										$tmp['directorys'][]=$args;
									elseif(is_string($args) && is_dir(RD.DS.$args))
										$tmp['directorys'][]=RD.DS.$args;
									elseif(is_string($args) && is_dir(SD.DS.$args))
										$tmp['directorys'][]=SD.DS.$args;
								}
								break;
							case'anchorappend':
								if(is_string($args))
									$tmp['file']=$this->request['anchor'].$args;
								break;
							default:
						}//end switch(mb_strtolower($cmd))
					}//end foreach($data['action'] as $cmd=>$args)
					if(count($tmp['directorys']))
						foreach($tmp['directorys'] as $dir)
							if(is_file($dir.DS.$tmp['file'])){
								readfile($dir.DS.$tmp['file']);
								break;
							}
					if(in_array('last',$flags) || (isset($flags['last']) && $flags['last']=true)){
						$this->finishRequest();
						return true;
					}
					$registeredMatch=true;
				}//end action
			}//end foreach($conf as $id=>$data)
			if($registeredMatch)
				return true;
		return false;
	}
	private function loadRegisteredAnchor(){
		if(isset($this->request['anchor']) && isset($this->request['anchor_type'])){
			if(!system::SM()->isOpen('RM.anchors'))
				try{new storage('RM.anchors','fileConfig',CONFIGDIRECTORY.'RM.anchors'.EXT);}catch(StorageException $e){return false;}
			if(!system::SM()->isOpen('RM.anchors'))
				return false;
			$conf=system::SM()->get('RM.anchors')->getConfig();
			$registeredMatch=false;
			foreach($conf as $id=>$data){
				/**$data=array(
				*	'filter'=>array('%or'=>array('anchor_type'=>'^(png|jpe?g|gif)$','path'=>'^images$')),
				*	'flags'=>array('last','sensitive'=>false),
				*	'action'=>array('run'=>array(Closure(),'classname'=>'functionname'),'header'=>array('Content-type: text/plain','Pragma'=>'public'),'direct'=>'store/public/yay.png','include'=>'store/ports/index.php','anchorappend'=>'.jpg','lookup'=>'store/public')
				*	);
				**/
				//parse filter
				$flags=(isset($data['flags']))?$data['flags']:array();
				if(isset($data['filter']) && isset($data['action']) && $this->filterMatch($data['filter'],$flags)){
					$tmp=array('directorys'=>array(),'file'=>$this->request['anchor']); // store temporary stuff here
					foreach($data['action'] as $cmd=>$args){
						switch(mb_strtolower($cmd)){
							case'run':
								if(is_array($args))
									foreach($args as $k=>$v){
										if($v instanceof reClosure){ //0=>function(){system::do(stuff);}
											$v();
										}
										elseif(is_string($v) && function_exists($v) && in_array($v,get_defined_functions())){ //0=>'functionname'
											$v();
										}
										elseif(is_string($k) && is_string($v) && (class_exists($k) && in_array($k,get_declared_classes())) && (method_exists($k,$v) && in_array($v,get_class_methods($k))) ){ //'classname'=>'functionname'
											$k::$v();
										}
									}
								else{
									if($args instanceof reClosure){ //0=>function(){system::do(stuff);}
										$args();
									}
									elseif(is_string($args) && function_exists($args) && in_array($args,get_defined_functions())){ //'run'=>'functionname'
										$args();
									}
								}
								break;
							case'include':
								if(is_array($args))
									foreach($args as $file){
										if(is_file($file))
											@include($file);
									}
								elseif(is_string($args)){
									if(is_file($args))
										@include($args);
								}
								break;
							case'header':
								if(is_array($args))
									foreach($args as $k=>$v){
										if(is_string($k) && is_string($v))
											header($k.': '.$v);
										elseif(is_string($v))
											header($v);
									}
								elseif(is_string($args))
									header($args);
								break;
							case'direct':
								if(is_array($args))
									foreach($args as $file){
										if(is_file($file))
											readfile($file);
									}
								elseif(is_string($args)){
									if(is_file($args))
										readfile($args);
								}
								break;
							case'lookup':
								if(is_array($args))
									foreach($args as $dir){
										if(is_string($dir) && is_dir($dir))
											$tmp['directorys'][]=$dir;
										elseif(is_string($dir) && is_dir(RD.DS.$dir))
											$tmp['directorys'][]=RD.DS.$dir;
										elseif(is_string($dir) && is_dir(SD.DS.$dir))
											$tmp['directorys'][]=SD.DS.$dir;
									}
								else{
									if(is_string($args) && is_dir($args))
										$tmp['directorys'][]=$args;
									elseif(is_string($args) && is_dir(RD.DS.$args))
										$tmp['directorys'][]=RD.DS.$args;
									elseif(is_string($args) && is_dir(SD.DS.$args))
										$tmp['directorys'][]=SD.DS.$args;
								}
								break;
							case'anchorappend':
								if(is_string($args))
									$tmp['file']=$this->request['anchor'].$args;
								break;
							default:
						}//end switch(mb_strtolower($cmd))
					}//end foreach($data['action'] as $cmd=>$args)
					if(count($tmp['directorys']))
						foreach($tmp['directorys'] as $dir)
							if(is_file($dir.DS.$tmp['file'])){
								readfile($dir.DS.$tmp['file']);
								break;
							}
					if(in_array('last',$flags) || (isset($flags['last']) && $flags['last']=true)){
						$this->finishRequest();
						return true;
					}
					$registeredMatch=true;
				}//end action
			}//end foreach($conf as $id=>$data)
			if($registeredMatch)
				return true;
		}
		return false;
	}
	private function loadDefaultPort(){
		if(system::SM()->get('system.config')->get('Ports','RequestConf')=="on"){
			$portsDir=system::SM()->get('system.config')->get('PortsDirectory','RequestConf');
			$defaultPort=system::SM()->get('system.config')->get('DefaultPort','RequestConf');
			if($defaultPort!==false && $portsDir!==false)
				if(file_exists(RD.DS.$portsDir.DS.$defaultPort)){
					include(RD.DS.$portsDir.DS.$defaultPort);
					$this->finishRequest();
					return true;
				}
		}
		return false;
	}
	public function loadPorts(){
		if(!isset($this->request['anchor']) || $this->request['anchor_type']!='php' || (system::SM()->get('system.config')->get('Ports','RequestConf')!="on"))
			return false;
		$portsDir=system::SM()->get('system.config')->get('PortsDirectory','RequestConf');
		if($portsDir!==false){
			$portsDir=RD.DS.$portsDir;
			if(file_exists($portsDir.DS.$this->request['anchor'])){
				include($portsDir.DS.$this->request['anchor']);
				return true;
			}
		}
		return false;
	}
	public function loadErrorPage($code="500",$text="Internal Server Error"){
		$epDir=(defined('ERRORPAGEDIRECTORY'))?ERRORPAGEDIRECTORY:(system::SM()->get('system.config')->get('ErrorpageDirectory'));
		if(is_string($epDir)){
			$epDir=(is_dir($epDir))?$epDir:((is_dir(RD.DS.$epDir))?RD.DS.$epDir:false);
			if($epDir){
				if(is_string($code) && is_file($epDir.DS.$code.EXT)){
					require_once($epDir.DS.$code.EXT);
					$this->finishRequest();
					return true;
				}
				elseif(is_file($epDir.DS."default".EXT)){
					require_once($epDir.DS."default".EXT);
					$this->finishRequest();
					return true;
				}
			}
		}
		$this->finishRequest();//we should cut here...
		$e=new Exception("Failed to access errorpage.");
		system::LOG()->e('requestmanager.loadErrorPage',$e->getMessage(),$code);
		throw $e;
	}
	
	public final function filterMatch($filter,$flags=array('sensitive'=>false),$glue='%and'){
		/*
		* $filter=array('%or'=>array('anchor_type'=>'^(png|jpe?g|gif)$','path'=>'^images$'));
		* $flags=array('sensitive'=>true);
		*/
		if(!is_array($filter) || !is_array($flags))
			return false;
		if(!isset($flags['sensitive']))
			$flags['sensitive']=false;
		$true=0;
		foreach($filter as $key=>$value){
			switch($key){
				case'%and':
				case'%or':
					$re=$this->filterMatch($value,$flags,$key);
					if($re==true)
						$true++;
					if($re==true && $glue=='%or')
						return true;
					if($re==false && $glue=='%and')
						return false;
					break;
				default:
					/*
					'variable'=>'string'
					'get.variable'=>123
					*/
					if(is_string($key)){
						if(strpos($key,'.')!==false){
							$keyparts=explode('.',$key);
							$arr=$this->request;
							for($i=0;$i<count($keyparts);$i++){
								if(isset($arr[$keyparts[$i]]) && is_array($arr[$keyparts[$i]])){
									$arr=$arr[$keyparts[$i]];
								}
								elseif(isset($arr[$keyparts[$i]])){
									$var=$arr[$keyparts[$i]];
									break;
								}
							}
						}
						elseif(isset($this->request[$key]))
							$var=$this->request[$key];
						else{
							//not set
							if($glue=='%and')
								return false;
							break;
						}
						
						if((is_string($value)||is_numeric($value)) && (($flags['sensitive'] && $var===$value) || ($flags['sensitive']==false && mb_strtolower($var)===mb_strtolower($value)))){
							$true++;
							if($glue=='%or')
								return true;
							break;
						}
						/*
						'variable'=>array('%gt'=>5,'%lte'=>10)
						'get.variable'=>array('%gt'=>5,'%lte'=>10)
						'post.variable'=>array('%gt'=>5,'%lte'=>10)
						*/
						if(is_array($value)){
							$t=0;
							foreach($value as $c=>$v){
								switch($c){
									case'%gt':
									case'%>':
										if(($flags['sensitive'] && $var > $v) || ($flags['sensitive']==false && mb_strtolower($var) > mb_strtolower($v)))
											$t++;
										break;
									case'%gte':
									case'%>=':
										if(($flags['sensitive'] && $var >= $v) || ($flags['sensitive']==false && mb_strtolower($var) >= mb_strtolower($v)))
											$t++;
										break;
									case'%lt':
									case'%<':
										if(($flags['sensitive'] && $var < $v) || ($flags['sensitive']==false && mb_strtolower($var) < mb_strtolower($v)))
											$t++;
										break;
									case'%lte':
									case'%<=':
										if(($flags['sensitive'] && $var <= $v) || ($flags['sensitive']==false && mb_strtolower($var) <= mb_strtolower($v)))
											$t++;
										break;
									case'%eq':
									case'%==':
										if(($flags['sensitive'] && $var == $v) || ($flags['sensitive']==false && mb_strtolower($var) == mb_strtolower($v)))
											$t++;
										break;
									case'%ne':
									case'%!=':
										if(($flags['sensitive'] && $var != $v) || ($flags['sensitive']==false && mb_strtolower($var) != mb_strtolower($v)))
											$t++;
										break;
									case'%match':
										if(($flags['sensitive'] && mb_ereg($v,$var)===1) || ($flags['sensitive']==false && mb_eregi($v,$var)===1))
											$t++;
										break;
									case'%notmatch':
										if(($flags['sensitive'] && mb_ereg($v,$var)===false) || ($flags['sensitive']==false && mb_eregi($v,$var)===false))
											$t++;
										break;
									case'%in':
										if(is_array($v) && in_array($var,$v) && $flags['sensitive'])
											$t++;
										elseif(is_array($v) && $flags['sensitive']==false){
											foreach($v as $i){
												if(mb_strtolower($var)==mb_strtolower($i)){
													$t++;
													break 2;
												}
											}
										}
										break;
								}
							}
							if($t==count($value))
								$true++;
								if($glue=='%or')
									return true;
								break;
						}
					}
			}
		}
		return (($true==count($filter) && $glue='%and') || ($true>=1))?true:false;
	}
	
	/*
		Public-Functions
	*/
	public function requestState(){
		/*	returns int:
		 *   0=not initialized
		 *   1=request analyzed / request data loadet
		 *   2=step of request processing
		 *   3=...
		 *     ...
		 *  -1=request processing completed
		 */
		return $this->_requestState;
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
	public function registerRequest($id,$filter,$action,$flags=array()){
		if(is_string($id) && is_array($filter) && is_array($action) && is_array($flags)){
			if(!system::SM()->isOpen('RM.requests'))
				try{new storage('RM.requests','fileConfig',CONFIGDIRECTORY.'RM.requests'.EXT);}catch(StorageException $e){return false;}
			if(!system::SM()->isOpen('RM.requests'))
				return false;
			system::SM()->get('RM.requests')->set($filter,'filter',$id);
			system::SM()->get('RM.requests')->set($flags,'flags',$id);
			system::SM()->get('RM.requests')->set($action,'action',$id);
			return true;
		}
		return false;
	}
	public function registerAnchor($id,$filter,$action,$flags=array()){
		if(is_string($id) && is_array($filter) && is_array($action) && is_array($flags)){
			if(!system::SM()->isOpen('RM.anchors'))
				try{new storage('RM.anchors','fileConfig',CONFIGDIRECTORY.'RM.anchors'.EXT);}catch(StorageException $e){return false;}
			if(!system::SM()->isOpen('RM.anchors'))
				return false;
			system::SM()->get('RM.anchors')->set($filter,'filter',$id);
			system::SM()->get('RM.anchors')->set($flags,'flags',$id);
			system::SM()->get('RM.anchors')->set($action,'action',$id);
			return true;
		}
		return false;
	}
}
?>