<?php
class logManager extends defaultClass{
	//constants
	const LOG_VERBOSE= 2;
	const LOG_DEBUG=3;
	const LOG_INFO=4;
	const LOG_WARN=5;
	const LOG_ERROR=6;
	const LOG_FATAL=7;
	//LogLevel
	private $logLevel=self::LOG_INFO;
	
	
	
	//methods
	
	public function __destruct(){
		$this->live=false;
		$this->write();
	}
	
	//wite the log entrys
	private function write(){
		if($this->live || (count($this->db)==0))
			return;
		// collecting
		$res=$this->db;
		$this->db=NULL;
		$this->db=array();
		$data=array();
		foreach($res as $i=>$a){
			$data[]=$a['t']."\t".$a['l']."(".self::levelToString($a['l']).")\t".$a['c']."\t".$a['tag'].":\t".serialize($a['msg']);
			//9765986709.6789	5(WARN)	300	<storage>:	MSG
		}
		$logFile=(system::C()->system->syslog->Filename)?system::C()->system->syslog->Filename:LOGDIRECTORY.DS.'syslog.log';
		if(!file_exists($logFile)){
			if(file_exists(LOGDIRECTORY.DS.$logFile)){
				$logFile=LOGDIRECTORY.DS.$logFile;
			}
			elseif(file_exists(RD.DS.$logFile)){
				$logFile=RD.DS.$logFile;
			}
		}
		mod::fileWrite($logFile,implode(EOL,$data).EOL,FILE_APPEND);
	}
	//logentry on verbose level
	public function v($tag,$message="",$errorCode=0){
		if($this->logLevel<=self::LOG_VERBOSE && is_string($tag))
			$this->db[]=array('t'=>microtime(true),'l'=>self::LOG_VERBOSE,'tag'=>$tag,'msg'=>$message,'c'=>$errorCode);
		$this->write();
	}
	//logentry on debug level
	public function d($tag,$message="",$errorCode=0){
		if($this->logLevel<=self::LOG_DEBUG && is_string($tag))
			$this->db[]=array('t'=>microtime(true),'l'=>self::LOG_DEBUG,'tag'=>$tag,'msg'=>$message,'c'=>$errorCode);
		$this->write();
	}
	//logentry on info level
	public function i($tag,$message="",$errorCode=0){
		if($this->logLevel<=self::LOG_INFO && is_string($tag))
			$this->db[]=array('t'=>microtime(true),'l'=>self::LOG_INFO,'tag'=>$tag,'msg'=>$message,'c'=>$errorCode);
		$this->write();
	}
	//logentry on warn level
	public function w($tag,$message="",$errorCode=0){
		if($this->logLevel<=self::LOG_WARN && is_string($tag))
			$this->db[]=array('t'=>microtime(true),'l'=>self::LOG_WARN,'tag'=>$tag,'msg'=>$message,'c'=>$errorCode);
		$this->write();
	}
	//logentry on error level
	public function e($tag,$message="",$errorCode=0){
		if($this->logLevel<=self::LOG_ERROR && is_string($tag))
			$this->db[]=array('t'=>microtime(true),'l'=>self::LOG_ERROR,'tag'=>$tag,'msg'=>$message,'c'=>$errorCode);
		$this->write();
	}
	//What a Terrible Failure: Report an exception that should never happen.
	public function wtf($tag="undifined",$message="",$errorCode=0,$e=NULL){
		if(!is_object($e)){
			$this->db[]=array('t'=>microtime(true),'l'=>self::LOG_FATAL,'tag'=>$tag,'msg'=>$message,'c'=>$errorCode);
		}
		else{
			$this->db[]=array('t'=>microtime(true),'l'=>self::LOG_FATAL,'tag'=>$tag,'msg'=>$message.";<Exception><Message>".$e->getMessage()."</Message><File>".$e->getFile()."</File><Line>".$e->getLine()."</Line><Trace>".$e->getTraceAsString()."</Trace></Exception>",'c'=>$errorCode);
		}
		$this->write();
	}
	
	public function levelToString($level){
		if($level>=self::LOG_VERBOSE && $level<=self::LOG_FATAL){
			switch($level){
				case self::LOG_VERBOSE:
					return'VERBOSE';
					break;
				case self::LOG_DEBUG:
					return'DEBUG';
					break;
				case self::LOG_INFO:
					return'INFO';
					break;
				case self::LOG_WARN:
					return'WARN';
					break;
				case self::LOG_ERROR:
					return'ERROR';
					break;
				default:
					return'FATAL';}
		}
		return false;
	}
}
?>