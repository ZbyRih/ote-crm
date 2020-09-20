<?php

class AdminLogActivity{

	/**
	 *
	 * @param integer $modul
	 * @param string $aktivita
	 * @param string $popis
	 * @param string $master
	 * @param integer $recid
	 * @param string $type = [info, danger, warning, success]
	 */
	public static function log($modul, $aktivita, $popis, $master = null, $recId = null, $type = null){
		AdminLogDBAccess::stop();

		$Obj = new MActivityLog();
		$item = [
			'ActivityLog' => [
				'user_id' => AdminUserClass::$userId,
				'kdy' => date('Y-m-d H:i:s'),
				'modul' => $modul,
				'aktivita' => $aktivita,
				'master' => $master,
				'rec_id' => $recId,
				'popis' => $popis
			]
		];
		$Obj->Save($item);

		if($type){
			AdminApp::postMessage($popis, $type);
		}

		AdminLogDBAccess::start();

		if(OBE_Cli::isCli()){
			OBE_Cli::writeLn(OBE_Strings::remove_diacritics(strtoupper($type) . ':' . $modul . ' - ' . $aktivita . ' - ' . $master . ' - ' . $popis));
		}
	}
}