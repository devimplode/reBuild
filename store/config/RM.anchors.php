; <?php die(); ?>

[images]
filter = "a:1:{s:11:\"anchor_type\";a:1:{s:6:\"%match\";s:17:\"^(png|jpe?g|gif)$\";}}"
action = "a:1:{s:3:\"run\";O:9:\"reClosure\":2:{s:7:\" * code\";s:382:\"function(){
	$anchor=system::RM()->request('anchor');
	if(file_exists(SD.DS.'public'.DS.$anchor)){
		header(\"Pragma: public\");
		header('Content-type: '.mod::getFileContentType(SD.DS.'public'.DS.$anchor));
		header(\"Content-Transfer-Encoding: binary\");
		header(\"Content-Length: \".filesize(SD.DS.'public'.DS.$anchor));
		readfile(SD.DS.'public'.DS.$anchor);
		return true;}}\";s:17:\" * used_variables\";a:0:{}}}"
flags = "a:1:{s:4:\"last\";b:1;}"
		
[php]
filter = "a:1:{s:11:\"anchor_type\";s:3:\"php\";}"
flags = "a:2:{s:9:\"sensitive\";b:0;s:4:\"last\";b:1;}"
action = "a:1:{s:3:\"run\";O:9:\"reClosure\":2:{s:7:\" * code\";s:38:\"function(){system::RM()->loadPorts();}\";s:17:\" * used_variables\";a:0:{}}}"
