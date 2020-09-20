<?php


/**
 * trida si pamatuje aktivni uzivatele a jejich session_data pod session_id, ktee predava do cookies,
 * po vyprseni session_id vygeneruje nove, diky cookies se ale navaznost neztrati

 */

class OBE_UserSession extends OBE_UserDBSession{

	const cookieKey = 'session_id';

	const sesCookie = 'cookie_set';

	const sesUserId = 'sysUserId';

	private static $cookieID = NULL;

	private static $isBot = false;

	var $bLogedAsAdmin = false;

	var $bShowAsAdmin = false;

	var $bLogedAsRoot = false;

	function __construct(){
		if(parent::$sysUserId !== NULL){
			$this->bShowAsAdmin = self::$_bShowAsAdmin;
			$this->bLogedAsAdmin = self::$_bLogedAsAdmin;
			$this->bLogedAsRoot = self::$_bLogedAsRoot;
		}
	}

	function __destruct(){
		if(!self::$isBot && OBE_App::$db){
			$this->Save();
		}
	}

	static function Init(){
		OBE_Log::logl1('Načtení session uživatele');

		self::detectBot();
		self::deleteOldSessions();

		if(!self::$isBot){
			if(OBE_Cookie::exists(self::cookieKey) || OBE_Session::exists(self::sesCookie)){
				if(OBE_Cookie::exists(self::cookieKey)){
					self::$cookieID = OBE_Cookie::read(self::cookieKey);
					OBE_Session::setID(self::$cookieID);
				}else{
					self::$cookieID = OBE_Session::getID();
				}
				self::Load(self::$cookieID);
			}
			OBE_Session::write(self::sesCookie, true);
			OBE_Cookie::writePerm(self::cookieKey, OBE_Session::getID());
			parent::detectAdminSession(self::$cookieID);
		}
	}

	private static function Load($cookieID){
		if(OBE_Http::issetServer('HTTP_USER_AGENT') && !self::$isBot){
			if($data = parent::getSession($cookieID)){
				OBE_Session::initGroup($data);
			}
		}
	}

	private static function detectBot(){
		if(!empty($_SERVER['REQUEST_URI']) && !empty($_SERVER['HTTP_USER_AGENT'])){
			$agent_test = $_SERVER['HTTP_USER_AGENT'];

			if(preg_match('/.*(@|ia_archiver|search|crawl|bot|spider|jeeves|coccoc|link|finder|facebook.*).*/i', $agent_test)){
				self::$isBot = true;
			}
			OBE_Log::logl2('isBot: ' . (int) self::$isBot);
		}
	}

	public static function setToBot(){
		self::$isBot = true;
	}

	function Save(){
		if(parent::$sysUserId === NULL){
			if(OBE_Http::issetServer('HTTP_USER_AGENT') && !self::$isBot){
				parent::addSession(OBE_Session::getID(), OBE_Session::getGroup(), self::$isBot);
			}
		}else{
			parent::updateSession(parent::$sysUserId, OBE_Session::getID(), OBE_Session::getGroup(), self::$isBot);
		}
	}
}