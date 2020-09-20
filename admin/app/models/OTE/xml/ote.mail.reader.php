<?php

class OTEMailBoxReaderException extends OBE_Exception{
}

class OTEMailBoxReader extends AppMailBoxFacade{

	/**
	 * @var MOTEMails
	 */
	private $mails = null;

	/**
	 * @var OTEXmlProcessor
	 */
	private $xmlProcessor = null;

	/**
	 * @var OTEMailCert
	 */
	private $cert;

	private $temp_raw = null;

	private $temp_decrypted = null;

	private $temp_recived_cert = null;

	private $temp_unsigned = null;

	private $errors = null;

	private $fails = [];

	private static $tmp = '/tmp/';

	/*
	 * dir-root -- root slozka
	 * dir-emails-backup -- zagzipovane zpracovane maily
	 * dir-emails-undecrypted -- nezagzipovane
	 * dir-emails-other -- ostatni maily
	 * dir-ote-messages -- ote zpravy
	 * dir-xml-unknown -- neidentifikovana xml
	 * box-readed -- mailova schranka se zpracovanymi maily
	 * box-others -- Ostatni ne xml maily
	 */
	protected $params = [];

	function __construct(
		$values)
	{
		if($values){
			parent::__construct('ote');

			$this->params = OBE_AppCore::getAppConf('ote-conf');
			$year = date('Y');

			try{
				OBE_File::checkDirectorys(
					[
						$this->params['dir-root'],
						$this->params['dir-emails-backup'] . '/' . $year,
						$this->params['dir-emails-undecrypted'] . '/' . $year,
						$this->params['dir-emails-other'] . '/' . $year,
						$this->params['dir-ote-messages'] . '/' . $year,
						$this->params['dir-xml-unknown'] . '/' . $year
					]);
			}catch(OBE_FileException $e){
				$this->addError('Při kontrole adresářové struktury došlo k chybě: ' . $e->getMessage());
				return;
			}

			if($this->create($values, 'mail_ote')){
				$this->reader->setMailMoveServer(OBE_Core::getConfEnvVar('move-mail-server'));
				$this->checkBoxes([
					$this->params['box-readed'],
					$this->params['box-others']
				]);
			}else{
				return;
			}

			$this->cert = new OTEMailCert($values);

			$this->temp_raw = tempnam(self::$tmp, 'eml');
			$this->temp_decrypted = tempnam(self::$tmp, 'dec');
			$this->temp_recived_cert = tempnam(self::$tmp, 'cer');
			$this->temp_unsigned = tempnam(self::$tmp, 'uns');

			$this->mails = new MOTEMails();
			$this->xmlProcessor = new OTEXmlProcessor();
		}
	}

	public function checkUnprocessed(
		$undecryptedOnly = false)
	{
		return [];
	}

	public function read(
		$tryDB = [])
	{
		if(!$this->reader){
			return false;
		}

		$lastXmlErr = $this->start();

		$this->log(' - ctu emaily');

		$msg = false;
		$success = 0;

		if($this->reader && $ids = $this->readAll()){
			$this->log('Emailů ve schránce: ' . count($ids) . ' - zpracovávám');
			foreach($ids as $id){
				try{
					$mail = $this->fetch($id);

					if(in_array($mail->message_uid, $tryDB)){
						continue;
					}

					if($this->check($mail)){

						$year = date('Y', strtotime($mail->sended));

						if($this->process($mail->msgId, $mail->message_uid, $mail->sended, $year, $mail->subject)){
							$this->moveMailToPrectene($mail->msgId, $year);
							$success++;
						}

						if(!empty($this->fails)){
							$this->addFail($this->fails, $mail->message_uid);
							$this->fails = [];
						}
					}
				}catch(MailboxReaderException $e){
					$msg = $e->getMessage();
					if($e->mail){
						$msg .= ' <' . $e->mail->message_uid . '>';
					}
					$this->addError('Při práci s emaily došlo k chybě: ' . $msg);
					if($errs = imap_errors()){
						$this->addError(print_r($errs, true));
						return false;
					}
					$code = $e->getCode();
					if($code == MailboxReader::MOVE_MAIL || $code == MailboxReader::CREATE_BOX){
						return false;
					}
				}catch(OTEMailBoxReaderException $e){
					$this->addFail($e->getMessage(), $mail->message_uid);
					return false;
				}
			}
			$msg = 'ztaženo: ' . count($ids) . ' zpráv |z toho: ' . $success . ' zpracováno';
			$this->log($msg);
			AdminLogActivity::log(null, 'Načtení', $msg, null, null, 'info');
		}

		$this->end($lastXmlErr);

		$this->close();

		if($success){
			return $msg;
		}
		return true;
	}

