<?php

define('STORNG_MAIL_LINE', '===============================================================');
define('THIN_MAIL_LINE', '---------------------------------------------------------------');

class OBE_Mail extends OBE_MailBase{
	var $from = '';
	var $to = '';
	var $bcc = [];
	var $body = '';
	var $subject = '';
	var $head = '';
	var $type = '';
	var $multipart = NULL;
	var $boundary = NULL;
	var $parts = NULL;

	private $headCreated = false;

	/**
	 * Vytvoreni objektu mailu
	 *
	 * @param string $to - komu se mail posle
	 * @param string $subject - predmet mailu
	 * @param string $body - telo mailu
	 * @param string $head - hlavicky
	 * @param string $from - od koho bude e-mail
	 * @param string $type - 'plain'
	 * @param boolean $bSend = false - ihned odeslat
	 */
	function __construct($to = '', $from = '', $subject = '', $body = '', $head = '', $type = 'plain', $bSend = false){
		$this->to = $to;
		$this->from = $from;
		$this->setSubject($subject);
		$this->body = $body;
		$this->head = $head;
		$this->type = $type;
		if($bSend){
			$this->Send();
		}
	}

	function AddHead($line){
		$this->head .= $line . "\n";
		return $this;
	}

	function AddLineToBody($line = ''){
		$this->body .= $line . "\r\n";
		return $this;
	}

	function AddTextToBody($text, $erase = false){
		if($erase){
			$this->body = '';
		}
		$this->body .= $text;
		return $this;
	}

	function AddDestinations($type = 'CC', $mails){
		if(is_array($mails)){
			$mails = implode(';', $mails);
		}
		if(!empty($mails)){
			$this->AddHead($type . ' :' . $mails . "\n");
		}
		return $this;
	}

	public function Send($bDebug = false){
		$head = $this->completeHeader();
		$body = $this->completeBody();
		if($bDebug){
			OBE_Trace::dump($this->to, $this->subject, $head, $this->body);
		}
		$ret = false;
		$oldER = error_reporting(0);
		if(!($ret = mail($this->to, $this->subject, $body, $head)) && (OBE_Core::$debug || $bDebug)){
			OBE_Log::log(base64_decode($body));
		}
		if($report = error_get_last()){
			OBE_Log::logDb(print_r($report, true), NULL, 0);
		}
		error_reporting($oldER);
		return $ret;
	}

	/**
	 *
	 * @return String
	 */
	public function completeHeader(){
		if(!$this->headCreated){
			$this->createHead();
		}
		$head = $this->head;
		if($bcc = $this->createBccForHead()){
			$head .= $bcc;
		}
		return $head;
	}

	public function createHead(){
		$this->headCreated = true;
		if(!empty($this->from)){
			$this->AddHead('From: ' . $this->from);
		}
		if($this->multipart){
			$this->AddHead("Content-Type: multipart/" . $this->multipart . "; boundary=\"" . $this->boundary . "\"");
		}else{
			$this->AddHead("Content-Type: text/" . $this->type . "; charset=\"utf-8\"");
		}
		$this->AddHead("Content-Transfer-Encoding: base64")
			->AddHead("MIME-Version: 1.0")
			->AddHead("Subject: " . $this->subject);
	}

	public function resetHead(){
		$this->headCreated = false;
		$this->head = '';
	}

	private function createBccForHead(){
		if(!empty($this->bcc)){
			$this->bcc;
			$bcc = implode(', ', $this->bcc);
			return 'Bcc: ' . $bcc . "\n";
		}
		return NULL;
	}

	public function completeBody(){
		if($this->multipart){
			$ret = '';
			foreach($this->parts as $part){
				$ret .= $part;
			}
			return $ret;
		}else{
			return chunk_split(base64_encode($this->body)) . "\n";
		}
	}

	public function addThinDelimiter(){
		return $this->AddLineToBody(THIN_MAIL_LINE);
	}

	public function addDoubleDelimiter(){
		return $this->AddLineToBody(STORNG_MAIL_LINE);
	}

	/**
	 *
	 * @param String $to
	 * @return OBE_Mail
	 */
	public function setTo($to){
		$this->to = $to;
		return $this;
	}

	/**
	 *
	 * @param String $subject
	 * @return OBE_Mail
	 */
	public function setSubject($subject){
		$this->subject = OBE_Strings::remove_diacritics($subject);
		return $this;
	}

	/**
	 *
	 * @param Mixed $bcc
	 * @return OBE_Mail
	 */
	public function addBcc($bcc){
		if(is_array($bcc)){
			$this->bcc = array_merge($this->bcc, $bcc);
		}else{
			$this->bcc[] = $bcc;
		}
		return $this;
	}

	/**
	 *
	 * @param String $from
	 * @return OBE_Mail
	 */
	public function setFrom($from){
		$this->from = $from;
		return $this;
	}

	/**
	 *
	 * @param String $bcc
	 * @return OBE_Mail
	 */
	public function setBcc($bcc){
		$this->bcc = MArray::AllwaysArray($bcc);
		return $this;
	}

	/**
	 *
	 * @param String $type
	 * @return OBE_Mail
	 */
	public function setType($type){
		$this->type = $type;
		return $this;
	}

	/**
	 *
	 * @param String $head
	 * @return OBE_Mail
	 */
	public function setHead($head){
		$this->headCreated = true;
		$this->head = $head;
		return $this;
	}

	/**
	 *
	 * @param String $body
	 * @return OBE_Mail
	 */
	public function setBody($body){
		$this->body = $body;
		return $this;
	}

	public function setMultipart($multipart){
		$this->boundary = md5(time());
		$this->multipart = $multipart;
	}

	public function addPartAtchs($content, $filename){
		$eol = "\n";
		$msg = '';
		$msg .= "--" . $this->boundary  . $eol;
    	$msg .= "Content-Type: application/force-download;" . $eol;
    	$msg .= "Content-Transfer-Encoding: base64" . $eol;
    	$msg .= "Content-Disposition: attachment; filename=\"".$filename."\"" . $eol . $eol;
    	$msg .= chunk_split(base64_encode($content)) . $eol . $eol;
    	$this->parts[] = $msg;
	}

	public function addPartText($content, $type, $encoding = 'utf-8'){
		$eol = "\n";
		$msg = '';
		$msg .= "--" . $this->boundary  . $eol;
    	$msg .= "Content-Type: text/" . $type . "; charset=\"" . $encoding . "\"" . $eol;
    	$msg .= "Content-Transfer-Encoding: base64" . $eol . $eol;
    	$msg .= chunk_split(base64_encode($content)) . $eol . $eol;
    	$this->parts[] = $msg;
	}

	public function logMail(){
		OBE_Log::log($this->getDump());
	}

	public function getDump(){
		$head = $this->completeHeader();
		$body = $this->completeBody();

		$mail = 'To:' . $this->to . "\n";
		$mail .= 'Subject:' . $this->subject . "\n";
		$mail .= $head . "\n";
		$mail .= $body . "\n";
		return $mail;
	}
}