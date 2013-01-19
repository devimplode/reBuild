<?php
class storageDriver{
	protected $open=false;
	protected $url=false;

	public function __construct($url=false,$open=true){
		$this->parseUrl($url);
		if($open)
			$this->open();
	}
	public function __destruct(){
		$this->close();
	}
	public function open(){
		$this->open=true;
		return true;
	}
	public function close(){
		$this->open=false;
		return true;
	}
	public function is_open(){
		return $this->open;
	}
	protected function parseUrl($url=false){
		if(!is_a($url,'link'))
			$url=new link($url);
		$this->url=$url;
		return true;
	}
}
?>