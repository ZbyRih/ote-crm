<?php

class OBE_CounterClass{
	static $self = null;

	function __construct(){
		if(is_null(self::$self)){
			self::$self = $this;
		}else{
			return self::$self;
		}
	}

	function getCurrent($name){
		if($counter = OBE_App::$db->FetchSingleArray(
			'SELECT c.lastnum, DATE_FORMAT(c.lastupdate, \'%Y\') AS lastupdate FROM es_counters AS c WHERE c.counterid=\'' . $name . '\''
		)){
			return $counter['lastnum'];
		}
		throw new OBE_Exception('Counter pro ' . $name . ' nebyl nalezen');
	}

	function getNextCounterVal($name, $year = null){
		if($counter = OBE_App::$db->FetchSingleArray(
			'SELECT c.lastnum, DATE_FORMAT(c.lastupdate, \'%Y\') AS lastupdate FROM es_counters AS c WHERE c.counterid=\'' . $name . '\''
		)){

			if($year && $year != $counter['lastupdate']){
				$counter['lastnum'] = 0;
			}

			return ++$counter['lastnum'];
		}
		throw new OBE_Exception('Counter pro ' . $name . ' nebyl nalezen');
	}

	function updateCounterValue($name, $value){
		$curr = $this->getCurrent($name);
		if($value > $curr){
			OBE_App::$db->Update(
				  'es_counters'
				, ['lastnum' => $value, 'lastupdate = NOW()']
				, 'counterid = \'' . $name . '\''
			);
		}
	}
}