<?php
function fileWrite($args=false){
	if(!is_array($args))
		return false;
	list($filename,$data,$flags)=$args;
	
	$file = new fileRawHandler($filename,(($flags & FILE_APPEND)?'ab':'wb'),(($flags & FILE_USE_INCLUDE_PATH)?true:false),false);
	if($flags & LOCK_EX)$file->lock(LOCK_EX);
	$file->set_file_buffer(0);
	
	$return=$file->write($data);
	$file->close();
	
	return $return;
	//return file_put_contents($filename, utf8_encode($data),$flags);
}
?>