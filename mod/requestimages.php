<?php
function requestImages($args=false){
	$anchor=system::RM()->request('anchor');
	$path=system::RM()->request('path');
	$file=false;
	if(file_exists(SD.DS.'public'.DS.preg_replace('~[/\\\]~u',DS,$path).DS.$anchor))
		$file=SD.DS.'public'.DS.preg_replace('~[/\\\]~u',DS,$path).DS.$anchor;
	elseif(file_exists(SD.DS.'public'.DS.$anchor))
		$file=SD.DS.'public'.DS.$anchor;
	if($file!=false){
		header('Pragma: public');
		header('Content-type: '.mod::getFileContentType($file));
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.filesize($file));
		readfile($file);
		return true;
	}
}