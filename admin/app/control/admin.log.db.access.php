<?php

class AdminLogDBAccess{

	private static $modul;

	private static $stop = false;

	public static function setModul($modul){
		self::$modul = $modul;
	}

	public static function stop(){
		self::$stop = true;
	}

	public static function start(){
		self::$stop = false;
	}

	public static function logInsert($name, $data){
		if(!self::$stop){
			self::write('I', $name, $data);
		}
	}

	public static function logUpdate($name, $data){
		if(!self::$stop){
			self::write('U', $name, $data);
		}
	}

	public static function logDelete($name, $data){
		if(!self::$stop){
			self::write('D', $name, $data);
		}
	}

	private static function write($action, $name2, $data){
		self::$stop = true;
		$Obj = new MDBAccessLog();
		$item = [
			'DBAccessLog' => [
				'app' => 'A',
				'date' => date('Y-m-d H:i:s'),
				'user_id' => AdminUserClass::$userId,
				'action' => $action,
				'name1' => self::$modul,
				'name2' => $name2,
				'data' => serialize($data)
			]
		];
		$Obj->Save($item);
		self::$stop = false;
	}
}