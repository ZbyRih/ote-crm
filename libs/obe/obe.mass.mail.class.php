<?php
class OBE_MassMail{
	var $debug = false;

	function __construct($debug = false){
		$this->debug = $debug;
	}

	/**
	 *
	 * @param OBE_Mail $mailObj
	 * @param Array $emails
	 * @param String $from
	 * @param Array $stats
	 * @param Integer $buffer_size
	 */
	function sendMassMail($mailObj, $emails, &$stats, &$sequence, $buffer_size = 50){
		$offset = 0;
		$size = count($emails);
		$sendCount = 0;
		$sequence = NULL;
		$saveForCron = false;

		$stats[] = 'Počet mailů celkem: ' . $size;

		$mailObj->createHead();

		if(isset(EnviromentConfig::$global['mailLimit'])){
			if($size >= EnviromentConfig::$global['mailHourLimit'] && EnviromentConfig::$global['mailLimit']){
				$saveForCron = EnviromentConfig::$global['mailLimit'];
			}
		}

		if($saveForCron || $this->debug){
			$sequence = $this->createSequence($mailObj, $size);
			if(isset(EnviromentConfig::$global['mailHourLimit'])){
				$mailsPerHour = EnviromentConfig::$global['mailHourLimit'] - EnviromentConfig::$global['mailReserve'];
				$buffer_size = $mailsPerHour / ceil($mailsPerHour / $buffer_size);
			}
		}

		$position = 1;

		while(true){
 			if(!OBE_Log::$timer->have(1)){
				return $offset;
			}
			if($offset > $size){
				break;
			}
			$buffer = array_slice($emails, $offset, $buffer_size);
			if(empty($buffer)){
				break;
			}

			if($saveForCron || $this->debug){
				$this->Save($mailObj, $sequence, $position, count($buffer), $buffer);
			}else{
				$mailObj->Send();
			}

			$sendCount += count($buffer);
			$offset += $buffer_size;
			$position++;
		}

		if($saveForCron || $this->debug){
			$stats[] = 'Do fronty přidáno mailů : ' . $sendCount;
		}else{
			$stats[] = 'Počet odeslaných mailů : ' . $sendCount;
		}
		return true;
	}

	function createSequence($mailObj, $numMails){
		$sequence = OBE_App::$db->getMaxOnRow('cron_mails', 'sequence') + 1;
		OBE_App::$db->Insert('cron_mails', [
			  'sequence' => $sequence
			, 'status' => 0
			, 'created' => 'NOW()'
			, 'mails_count' => $numMails
			, 'to' => $mailObj->to
			, 'subject' => $mailObj->subject
			, 'body' => $mailObj->body
		]);
		return $sequence;
	}

	/**
	 *
	 * @param OBE_Mail $mailObj
	 * @param Integer $sequence
	 */
	function Save($mailObj, $sequence, $position, $numMails, $mails){
		$head = $mailObj->completeHeader();
		OBE_App::$db->Insert('cron_mails_head', [
			  'sequence' => $sequence
			, 'position' => $position
			, 'send' => 0
			, 'num_mails' => $numMails
			, 'head' => $head
			, 'to' => serialize($mails)
		]);
	}

	function sendSavedSequences(){
		if($cm = $this->getNextSequencePart()){
			$this->send($cm);
			if(!($cmNext = $this->getNextSequencePart())){ /* je ve fronte dalsi sequence ? */
				$this->startNewSeq();/* kdyz neni nova odstartovana, pokusi se spustit novou */
				/* coz znamena ze mozna ta co se odeslala s cm je posledni pustena */
				$this->updateNewsAndDeleteSequnce($cm['sequence']);
			}else{
			}
		}else{
			if($this->startNewSeq()){
				$this->sendSavedSequences();
			}
		}
	}

	function getNextSequencePart(){
		return OBE_App::$db->FetchSingleArray('
			SELECT cmh.id AS cmhid, cmh.head, cmh.to as mail_to, cm.id AS cmid, cm.to, cm.subject, cm.body, cm.sequence
			FROM cron_mails_head AS cmh
				, cron_mails AS cm
			WHERE cmh.send = 0 AND cmh.sequence = cm.sequence AND cm.status = 1
			ORDER BY cmh.sequence ASC, cmh.position ASC
			LIMIT 1');
	}

	function startNewSeq(){
		if($startSeq = OBE_App::$db->FetchSingleArray('
			SELECT cm.id AS cmid FROM cron_mails AS cm
			WHERE cm.status = 0
			ORDER BY cm.created ASC	LIMIT 1')){
			OBE_App::$db->Update('cron_mails', ['status' => 1], ['id =' . $startSeq['cmid']]);
			return true;
		}
		return false;
	}

	function updateNewsAndDeleteSequnce($sequence){
		OBE_App::$db->Update('obe_news', ['mailsequence' => NULL, 'newsmailsend' => 3], ['mailsequence' => $sequence]);
		OBE_App::$db->Delete('cron_mails', ['sequence' => $sequence]);
	}

	function send($data){
//		$this->writeLog($data['head']);

		$mails = unserialize($data['mail_to']);
		foreach($mails as $to){

			$body = mb_ereg_replace('\[unsubscribe_code\]', md5($to), $data['body']);

			$mailObj = new OBE_Mail();
			$mailObj->setTo($to)
				->setSubject($data['subject'])
				->setHead($data['head'])
				->setBody($body);
			$mailObj->Send();
			unset($mailObj);
		}
		OBE_App::$db->Update('cron_mails_head', ['send' => 1], ['id = ' . $data['cmhid']]);
	}

	function pauseSequence($sequence){
		OBE_App::$db->Update('cron_mails', ['status' => 2], ['sequence' => $sequence]);
	}

	function continueSequence($sequence){
		OBE_App::$db->Update('cron_mails', ['status' => 1], ['sequence' => $sequence]);
	}

	function cancelSequence($sequence){
		OBE_App::$db->Delete('cron_mails', ['sequence' => $sequence]);
	}

	function getSequenceStatus($sequence){
		$stats = [];
		$cronMail = OBE_App::$db->FetchSingleArray('SELECT cm.mails_count, cm.status FROM cron_mails AS cm WHERE cm.sequence = ' . $sequence . '');
		switch($cronMail['status']){
			case 0:
				$stat = 'je ve frontě, čeká na zahájení';
				break;
			case 1:
				$data = OBE_App::$db->FetchSingleArray('
					SELECT SUM(cmh.num_mails) AS sended
					FROM cron_mails_head AS cmh
					WHERE cmh.send = 1 AND cmh.sequence = ' . $sequence . '
					GROUP BY cmh.sequence');
				$stat = 'je ve frontě, probíhá';
				$stats[] = 'Z ' . $cronMail['mails_count'] . ' mailu celkem, odeslano ' . (int)$data['sended'];
				break;
			case 2:
				$data = OBE_App::$db->FetchSingleArray('
					SELECT SUM(cmh.num_mails) AS sended
					FROM cron_mails_head AS cmh
					WHERE cmh.send = 1 AND cmh.sequence = ' . $sequence . '
					GROUP BY cmh.sequence');
				$stat = 'je ve frontě, ale pozastaveno';
				$stats[] = 'Z ' . $cronMail['mails_count'] . ' mailu celkem, odeslano ' . (int)$data['sended'];
				break;
			case 3:
				$stat = 'odesilani ukonceno';
				break;
		}
		array_unshift($stats, $stat);
		return $stats;
	}
}