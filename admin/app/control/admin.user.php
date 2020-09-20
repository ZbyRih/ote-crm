<?php
use App\Models\Tables\RoleTable;

class AdminUserClass{

	private static $bLoadedSessionDataForSession = false;

	/**
	 * @var MUser
	 */
	static $userObj;

	static $userId = NULL;

	static $logedUser;

	static $session;

	static $sessionDBId = NULL;

	public static function init(
		$user,
		$session)
	{
		$user['rights'] = RoleTable::permsUnpack($user['perms']);
		self::$logedUser['User'] = $user;
		self::$userId = $user['id'];
	}

	public static function save()
	{
	}

	static function checkLogon()
	{
		return true;
	}

	static function configUser(
		$modelData)
	{
	}

	static function clearUser()
	{
		self::$logedUser = NULL;
		self::$userId = NULL;
	}

	static function loadSessionFromDB()
	{
	}

	static function saveSessionToDB(
		$logOut = false)
	{
	}

	static function isLogged()
	{
		if(self::$userId){
			return true;
		}
		return false;
	}

	static function logOut()
	{
	}

	static function isUserInSession()
	{
		return OBE_Session::read('loged_userid');
	}

	static function isDelegate()
	{
		return (bool) (self::isSuperUser() || self::$logedUser['User']['delegate']);
	}

	static function isOnlyOwn()
	{
		return (bool) (!self::isSuperUser() && self::$logedUser['User']['onlyown']);
	}

	static function isChangeOwner()
	{
		return (bool) (self::isSuperUser() || self::$logedUser['User']['chowner']);
	}

	static function is(
		$x)
	{
		return (bool) (self::isSuperUser() || (int) self::$logedUser['User'][$x]);
	}

	static function isSuperUser()
	{
		return (bool) self::$logedUser['User']['superuser'];
	}

	static function getSession(
		$key)
	{
	}

	static function setSession(
		$key,
		$val)
	{
	}

	static function getViewUser()
	{
		if(self::$logedUser){
// 			$info = new MInfo();
// 			$msgsNew = $info->getNew(self::$userId);
// 			$msgsNewCount = $info->getNewCount(self::$userId);
			return [
				'USER' => self::$logedUser['User'],
				'userLogged' => self::isLogged(),
				'last_user_conlision' => self::checkColision()
// 				'user_messages' => [], // $msgsNew,
// 				'user_messages_now' => 0 // $msgsNewCount
			];
		}
		return [
			'USER' => NULL
		];
	}

	static function checkColision()
	{
		if(AdminApp::$lastLoc){
			$Users = new MUser();
			if($u = $Users->FindOne([
				'lastloc' => AdminApp::$lastLoc,
				'!id != ' . self::$userId
			], [], [
				'activity' => 'DESC'
			])){
				$diff = time() - strtotime($u['User']['activity']);
				if($diff < (5 * 60)){
					return 'Vstoupili jste do záznamu v němž byl(a) před ' . date('i', $diff) . ' miutami a ' . date('s', $diff) . ' vteřinami ' . $u['User']['jmeno'];
				}
			}
		}
		return NULL;
	}

	static function getUserLevel()
	{
		return (int) (!(bool) self::$logedUser['User']['superuser']);
	}

	static function getRights()
	{
		return self::$logedUser['User']['rights'];
	}

	static function getSuperAccess()
	{
		$suser = new MUser();
		$data = $suser->FindOneBy('superuser', 1);
		return unserialize($data['User']['rights']);
	}

	static function getModuleAccesss(
		$module)
	{
		if($modules = self::getModulesAccesss()){
			if(isset($modules[$module])){
				return $modules[$module];
			}else{
				return NULL;
			}
		}else{
			return FormFieldRights::DELETE;
		}
	}

	static function getModulesAccesss()
	{
		if(isset(self::$logedUser['User']['rights']['modules'])){
			return self::$logedUser['User']['rights']['modules'];
		}else{
			return null;
		}
	}

	static function getFieldsAccess(
		$modul)
	{
		if(isset(self::$logedUser['User']['rights']['fields'][$modul])){
			return self::$logedUser['User']['rights']['fields'][$modul];
		}else{
			return null;
		}
	}

	static function setModulesAccess(
		$access)
	{
	}
}