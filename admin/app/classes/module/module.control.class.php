<?php


class ModuleControlClass{

	/**
	 *
	 * @var CallBackControlClass
	 */
	public $callBacks = NULL;

	/**
	 *
	 * @var ModuleInfoClass
	 */
	public $info = NULL;

	public function __construct($info, $context, $callbacks){
		$this->info = $info;
		$this->callBacks = new CallBackControlClass($context, $callbacks);
		$this->info->control = $this;
	}

	/**
	 *
	 * @param boolean $debug
	 */
	public function handle($debug = false){
		$view = $this->info->scope->view;
		if(!$view){
			$view = $this->callBacks->getFirstKey();
		}
		if($debug){
			OBE_Trace::dump($this->info->scope->module, $view, $this->callBacks);
		}
		OBE_Log::log(' -- module callback - ' . $this->info->name . ':' . $view);
		return $this->callBacks->runCallBack($view, $this->info);
	}

	public function isDefaultView(){
		if($this->info->scope->view == $this->callBacks->getFirstKey()){
			return true;
		}
		return false;
	}
}