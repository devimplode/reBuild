<?php
class link{
	protected $_url;//only set and updated by __construct(string $url)
	protected $_parts=array(
		'scheme'=>false,
		'user'=>false,
		'pass'=>false,
		'host'=>false,
		'port'=>false,
		'path'=>false,
		'query'=>false,
		'fragment'=>false
	);
	protected $_options=array();

	public function __construct($url=false){
		if($url===false)
			return;
		if(is_string($url))
			$this->url=$url;
		if(!$this->set($this->parseUrl($url)))
			throw new Exception("Coudn't handle url!");
	}
	protected function parseUrl($url=false,$keep=false){
		//check input
		//$url is required
		if($url===false)
			return(false);
		//$keep is optional
		if($keep===false)
			$keep=array_keys($this->_parts);
		//$data to return
		$data=array();

		//data analyzing
		if(is_string($url)){
			//parsing given url string
			//checks if given string is valid and put content parts into $matches
			$matches=array();
			if(preg_match('~^(?:(?:(?P<scheme>[^:]*):)?(?://))?(?:(?P<user>[^:@]*)?(?::(?P<pass>[^@]*))?@)?(?:(?P<host>[^/?:#]*)(?::(?P<port>\\d*))?)?(?:/(?P<path>[^?#]*))?(?:\\?(?P<query>[^#]*))?(?:#(?P<fragment>.*))?~u', $url, $matches)){
				foreach($matches as $key=>$value)
					//filter result
					if(in_array($key,$keep,true) && !empty($value))
						$data[$key]=$value;
				return($data);
			}
			return(false);
		}
		elseif(is_object($url) && is_a($url,'link') && method_exists($url,'get')){
			//if $url is a other link object
			$data=array();
			foreach($keep as $key){
				$data[$key]=$url->get($key);
			}
			return($data);
		}
		return(false);
	}
	public function toString(){
		//build url from parts
		if(
			( isset($this->_parts['scheme']) && ($this->_parts['scheme'] !== false) ) ||
			( isset($this->_parts['user']) && ($this->_parts['user'] !== false) ) ||
			( isset($this->_parts['pass']) && ($this->_parts['pass'] !== false) ) ||
			( isset($this->_parts['host']) && ($this->_parts['host'] !== false) ) ||
			( isset($this->_parts['port']) && ($this->_parts['port'] !== false) ) ||
			( isset($this->_parts['path']) && ($this->_parts['path'] !== false) ) ||
			( isset($this->_parts['query']) && ($this->_parts['query'] !== false) ) ||
			( isset($this->_parts['fragment']) && ($this->_parts['fragment'] !== false) )
		){
		//parts to string!!!
			$url="";
			if(isset($this->_parts['scheme']) && ($this->_parts['scheme'] !== false))
				$url=$this->_parts['scheme'].'://';
			$user=false;
			if(isset($this->_parts['user']) && ($this->_parts['user'] !== false))
				$user=$this->_parts['user'];
			if(isset($this->_parts['pass']) && ($this->_parts['pass'] !== false)){
				if($user===false)
					$user=':'.$this->_parts['pass'];
				else
					$user.=':'.$this->_parts['pass'];
			}
			if($user!==false)
				$url.=$user.'@';
			if(isset($this->_parts['host']) && ($this->_parts['host'] !== false))
				$url.=$this->_parts['host'];
			if(isset($this->_parts['port']) && ($this->_parts['port'] !== false))
				$url.=':'.$this->_parts['port'];
			if(isset($this->_parts['path']) && ($this->_parts['path'] !== false))
				$url.='/'.$this->_parts['path'];
			else
				$url.='/';
			if(isset($this->_parts['query']) && ($this->_parts['query'] !== false))
				$url.='?'.$this->_parts['query'];
			if(isset($this->_parts['fragment']) && ($this->_parts['fragment'] !== false))
				$url.='#'.$this->_parts['fragment'];
			return($url);
		}
		//use plain old one - not safe
		if(isset($this->_url) && !empty($this->_url))
			return($this->_url);
		return(false);
	}
	/*
		$target=$link->get('host');
		list($target,$port)=$link->get(array('host','port'));
	*/
	public function get($var){
		if(is_string($var) && !empty($var) && isset($this->_parts[$var])){
			return($this->_parts[$var]);
		}
		elseif(is_array($var) && !empty($var)){
			$data=array();
			foreach($var as $key){
				$data[$key]=$this->get($key);
			}
			return($data);
		}
		return(false);
	}
	/*
		$link->set('host','localhost');
		$link->set(array('host'=>'localhost','port'=>443));
	*/
	public function set($var,$val=false){
		if(is_array($var)){
			foreach($var as $key=>$val){
				$this->set($key,$val);
			}
			return(true);
		}
		elseif(is_string($var) && !empty($var) && isset($this->_parts[$var]) && ( is_string($val) || is_numeric($val) )){
			$this->_parts[$var]=$val;
			if($var==='query' && is_string($val)){
				//overwrite/remove the old options
				$this->_options=array();
				$options=preg_split('/[;&]/',$val);
				foreach($options as $match){
					$option=explode('=',$match);
					$this->_options[$option[0]]=(isset($option[1]))?$option[1]:true;
				}
			}
			return(true);
		}
		return(false);
	}
	/*
		$link->setOption('chpwd',true);
		$link->setOption(array('param'=>'localhost','id'=>123));
	*/
	public function setOption($var,$val=false){
		if(is_array($var)){
			foreach($var as $key=>$val){
				$this->set($key,$val);
			}
			return(true);
		}
		elseif(is_string($var) && !empty($var) && ( is_string($val) || is_numeric($val) )){
			$this->_options[$var]=$val;
			//update query string
			$query=array();
			foreach($this->_options as $k=>$v){
				$query[]=$k.($v===true)?'':'='.$v;
			}
			$this->_parts['query']=implode('&',$query);
			return(true);
		}
		return(false);
	}
	/*
		$chpwd=$link->getOption('chpwd');
		$options=$link->getOption();
		$options=$link->getOption(array('chpwd','param'));
	*/
	public function getOption($var=false){
		if($var===false){
			return($this->_options);
		}
		elseif(is_array($var) && !empty($var)){
			$data=array();
			foreach($var as $key){
				$data[$key]=$this->getOption($key);
			}
			return($data);
		}
		elseif(is_string($var) && !empty($var) && isset($this->_options[$var])){
			return($this->_options[$var]);
		}
		return(false);
	}
	/*
		$link->remove('pass');
		$link->remove(array('user','pass'));
	*/
	public function remove($var){
		if(is_array($var)){
			foreach($var as $part)
				$this->remove($part);
			return(true);
		}
		elseif(is_string($var) && !empty($var) && isset($this->_parts[$var])){
			$this->_parts[$var]=false;
			return(true);
		}
		return(false);
	}
	/*
		$target=$link->host;
	*/
	public function __get($var){
		return($this->get($var));
	}
	/*
		$link->host='localhost';
	*/
	public function __set($var,$val){
		return($this->set($var,$val));
	}
	/*
		unset($link->pass);
	*/
	public function __unset($var){
		return($this->remove($var));
	}
	public function __toString(){
		return($this->toString());
	}
}
?>