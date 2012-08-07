<?php
class eventManager{
	/**
	 * Usage:
	 * register functions on events:
	 * $f=function($data){echo($data['fu']);};
	 * system::EM()->attach('customEvent','customHandler',$f);
	 * 
	 * call event:
	 * system::EM()->call('customEvent',array('fu'=>'bar'));
	 * 
	 * unregister event handler:
	 * system::EM()->detach('customEvent','customHandler');
	 * 
	 * unregister all event handlers:
	 * system::EM()->detachAll('customEvent');
	 */
	public function call($eventName,$args){
		//execute Event $eventName
		//load event-config
		if(!system::SM()->isOpen('EM.events')){
			try{new storage('EM.events','fileConfig',CONFIGDIRECTORY.'EM.events'.EXT);}catch(StorageException $e){echo "OMG!".$e->getMessage();return false;}
			if(!system::SM()->isOpen('EM.events'))
				return false;
		}
		$events=system::SM()->get('EM.events')->getSection($eventName);
		if(is_array($events))
			foreach($events as $event)
				$event($args);
	}
	public function attach($eventName,$handlerName,$funct){
		if(is_string($eventName) && is_string($handlerName) && ($funct instanceOf Closure || $funct instanceOf reClosure)){
			if(!system::SM()->isOpen('EM.events')){
				try{new storage('EM.events','fileConfig',CONFIGDIRECTORY.'EM.events'.EXT);}catch(StorageException $e){return false;}
				if(!system::SM()->isOpen('EM.events'))
					return false;
			}
			if(system::SM()->get('EM.events')->set($funct,$handlerName,$eventName))
				return true;
		}
		return false;
	}
	public function detach($eventName,$handlerName){
		if(is_string($eventName) && is_string($handlerName)){
			if(!system::SM()->isOpen('EM.events')){
				try{new storage('EM.events','fileConfig',CONFIGDIRECTORY.'EM.events'.EXT);}catch(StorageException $e){return false;}
				if(!system::SM()->isOpen('EM.events'))
					return false;
			}
			if(system::SM()->get('EM.events')->removeKey($handlerName,$eventName))
				return true;
		}
		return false;
	}
	public function detachAll($eventName){
		if(is_string($eventName)){
			if(!system::SM()->isOpen('EM.events')){
				try{new storage('EM.events','fileConfig',CONFIGDIRECTORY.'EM.events'.EXT);}catch(StorageException $e){return false;}
				if(!system::SM()->isOpen('EM.events'))
					return false;
			}
			if(system::SM()->get('EM.events')->removeSection($eventName))
				return true;
		}
		return false;
	}
}
?>