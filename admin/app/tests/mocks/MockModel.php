<?php


function outSave($s){
	$f = reset($s);
	if(is_numeric(key($s))){
		foreach($s as $sub){
			foreach($sub as $k => $vs){
				OBE_Cli::writeArr($k, $vs);
			}
		}
	}else if(is_array($f)){
		foreach($s as $k => $sub){
			OBE_Cli::writeArr($k, $sub);
		}
	}else{
		OBE_Cli::writeArr(null, $s);
	}
}

class MockModel{

	static $loader;

	public static function addLoader($model, $loader){

		self::$loader[$model] = $loader;

		runkit_method_remove($model, 'FindAll');

		runkit_method_copy($model, 'FindAll', 'MockModel');
	}

	public function FindAll(){
		return call_user_func_array(MockModel::$loader[get_class($this)], func_get_args());
	}

	public function Save($s){
		OBE_Cli::writeLn(get_class($this) . '::Save');
		outSave($s);
	}
}