	private function start()
	{
		AdminLogDBAccess::Stop();
		return libxml_use_internal_errors(true);
	}

	private function end(
		$lastXmlErr)
	{
		libxml_clear_errors();
		libxml_use_internal_errors($lastXmlErr);
		AdminLogDBAccess::start();
	}

	/**
	 * @param MBMessage $mail
	 */
	private function check(
		$mail)
	{
		$this->log(' - check: ' . $mail->message_uid);

		$year = date('Y', strtotime($mail->sended));

		if(count($mail->parts) > 0 && isset($mail->parts[1]->subtype) && ($mail->parts[1]->subtype == 'PKCS7-MIME' || $mail->parts[1]->subtype == 'X-PKCS7-MIME')){

			if($mail->message_uid && $saved = $this->mails->FindOneBy('msg_uid', $mail->message_uid)){

				if(true === ($r = $this->tryFromDB($saved[$this->mails->name]))){
					$this->moveMailToPrectene($mail->msgId, $year);
					return true;
				}else{
					return false;
				}
			}
			return true;
		}

		// tady si nic neukladam do db o mailu ale mozna bych mel

		$raw = $this->reader->fetchRaw($mail->msgId);
		$this->saveOtherFile($raw, $mail->message_uid, $year);
		$this->moveMailToOthers($mail->msgId, $year);

		$this->log($mail->message_uid . ' - ulozen do ostatnich');

		// neni mime mail
		return false;
	}

	private function tryFromDB(
		$data)
	{
		$old_id = $data['id'];
		$message_uid = $data['msg_uid'];
		$date = $data['received'];
		$year = date('Y', strtotime($date));

		if($data['processed'] && $data['decrypted']){
			return true;
		}else if(!$data['decrypted'] && $data['file_eml']){ // zkusime desifrovat a zprocesovat
			if($this->tryUndecrypted($message_uid, $date, $year, $data['subject'], $old_id, $data['file_eml'])){
				unlink(WWW_DIR_OLD . '/' . $data['file_eml']); // tady mazeme nedcryptovany protoze ho to rozsifrovalo a nahradilo
				return true;
			}
			return -1;
		}else if(!$data['processed']){ // kdyz mame i xml - poustime xml procesor // zkusime znova zprocesovat
			if($this->tryUnprocessed($message_uid, $date, $year, $data['subject'], $old_id, ((!$data['file_xml']) ? $data['file_eml'] : $data['file_xml']))){
				return true;
			}
			return -2;
		}

		throw new OTEMailBoxReaderException('Nekonzistentní stav databáze, decrypted = 0 a neni k dispozici email');
	}

	private function tryUndecrypted(
		$message_uid,
		$date,
		$year,
		$subject,
		$old_id,
		$file)
	{
		file_put_contents($this->temp_raw, file_get_contents((WWW_DIR_OLD . '/' . $file)));

		$this->log($message_uid . ' - ' . $file . ' - nacteni emailu ze souboru');

		return $this->process(null, $message_uid, $date, $year, $subject, $old_id);
	}

	private function tryUnprocessed(
		$message_uid,
		$date,
		$year,
		$subject,
		$old_id,
		$file)
	{
		$this->log($message_uid . ' - ' . $file . ' - nacteni xml ze souboru');
		if(!file_exists(WWW_DIR_OLD . '/' . $file)){
			$this->log($message_uid . ' - ' . $file . ' - soubor neexisuje :/');
			return false;
		}else{
			return $this->processXml(file_get_contents(WWW_DIR_OLD . '/' . $file), $message_uid, $date, $year, $subject, $old_id);
		}
	}

