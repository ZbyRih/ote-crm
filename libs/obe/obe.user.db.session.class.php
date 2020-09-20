<?php


class OBE_UserDBSession{

	const klientTable = 'obe_klients';

	const adminTable = 'obe_users';

	static $sysUserId = NULL;

	static $_bLogedAsAdmin = false;
	static $_bShowAsAdmin = false;
	static $_bLogedAsRoot = false;

	public static function getSession($cookieId){
		list($userAgent, $remoteArddr) = self::sanitizeParams();

		$data = OBE_App::$db->FetchSingleArray(
			'
				SELECT su.klientid, su.session
				FROM ' . self::klientTable . ' AS su
				WHERE su.agent = \'' . $userAgent . '\'
				AND su.addr = \'' . $remoteArddr . '\'
				AND su.cookieid = \'' . OBE_DB::escape_string($cookieId) . '\'');
		self::$sysUserId = $data['klientid'];
		return $data['session'];
	}

	public static function addSession($cookieId, $data, $bot){
		list($userAgent, $remoteArddr) = self::sanitizeParams();
		if(!OBE_Core::$cli){
			OBE_App::$db->Insert(
				self::klientTable,
				[
					'agent' => $userAgent, 'addr' => $remoteArddr, 'cookieid' => $cookieId /* OBE_Session::getID() */
				, 'session' => $data /* OBE_Session::getGroup() */
				, 'last_access' => 'NOW()', 'bot' => $bot
				]);
			self::$sysUserId = OBE_App::$db->getLastInsertId();
		}
	}

	public static function updateSession($userId, $cookieId, $data, $bot){
		if(!OBE_Core::$cli){
			OBE_App::$db->Update(
				self::klientTable,
				[
					'cookieid' => $cookieId /* OBE_Session::getID() */
				, 'session' => $data /* OBE_Session::getGroup() */
				, 'last_access' => 'NOW()', 'bot' => $bot
				], 'klientid = ' . $userId);
		}
	}

	public static function detectAdminSession($cookieId){
		if($data = OBE_App::$db->FetchSingleArray(
			'SELECT userid, session  FROM ' . self::adminTable . ' WHERE cookieid = \'' . OBE_DB::escape_string($cookieId) . '\'')){
			$data = unserialize($data['session']);
			if(isset($data['loged_userid'])){
				self::$_bLogedAsAdmin = true;
				if(isset($data['root'])){
					self::$_bLogedAsRoot = true;
				}
			}
			self::$_bShowAsAdmin = true;
			return true;
		}
		return false;
	}

	public static function setAdminUser($isLoged, $isRoot, $showAsAdmin){
		self::$_bLogedAsAdmin = $isLoged;
		self::$_bLogedAsRoot = $isRoot;
		self::$_bShowAsAdmin = $showAsAdmin;
	}

	public static function isAdminLogedOn(){
		return self::$_bLogedAsAdmin;
	}

	public static function deleteOldSessions(){
		if(OBE_AppCore::getAppConf('users_lifetime') !== NULL){
			OBE_App::$db->query(
				'DELETE FROM ' . self::klientTable . ' WHERE last_access < (NOW() - INTERVAL ' . OBE_AppCore::getAppConf('users_lifetime') . ' MONTH )');
		}
	}

	public static function sanitizeParams(){
		$userAgent = OBE_DB::escape_string(substr(OBE_Http::getServer('HTTP_USER_AGENT', true), 0, 255));
		$remoteArddr = OBE_DB::escape_string(substr(OBE_Http::getServer('REMOTE_ADDR', true), 0, 24));
		return [
			$userAgent, $remoteArddr
		];
	}
}