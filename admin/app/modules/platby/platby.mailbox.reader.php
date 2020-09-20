<?php

class PlatbyMailBoxReader extends AppMailBoxFacade{

	const PLATBY = 0;

	const PLATBY_OSTATNI = 1;

	const OSTATNI = 2;

	private $incomes = [];

	private $incomesOthers = [];

	private $others = [];

	private $count = 0;

	private $year = null;

	private $MMails = null;

	private $subFolders = [
		'Ostatni',
		'Platby',
		'PlatbyJine'
	];

	const SUBJECT_MATCH = 'CEB Info: Zaúčtování platby';

	const FROM = 'notification@csob.cz';

	function __construct(
		$values)
	{
		if($values){
			parent::__construct('banka');

			$this->params = OBE_AppCore::getAppConf('banka-conf');
			$this->year = $year = date('Y');

			try{
				OBE_File::checkDirectorys(
					[
						$this->params['dir-root'],
						$this->params['dir-emails-platby'] . '/' . $year,
						$this->params['dir-emails-ostatni'] . '/' . $year,
						$this->params['dir-emails-platby-ostatni'] . '/' . $year
					]);
			}catch(OBE_FileException $e){
				$this->addError('Při kontrole adresářové struktury došlo k chybě: ' . $e->getMessage());
				return;
			}

			if($this->create($values, 'mail_banka')){
				$this->reader->setMailMoveServer(OBE_Core::getConfEnvVar('move-mail-server'));
				$this->checkBoxes(
					[
						$this->params['box-incomes'] . '.' . $year,
						$this->params['box-incomes-others'] . '.' . $year,
						$this->params['box-others'] . '.' . $year
					]);
			}else{
				return;
			}

			$this->MMails = new MBankaMails();
		}
	}

	public function read()
	{
		$msg = false;

		if(!$this->reader){
			return false;
		}

		try{
			AdminLogDBAccess::Stop();
			try{
				if($mailsIds = $this->readAll()){

					$mails = [];

					$countInbox = count($mailsIds);

					foreach($mailsIds as $id){
						$this->process($this->fetch($id));
					}

					$this->moveMails($this->year);

					$msg = 'Načetlo se: ' . $countInbox . ' e-mailů|z toho: ' . count($this->incomes) . ' avíz|z toho : ' . $this->count . ' přípisů';

					AdminLogActivity::log(null, 'Načtení', $msg, null, null, 'info');

// 					AdminApp::$mainModule->activityLog('Načteno', $msg, null, 'info');
				}else{
					AdminApp::postMessage('Emailová schránka je prázdná', 'info');
				}
			}catch(MailboxReaderException $e){
				AdminApp::postMessage('Při čtení emailů došlo k neočekávané chybě: ' . $e->getMessage(), 'warning');
			}

			return $msg;
		}finally{
			AdminLogDBAccess::Start();
			$this->close();
		}
	}

	/**
	 *
	 * @param MBMessage $mail
	 */
	protected function process(
		$mail)
	{
		$platby = false;
		$mailId = $mail->msgId;

		$new = $this->check($mail);

		if(mb_stripos($mail->subject, self::SUBJECT_MATCH) === false){
			if($new){
				$file = $this->saveMailOstatni($mail);
				$typ = self::OSTATNI;
			}
			$this->log($mail->message_uid . ' - ulozen do ostatnich');
			$this->others[] = $mailId;
		}else if($platby = $this->extract($mail)){
			if($new){
				$file = $this->saveMailPlatby($mail);
				$typ = self::PLATBY;
			}
			$this->log($mail->message_uid . ' - ulozen do plateb');
			$this->incomes[] = $mailId;
		}else{
			if($new){
				$file = $this->saveMailPlatbyOstatni($mail);
				$typ = self::PLATBY_OSTATNI;
			}
			$this->log($mail->message_uid . ' - ulozen do ostatnich plateb');
			$this->incomesOthers[] = $mailId;
		}

		if($new){
			$id = $this->saveMail($mail, $file, $typ);

			if($platby){
				$this->savePlatby($platby, $id);
				$this->log($mail->message_uid . ' - pridano ' . count($platby) . ' plateb');
				return $platby;
			}
		}

		return false;
	}

