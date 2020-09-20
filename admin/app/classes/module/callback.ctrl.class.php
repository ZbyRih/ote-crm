<?php


class CallBackItemClass{

	/**
	 *
	 * @var mixed
	 */
	var $callBack = NULL;

	function __construct($callBack){
		$this->callBack = $callBack;
	}

	function isCallAble(){
		return !empty($this->callBack);
	}

	function call($params, $parent = NULL){
		if(!empty($this->callBack)){
			$callback = $this->callBack;
			if(!is_array($callback) && !is_callable($callback)){
				if($parent !== NULL){
					$callback = [
						$parent,
						$callback
					];
				}else{
					$callback = [
						$this,
						$callback
					];
				}
			}
			return call_user_func_array($callback, $params);
		}
		return NULL;
	}
}

class CallBackControlClass{

	/**
	 *
	 * @var array/CallBackItemClass
	 */
	private $callbacks = [];

	private $parent = NULL;

	function __construct($parentObj = NULL, $callBacks = NULL){
		$this->parent = $parentObj;
		$this->addCallBack($callBacks);
	}

	function exist($key){
		return isset($this->callbacks[$key]);
	}

	function addCallBack($key, $callback = NULL){
		if(is_array($key)){
			foreach($key as $_key => $_callback){
				if($callback !== NULL){
					$this->callbacks[$_key] = $this->convertToObject($callback);
				}else{
					$this->callbacks[$_key] = $this->convertToObject($_callback);
				}
			}
		}else if($key){
			$this->callbacks[$key] = $this->convertToObject($callback);
		}
	}

	function getFirstKey(){
		if(!empty($this->callbacks)){
			reset($this->callbacks);
			return key($this->callbacks);
		}
		return NULL;
	}

	function convertToObject($callback){
		if(!is_object($callback) || is_callable($callback)){
			$callback = new CallBackItemClass($callback);
		}
		return $callback;
	}

	function runCallBack($callKey){
		if(isset($this->callbacks[$callKey])){
			$params = func_get_args();
			$params = MArray::AllwaysArray($params);
			array_shift($params);
			return $this->runCallBackParams($callKey, $params);
		}
		return false;
	}

	function setCallBack($key, $callBack = NULL){
		if(!is_array($key) && $callBack != NULL){
			$key = [
				$key => $callBack
			];
		}
		foreach($key as $_key => $callBack){
			if(isset($this->callbacks[$_key])){
				$this->callbacks[$_key] = $this->convertToObject($callBack);
			}
		}
	}

	function runCallBackParams($callKey, $params){
		if(isset($this->callbacks[$callKey])){
			$callback = $this->callbacks[$callKey];
			return $callback->call($params, $this->parent);
		}
		return false;
	}

	/**
	 *
	 * @param ModuleUrlScope $scope
	 * @param array $params
	 */
	function handle($scope, $params = []){
		if($scope->action){
			return $this->runCallBackParams($scope->action, $params);
		}
		return false;
	}

	/**
	 *
	 * @param String $getKey - klic v url
	 * @param Mixed $params - parametry predavane callback fci
	 * @return Mixed/False
	 */
	function catchByGet($getKey, $params = []){
		if(OBE_Http::issetGet($getKey)){
			return $this->runCallBackParams(OBE_Http::getGet($getKey), $params);
		}
		return false;
	}

	function isCallable($callKey){
		return (bool) (isset($this->callbacks[$callKey]) && $this->callbacks[$callKey]->isCallAble());
	}

	function trace(){
		OBE_Trace::callPoint('CallBackControlClass::trace()');
		OBE_Trace::dump(array_keys($this->callbacks));
	}
}