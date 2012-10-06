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
	public function __construct(){
		$this->call('EM.construct',$this);
	}
	public function __call($eventName,$args){
		$this->call($eventName,$args);
	}
	public function call($eventName,$args=false){
		//execute Event $eventName
		//load event-config
		$events=system::C()->get('EM.events')->get($eventName);
		if($events)
			foreach($events as $event)
				$event($args);
	}
	public function attach($eventName,$handlerName,$funct){
		if(is_string($eventName) && is_string($handlerName) && ($funct instanceOf Closure || is_a($funct,'reClosure'))){
			if(system::C()->get('EM.events')->get($eventName)->set($handlerName,$funct))
				return true;
		}
		return false;
	}
	public function detach($eventName,$handlerName){
		if(is_string($eventName) && is_string($handlerName)){
			return(system::C()->get('EM.events')->get($eventName)->del($handlerName));
		}
		return false;
	}
	public function detachAll($eventName){
		if(is_string($eventName)){
			return(system::C()->get('EM.events')->del($eventName));
		}
		return false;
	}
}
?>