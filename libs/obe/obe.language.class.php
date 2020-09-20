<?php

class OBE_Language{
	const GET_LANG_KEY = 'lang';

	public static $id;
	static $defaultLang = null;
	static $langIdsToScs = [];

	/**
	 * @var array --OBE_Array
	 */
	static $langs = [];
	static $codes = [];
	static $browsverCode = null; // realnej jazyk predanej prohlizecem

	const ACCEPT_LANGUAGE_PATTERN = '/^(?P<primarytag>[a-zA-Z]{2,8})(?:-(?P<subtag>[a-zA-Z]{2,8}))?(?:(?:;q=)(?P<quantifier>\d\.\d))?$/';

	public static function init(){
		OBE_Log::logl1('Načtení jazyků');

		self::$defaultLang = null;
		self::$codes = [];

		$where = '';
		if(!OBE_UserSession::isAdminLogedOn()){
			$where = ' WHERE l.visible = 1';
		}

		self::$langs = OBE_App::$db->FetchAssoc('SELECT l.langid, l.fraze, l.settings, l.languageshortcut, l.`default`, l.`codes`, l.languagename, l.langlabel, l.icoimage, l.visible FROM obe_languages AS l' . $where . ' ORDER BY l.position ASC', 'languageshortcut');

 		foreach(self::$langs as &$l){
			$l['fraze'] = unserialize($l['fraze']);
			$l['settings'] = unserialize($l['settings']);

			self::$langIdsToScs[$l['langid']] = $l['languageshortcut'];

			$codes = explode(',', $l['codes']);
			if(empty($codes)){
				$codes = [$l['languageshortcut']];
			}

			foreach($codes as $code){
				self::$codes[$code] = $l['langid'];
			}

			if(($l['default'] == '1' || sizeof(self::$langs) == 1 || empty($l['codes'])) && self::$defaultLang === null){
				$code = reset($codes);
				self::$browsverCode = $code;
				self::$defaultLang = $l['langid'];
			}
 		}

 		if(self::$defaultLang == null){
 			self::$defaultLang = reset(self::$langs)['langid'];
 		}

		self::detectUserBrowseLang();
		self::handleLang();
	}

	static function detectUserBrowseLang(){
		if(OBE_Http::issetServer('HTTP_ACCEPT_LANGUAGE')){

			$detLangs = [];

			foreach(explode(',', OBE_Http::getServer('HTTP_ACCEPT_LANGUAGE')) as $lang){

				$splits = [];

				if(preg_match(self::ACCEPT_LANGUAGE_PATTERN, $lang, $splits)){
					if(isset($splits['primarytag'])){
						$detLangs[] = [
							  'c' => $splits['primarytag']
							, 'w' => ((isset($splits['quantifier']))? floatval($splits['quantifier']) : 1)
						];
					}
				}
			}
			if(!empty($detLangs)){
				$quant = 0;

				foreach($detLangs as $bl){
					if(isset(self::$codes[$bl['c']])){
						if($bl['w'] > $quant){
							self::$defaultLang = self::$codes[$bl['c']];
							self::$browsverCode = $bl['c'];
							$quant = $bl['w'];
						}
					}
				}
			}
		}
	}

	public static function handleLang(){
		$shortCut = (new OBE_DynVar(self::GET_LANG_KEY, [OBE_DynVar::GET, OBE_DynVar::SES], OBE_Language::getDefault(), OBE_Language::getAvaibleShortCuts()))->get();

		self::setLangById(self::getLangIdBySC($shortCut));
	}

	public static function setLangById($id){
		self::$id = $id;
		OBE_AppCore::setDBVar('fraze', OBE_Language::getFraze(self::$id));
		OBE_AppCore::mergeDBVar('front', OBE_Language::getSettings(self::$id));
		OBE_Session::write(self::GET_LANG_KEY, self::$langIdsToScs[self::$id]);
	}

	static function getLangIdBySC($shortCut){
		return self::$langs[$shortCut]['langid'];
	}

	static function getLangIdByCode($code){
		return self::$codes[$code];
	}

	static function getFraze($langId){
		return self::$langs[self::$langIdsToScs[$langId]]['fraze'];
	}

	static function getSettings($langId){
		return self::$langs[self::$langIdsToScs[$langId]]['settings'];
	}

	static function getShortCut($langId = null){
		if($langId === null){
			return self::$langIdsToScs[self::$id];
		}
		return self::$langIdsToScs[$langId];
	}

	static function getAvaibleShortCuts(){
		return array_keys(self::$langs);
	}

	static function getIdsMapToShortCuts(){
		return array_flip(self::$langIdsToScs);
	}

	static function GetLangs(){
		return self::$langs;
	}

	static function GetNameToShortCut(){
		return MArray::MapValToKey(self::$langs, 'languageshortcut', 'languagename');
	}

	static function getDefault(){
		return self::$langIdsToScs[self::$defaultLang];
	}

	static function getLngCodes($langId){
		$vals = array_values(self::$codes);
		$keys = array_keys(self::$codes);
		$codes = [];

		foreach($vals as $index => $val){
			if($val == $langId){
				$codes[] = $keys[$index];
			}
		}

		return $codes;
	}

	static function getCFraze($key){
		return self::$langs[self::$langIdsToScs[self::$id]]['fraze'][$key];
	}
}