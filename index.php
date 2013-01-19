<?php
define('startTime',microtime(true));
define('DEBUG',false);
define('DS',(defined('DIRECTORY_SEPARATOR'))?DIRECTORY_SEPARATOR:'/'); //fallback if php-directory-module isn't implemented
define('RD',dirname(__FILE__)); //Root-Directory
define('CD',RD.DS.'class'); //CLASS-Directory
define('MD',RD.DS.'mod'); //MOD-Directory
define('SD',RD.DS.'store'); //STORAGE-Directory
define('LOGDIRECTORY',SD.DS.'log'); //LOG-Directory
define('CONFIGDIRECTORY',SD.DS."config"); //CONFIG-Directory
define('EXT','.php');
define('EOL',PHP_EOL);

require_once(CD.DS.'system'.EXT);
new system(); //initiate default-system for ReBuild-Framework
system::EM()->onSystemReady();

define('endTime',microtime(true));
if(DEBUG){
	print(EOL."ScriptExecutionDuration=".((float)endTime-(float)startTime).EOL);
	print_r($_GET);
	print_r($_SERVER);
}
?>