	/**
	 * @param MBMessage $mail
	 */
	private function process(
		$msgId,
		$message_uid,
		$date,
		$year,
		$subject,
		$old_id = null)
	{
		if($this->download($msgId, $old_id)){
			if($this->decrypt() && $this->unsign()){
				$this->processXml(file_get_contents($this->temp_unsigned), $message_uid, $date, $year, $subject, $old_id);
				return true;
			}else{
				$this->log($message_uid . ' - ukladam nedecryptovany mail');

				$file = $this->saveUndecryptedMail($message_uid, $year);
				$this->dbSave([
					'processed' => 0,
					'decrypted' => 0,
					'file_eml' => $file
				], $message_uid, $date, $subject, $old_id);
			}
		}
		return false;
	}

	/**
	 * @param string $raw_xml
	 * @param MBMessage $mail
	 * @param int $old_id
	 * @return mixed|boolean
	 */
	private function processXml(
		$raw,
		$message_uid,
		$date,
		$year,
		$subject,
		$old_id = null)
	{
		if(empty($raw)){
			$this->log($message_uid . ' - raw je prazdny');
			return false;
		}

		$this->log($message_uid . ' - zpracovavam xml');

		$parts = explode("\r\n\r\n", $raw);

		if(count($parts) > 1){
			if(strpos($parts[0], 'Content-Transfer-Encoding: quoted-printable')){
				$parts[1] = quoted_printable_decode($parts[1]);
			}

			if(strpos($parts[0], 'Content-Transfer-Encoding: base64')){
				$parts[1] = base64_decode($parts[1]);
			}

			if(isset($parts[1])){
				$raw_xml = $parts[1];
			}else{
				$raw_xml = $raw;
			}
		}else{
			$raw_xml = $raw;
		}

		$file_xml = null;

		if($xml = simplexml_load_string($raw_xml)){
			$ote_kod = null;
			$ote_id = null;

			if(preg_match('/([A-Z0-9]{3,}) \- /', $subject, $match)){
				$ote_kod = $match[1];
				if(isset($xml->Identification)){
					if(isset($xml->Identification['v'])){
						$ote_id = (string) $xml->Identification['v'];
					}
				}
			}

			if(isset($xml['message-code'])){
				$ote_kod = (string) $xml['message-code'];
			}
			if(isset($xml['id'])){
				$ote_id = (string) $xml['id'];
			}

			if($ote_kod && $ote_id){
				$file_gzip = $this->saveGZipedMail($message_uid, $ote_kod, $year);
				$file_xml = $this->saveXmlFile($raw_xml, $ote_id, $ote_kod, $year);

				$result = $this->xmlProcessor->process($xml, $ote_id);

				$this->dbSave(
					[
						'processed' => (int) $result,
						'decrypted' => 1,
						'file_eml' => $file_gzip,
						'file_xml' => $file_xml,
						'ote_kod' => $ote_kod,
						'ote_id' => $ote_id
					], $message_uid, $date, $subject, $old_id);

				$this->log($message_uid . ' - ukladam vysledek zpracovani xml');

				return $result;
			}else{
				if($message_uid){
					$file_xml = $this->saveUnknownXmlFile($raw_xml, $message_uid, $year);
					$this->log($message_uid . ' - ukladam nezname xml');
				}
			}
		}else{
			$errors = libxml_get_errors();
			foreach($errors as $error){
				$this->log($error->message);
			}
			libxml_clear_errors();
		}

		$file_mail = $this->saveOtherFile($raw, $message_uid, $year);
		$this->dbSave(
			[
				'processed' => 0,
				'decrypted' => 1
			] + (($message_uid) ? [
				'file_eml' => $file_mail
			] : []) + (($file_xml && $message_uid) ? [
				'file_xml' => $file_xml
			] : []), $message_uid, $date, $subject, $old_id);

		if(!$file_xml){
			$this->log($message_uid . ' - ukladam ne xml mail');
		}

		return false;
	}

	/**
	 * @param MBMessage $mail
	 */
	private function download(
		$msgId,
		$old_id = null)
	{
		if($msgId && !$old_id){

			$this->log(' - stahuji email ze schranky');

			$raw = $this->reader->fetchPart($msgId, 1);
			if(!empty($raw)){
				return file_put_contents($this->temp_raw, $raw);
			}
			return false;
		}
		return true;
	}

	private function decrypt()
	{
		file_put_contents($this->temp_decrypted, '');
		if(openssl_pkcs7_decrypt($this->temp_raw, $this->temp_decrypted, $this->cert->public, $this->cert->private)){
			return true;
		}else{
			$this->getOpenSSLErrors('decrypt');
		}
		return false;
	}

