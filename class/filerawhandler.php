<?php
class fileRawHandler{
	private $fp=NULL;
	private $open=false;
	private $mode;
	private $filename;
	private $options;
	private $includePath;
	
	public function __construct($file,$mode='wb',$includePath=false,$options=false){
		$this->filename=$file;
		$this->mode=$mode;
		$this->includePath=$includePath;
		$this->options=$options;
		if($this->open($this->filename,$this->mode,$this->includePath,$this->options) === false)
			throw new StorageException("Can't open file '".$file."'");
	}
	public function __destruct(){
		$this->close();
	}
	
	public function open($file=false,$mode='wb',$includePath=false,$options=false){
		if($this->open && $this->fp)
			return true;
		if($file!==false)
			$this->filename=$file;
		$this->mode=$mode;
		$this->includePath=$includePath;
		$this->options=$options;
		
		if(!is_string($this->filename))
			return false;
		$this->includePath = ($includePath === true)?true:false;
		
		system::LOG()->v('storage.fileRawHandler',"open file '".$file."' with mode '".$mode."'");
		try{
			if(sizeof($options) && is_array($options)){
				$this->fp = fopen($this->filename, $this->mode, $this->includePath, stream_context_create($options));
			}
			else{
				$this->fp=fopen($this->filename, $this->mode, $this->includePath);
			}
		}
		catch(Exception $e){
			system::LOG()->e('storage.fileRawHandler',"Unexpectet failure while fopen('".$file."'...)");
			throw new StorageException("Unexpectet failure while fopen('".$file."'...)");
		}
		if($this->fp===false){
			system::LOG()->e('storage.fileRawHandler',"Can't fopen('".$file."','".$mode."'...)");
			throw new StorageException("Can't fopen('".$file."','".$mode."'...)");
		}
		$this->open=true;
		return true;
	}
	public function is_open(){
		return $this->open;
	}
	
	public function is_eof(){
		return ($this->open)?feof($this->fp):NULL;
	}
	public function flush(){
		return ($this->open)?fflush($this->fp):NULL;
	}
	public function getc(){
		return ($this->open)?fgetc($this->fp):NULL;
	}
	public function getcsv($length=0,$delimiter=',',$enclosure='"',$escape='\\'){
		return ($this->open)?fgetcsv($length=0,$delimiter=',',$enclosure='"',$escape='\\'):NULL;
	}
	public function gets($length=FALSE){
		return ($this->open)?(($length!==FALSE)?fgets($this->fp,$length):fgets($this->fp)):NULL;
	}
	public function getss($length=NULL,$allowable_tags=NULL){
		return ($this->open)?(($length!==NULL)?(($allowable_tags!==NULL)?fgetss($this->fp,$length,$allowable_tags):fgetss($this->fp,$length)):fgetss($this->fp)):NULL;
	}
	public function lock($mode){
		return (($mode==LOCK_SH)||($mode==LOCK_EX)||($mode==LOCK_UN))?flock($this->fp,$mode):NULL;
	}
	public function passthru(){
		return ($this->open)?fpassthru($this->fp):NULL;
	}
	public function puts($data,$length=false){
		return $this->write($data,$length);
	}
	public function write($data,$length=false){
		return (is_string($data))?(($this->open)?(($length!==FALSE)?fwrite($this->fp,$data,$length):fwrite($this->fp,$data)):NULL):FALSE;
	}
	public function read($length=0){
		return ($this->open)?fread($this->fp,$length):NULL;
	}
	public function seek($offset=0,$whence=SEEK_SET){
		return ($this->open)?fseek($this->fp,$offset,$whence):NULL;
	}
	public function tell(){
		return ($this->open)?ftell($this->fp):NULL;
	}
	public function stat(){
		return ($this->open)?fstat($this->fp):NULL;
	}
	public function truncate($size=0){
		return ($this->open)?ftruncate($this->fp,$size):NULL;
	}
	public function rewind(){
		return ($this->open)?rewind($this->fp):NULL;
	}
	public function stream_set_write_buffer($buffer=0){
		return ($this->open)?stream_set_write_buffer($this->fp,$buffer):NULL;
	}
	public function set_write_buffer($buffer=0){
		$this->stream_set_write_buffer($buffer);
	}
	public function set_file_buffer($buffer=0){
		$this->stream_set_write_buffer($buffer);
	}
	public function close(){
		try{
			@fclose($this->fp);
			$this->fp=NULL;
			$this->open=false;
			return true;
		}
		catch(Exception $e){
			system::LOG()->w('storage.fileRawHandler',"Can't fclose('".$this->filename."')");
			return false;
		}
	}
}
?>