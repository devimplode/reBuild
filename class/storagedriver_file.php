<?php
class storageDriver_file extends storageDriver{
	protected $fp=NULL;

	public function __construct($file=false,$open=true){
		$this->parseUrl($file);
		if(preg_match('~^\w:[/\\\].+$~',$this->url->path))// iz dis windows? - this should in a other link class
			$this->url->path=str_replace('/','\\',$this->url->path);
		if($open)
			if($this->open() == false)
				throw new StorageException("Can't open file '".$file."'");
	}
	
	public function open(){
		if($this->is_open())
			return $this;
		if($this->url->getOption('mode') !== false)
			$this->url->setOption('mode','rb+');
		if($this->url->getOption('includePath') !== false)
			$this->url->setOption('includePath',false);
		system::LOG()->v('storage.driver.file',"open file '".$this->url->path."' with mode '".$this->url->getOption('mode')."'");
		try{
			if($this->url->getOption('context') !== false && is_array($this->url->getOption('context')) && count($this->url->getOption('context'))){
				$this->fp = fopen($this->url->path, $this->url->getOption('mode'), $this->url->getOption('includePath'), stream_context_create($this->url->getOption('context')));
			}
			else{
				$this->fp=fopen($this->url->path, $this->url->getOption('mode'), $this->url->getOption('includePath'));
			}
		}
		catch(Exception $e){
			system::LOG()->e('storage.driver.file',"Unexpectet failure while fopen('".$this->url->path."'...)");
			throw new StorageException("Unexpectet failure while fopen('".$this->url->path."'...)");
		}
		if($this->fp===false){
			system::LOG()->e('storage.driver.file',"Can't fopen('".$this->url->path."','".$this->url->getOption('mode')."'...)");
			throw new StorageException("Can't fopen('".$this->url->toString()."','".$this->url->getOption('mode')."'...)");
		}
		$this->open=true;
		return true;
	}
	public function close(){
		try{
			@fclose($this->fp);
			$this->fp=NULL;
			$this->open=false;
			return true;
		}
		catch(Exception $e){
			system::LOG()->w('storage.driver.file',"Can't fclose('".$this->filename."')");
			return false;
		}
	}
	public function is_open(){
		return ($this->open && $this->fp);
	}

	public function is_eof(){
		return ($this->is_open())?feof($this->fp):NULL;
	}
	public function flush(){
		return ($this->is_open())?fflush($this->fp):NULL;
	}
	public function getc(){
		return ($this->is_open())?fgetc($this->fp):NULL;
	}
	public function getcsv($length=0,$delimiter=',',$enclosure='"',$escape='\\'){
		return ($this->is_open())?fgetcsv($length=0,$delimiter=',',$enclosure='"',$escape='\\'):NULL;
	}
	public function gets($length=FALSE){
		return ($this->is_open())?(($length!==FALSE)?fgets($this->fp,$length):fgets($this->fp)):NULL;
	}
	public function getss($length=NULL,$allowable_tags=NULL){
		return ($this->is_open())?(($length!==NULL)?(($allowable_tags!==NULL)?fgetss($this->fp,$length,$allowable_tags):fgetss($this->fp,$length)):fgetss($this->fp)):NULL;
	}
	public function lock($mode){
		return (($mode==LOCK_SH)||($mode==LOCK_EX)||($mode==LOCK_UN))?flock($this->fp,$mode):NULL;
	}
	public function passthru(){
		return ($this->is_open())?fpassthru($this->fp):NULL;
	}
	public function puts($data,$length=false){
		return $this->write($data,$length);
	}
	public function write($data,$length=false){
		return (is_string($data))?(($this->is_open())?(($length!==FALSE)?fwrite($this->fp,$data,$length):fwrite($this->fp,$data)):NULL):FALSE;
	}
	public function read($length=0){
		return ($this->is_open())?fread($this->fp,$length):NULL;
	}
	public function seek($offset=0,$whence=SEEK_SET){
		return ($this->is_open())?fseek($this->fp,$offset,$whence):NULL;
	}
	public function tell(){
		return ($this->is_open())?ftell($this->fp):NULL;
	}
	public function stat(){
		return ($this->is_open())?fstat($this->fp):NULL;
	}
	public function truncate($size=0){
		return ($this->is_open())?ftruncate($this->fp,$size):NULL;
	}
	public function rewind(){
		return ($this->is_open())?rewind($this->fp):NULL;
	}
	public function stream_set_write_buffer($buffer=0){
		return ($this->is_open())?stream_set_write_buffer($this->fp,$buffer):NULL;
	}
	public function set_write_buffer($buffer=0){
		return $this->stream_set_write_buffer($buffer);
	}
	public function set_file_buffer($buffer=0){
		return $this->stream_set_write_buffer($buffer);
	}
}
?>