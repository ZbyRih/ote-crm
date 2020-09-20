<?php

class LogSubModule extends SubModule{

	var $handlers = [
		  self::DEFAULT_VIEW => 'viewLog'
	];

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function viewLog($info){
		return true;
	}
}