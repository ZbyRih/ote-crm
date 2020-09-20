<?php

class OBE_Session{

	static $group = 'ses';

	static function init(
		$name = null,
		$group = null)
	{
		if($name){
			session_name(hash('md5', $name));

			if(!$group){
				self::$group = $name;
			}
		}

		if($group){
			self::$group = $group;
		}

		if(session_status() == PHP_SESSION_NONE){
			session_start();
		}
		if(!isset($_SESSION[self::$group])){
			$_SESSION[self::$group] = [];
		}
	}

	static function destroy()
	{
		session_destroy();
	}

	static function checkHeaders()
	{
		if(headers_sent()){
			Die("Nelze nastavit session promennou, hlavicky byly jiz odeslany.");
		}
	}

	static function read(
		$var)
	{
		if(isset($_SESSION[self::$group][$var])){
			return $_SESSION[self::$group][$var];
		}
		return false;
	}

	static function readSerialized(
		$var)
	{
		if(isset($_SESSION[self::$group][$var])){
			return unserialize($_SESSION[self::$group][$var]);
		}
		return false;
	}

	static function exists(
		$var)
	{
		return isset($_SESSION[self::$group][$var]);
	}

	static function write(
		$var,
		$val)
	{
		$_SESSION[self::$group][$var] = $val;
		return $val;
	}

	static function writeSerialized(
		$var,
		$val)
	{
		$_SESSION[self::$group][$var] = serialize($val);
		return $val;
	}

	static function delete(
		$var)
	{
		unset($_SESSION[self::$group][$var]);
	}

	static function getID()
	{
		return session_id();
	}

	static function setID(
		$id)
	{
		return session_id($id);
	}

	static function reset()
	{
		session_destroy();
	}

	static function genNexId()
	{
		session_regenerate_id();
		return self::getID();
	}

	static function initGroup(
		$data)
	{
		/* dynamic vars prepsat uz zpracovanejma */
		$_SESSION[self::$group] = unserialize($data);
		return $_SESSION[self::$group];
	}

	static function getGroup()
	{
		return serialize($_SESSION[self::$group]);
	}

	static function drop()
	{
		self::destroy();
		OBE_Cookie::delete('PHPSESSID');
	}

	static function trace()
	{
		OBE_Trace::dump($_SESSION[self::$group]);
	}
}