	/**
	 *
	 * @param MBMessage $mail
	 */
	public function extract(
		$mail)
	{
		$platby = [];
		$parts = explode('dne ', $mail->plain);

		foreach($parts as $pla){
			$date = [];
			if(preg_match('/^(\d{1,2}\.\d{1,2}\.\d{4})/', $pla, $date)){
				$platba = [];

				$castka = $this->extract_param('Částka', $pla);
				$castka = strtr($castka, [
					'CZK' => '',
					' ' => '',
					' ' => ''
				]);

				$castka = OBE_Math::correctFloatNumber($castka);

				if($castka < 0){
					continue;
				}

				$platba['when'] = OBE_DateTime::convertToDB($date[0]);
				$platba['platba'] = $castka;
				$platba['from_cu'] = $this->extract_param('Účet protistrany', $pla);
				$platba['subject'] = $this->extract_param('Název protistrany', $pla);

				$platba['ks'] = $this->extract_param('Konstantní symbol', $pla);
				$platba['vs'] = $this->extract_param('Variabilní symbol', $pla);
				$platba['ss'] = $this->extract_param('Specifický symbol', $pla);
				$platba['msg'] = $this->extract_param('Zpráva příjemci', $pla);

				$platba['id'] = $mail->msgId;
				$platba['msg_id'] = $mail->message_uid;

				$platby[] = $platba;
			}
		}

		return $platby;
	}

	function extract_param(
		$key,
		$subject)
	{
		$matches = [];
		if(preg_match('/' . $key . '\: (.*)\n/', $subject, $matches)){
			return trim($matches[1]);
		}
		return null;
	}

	/**
	 *
	 * @param array $platby
	 * @param MBMessage $mail
	 */
	protected function check(
		$mail)
	{
		$this->log(' - check: ' . $mail->message_uid);

		if($mails = $this->MMails->FindAll([
			'message_id' => $mail->message_uid
		])){
			$this->log($mail->message_uid . ' - jiz zpracovany');
			return false;
		}
		return true;
	}

	protected function savePlatby(
		$platby,
		$id)
	{
		$Platba = new MPlatby();

		foreach($platby as $f){
			$pl['Platba'] = [
				'when' => $f['when'],
				'from_cu' => $f['from_cu'],
				'subject' => $f['subject'],
				'platba' => $f['platba'],
				'vs' => $f['vs'],
				'ks' => $f['ks'],
				'ss' => $f['ss'],
				'msg' => $f['msg'],
				'man' => 0,
				'edit' => 0,
				'link' => 0,
				'mail_id' => $id
			];
			$Platba->Save($pl);
		}
		$this->count += count($platby);
	}

	/**
	 *
	 * @param MBMessage $mail
	 * @param string $file
	 * @param string $type
	 * @return boolean
	 */
	protected function saveMail(
		$mail,
		$file,
		$type)
	{
		$d = [
			$this->MMails->name => [
				'message_id' => $mail->message_uid,
				'mail_dt' => $mail->sended,
				'typ' => $type,
				'file' => $file
			]
		];
		return $this->MMails->Save($d);
	}

	/**
	 *
	 * @param MBMessage $mail
	 */
	protected function saveMailPlatby(
		$mail)
	{
		return $this->saveFile($mail, $this->params['dir-emails-platby'] . '/' . $mail->year);
	}

	/**
	 *
	 * @param MBMessage $mail
	 */
	protected function saveMailPlatbyOstatni(
		$mail)
	{
		return $this->saveFile($mail, $this->params['dir-emails-platby-ostatni'] . '/' . $mail->year);
	}

	/**
	 *
	 * @param MBMessage $mail
	 */
	protected function saveMailOstatni(
		$mail)
	{
		return $this->saveFile($mail, $this->params['dir-emails-ostatni'] . '/' . $mail->year);
	}

	/**
	 *
	 * @param MBMessage $mail
	 * @param string $dir
	 * @return string
	 */
	private function saveFile(
		$mail,
		$dir)
	{
		OBE_File::checkDirectorys($dir);
		$file = $dir . '/' . OBE_File::normalizeFile($mail->message_uid) . '.eml';
		$raw = $mail->raw_header . "\n\n" . $mail->raw_body;
		file_put_contents(WWW_DIR_OLD . '/' . $file, $raw);
		return $file;
	}

	protected function moveMails(
		$year)
	{
		$this->move_ids_to($this->incomes, $this->params['box-incomes'] . '.' . $year);
		$this->move_ids_to($this->incomesOthers, $this->params['box-incomes-others'] . '.' . $year);
		$this->move_ids_to($this->others, $this->params['box-others'] . '.' . $year);
	}
}