	private function unsign()
	{
		$ver = openssl_pkcs7_verify($this->temp_decrypted, PKCS7_NOVERIFY, $this->temp_recived_cert);

		if($ver !== true){
			$this->getOpenSSLErrors('verify #1');
			return false;
		}

		$ver = openssl_pkcs7_verify($this->temp_decrypted, PKCS7_NOVERIFY, $this->temp_recived_cert, array(), $this->temp_recived_cert, $this->temp_unsigned);

		if($ver !== true){
			$this->getOpenSSLErrors('verify #2');
			return false;
		}

		return true;
	}

	/**
	 * @param MBMessage $mail
	 */
	private function moveMailToPrectene(
		$msgId,
		$year)
	{
		$this->log(' - přesouvám email do přečtených ' . $year);
		$box = $this->params['box-readed'] . '.' . $year;
		if($this->checkBoxes($box)){
			$this->move_ids_to($msgId, $box);
		}
	}

	/**
	 * @param MBMessage $mail
	 */
	private function moveMailToOthers(
		$msgId,
		$year)
	{
		$box = $this->params['box-others'] . '.' . $year;
		if($this->checkBoxes($box)){
			$this->move_ids_to($msgId, $box);
		}
	}

	/**
	 * @param MBMessage $mail
	 * @param string $kod
	 */
	private function saveGZipedMail(
		$message_uid,
		$ote_kod,
		$year)
	{
		$dir = $this->params['dir-emails-backup'] . '/' . $year . '/' . strtoupper($ote_kod);
		OBE_File::checkDirectorys($dir);
		$file = $dir . '/' . OBE_File::normalizeFile($message_uid) . '.eml';
		file_put_contents(WWW_DIR_OLD . '/' . $file, file_get_contents($this->temp_raw));
		return $file;
	}

	private function saveUndecryptedMail(
		$message_uid,
		$year)
	{
		$dir = $this->params['dir-emails-undecrypted'] . '/' . $year;
		OBE_File::checkDirectorys($dir);
		$file = $dir . '/' . OBE_File::normalizeFile($message_uid) . '.eml';
		file_put_contents(WWW_DIR_OLD . '/' . $file, file_get_contents($this->temp_raw));
		return $file;
	}

	private function saveXmlFile(
		$raw_xml,
		$xml_uid,
		$ote_kod,
		$year)
	{
		$dir = $this->params['dir-ote-messages'] . '/' . $year . '/' . strtoupper($ote_kod);
		OBE_File::checkDirectorys($dir);
		$file = $dir . '/' . $xml_uid . '.xml';
		file_put_contents(WWW_DIR_OLD . '/' . $file, $raw_xml);
		return $file;
	}

	private function saveUnknownXmlFile(
		$raw_xml,
		$message_uid,
		$year)
	{
		$dir = $this->params['dir-xml-unknown'] . '/' . $year;
		OBE_File::checkDirectorys($dir);
		$file = $dir . '/' . OBE_File::normalizeFile($message_uid) . '.xml';
		file_put_contents(WWW_DIR_OLD . '/' . $file, $raw_xml);
		return $file;
	}

	private function saveOtherFile(
		$raw,
		$message_uid,
		$year)
	{
		$dir = $this->params['dir-emails-other'] . '/' . $year;
		OBE_File::checkDirectorys($dir);
		$file = $dir . '/' . OBE_File::normalizeFile($message_uid) . '.eml';
		file_put_contents(WWW_DIR_OLD . '/' . $file, $raw);
		return $file;
	}

	private function dbSave(
		$data,
		$message_uid,
		$date,
		$subject,
		$old_id)
	{
		$data = $data + [
			'msg_uid' => $message_uid,
			'received' => $date
		];

		if($old_id){
			$data = $data + [
				'id' => $old_id
			];
		}

		if($subject){
			$data = $data + [
				'subject' => $subject
			];
		}

		$mdata = [
			$this->mails->name => $data
		];
		$this->mails->Save($mdata);
	}

	private function getOpenSSLErrors(
		$loc = null)
	{
		while($e = openssl_error_string()){
			$this->fails[] = $loc . ':' . $e;
			$this->log($loc . ':' . $e);
		}
	}
}