<?php

class AppMailBoxFacadeException extends OBE_Exception{
}

class AppMailBoxFacade{

	protected $box = null;

	protected $email = null;

	/** @var OBE_MailReader */
	protected $reader = null;

	protected $settings = [];

	/** @var \App\Core\Container */
	private $fails;

	/** @var \App\Core\Container */
	private $errors;

	/** @var \App\Core\Logger */
	private $log;

	const CACHE_KEY = 'mailboxreader';

	function __construct($logSubDir){
		$this->errors = new \App\Core\Container();
		$this->fails = new \App\Core\Container();
		$this->log = new \App\Core\Logger($logSubDir, 'mailreader.log');
	}

	function __destruct(){
		$this->close();
	}

	public function create($values, $prefix){
		$reader = null;

		/* kontrola jestli nahodou zrovna na schranku neni nekdo pripojenej */

		$this->box = $values[$prefix . '_folder'];
		$this->email = $values[$prefix . '_user'];

		$this->settings = [
			'server' => $values[$prefix . '_server'],
			'user' => $values[$prefix . '_user'],
			'pass' => $values[$prefix . '_pass'],
			'folder' => $this->box,
			'opts' => 0
		]; // OP_SECURE

		$old = set_error_handler(function ($errno, $errstr, $errfile, $errline){
			throw new AppMailBoxFacadeException($errstr);
		});

		try{
			$reader = new OBE_MailReader($this->settings);
		}catch(MailboxReaderException $e){
			$this->addError('Při přístupu k emailové schránce došlo k chybě: ' . $e->getMessage());
		}catch(\Exception $e){
			$this->addError('Při přístupu k emailové schránce došlo k chybě: ' . $e->getMessage());
		}

		set_error_handler($old);

		if($reader){
			$this->reader = $reader;
			return true;
		}

		return false;
	}

	public function close(){
		if($this->reader){
			$this->reader->close();
			$this->reader = null;
		}
	}

	public function readAll(){
		return $this->reader->search('ALL');
	}

	/**
	 *
	 * @param integer $msg_id
	 * @return MBMessage
	 */
	public function fetch($msg_id){
		$mail = $this->reader->fetchMail($msg_id);
		if(isset($mail->header->message_id)){
			$mail->message_uid = $mail->header->message_id;
		}
		if(isset($mail->header->date)){
			$time = strtotime($mail->header->date);
			$mail->sended = date('Y-m-d H:i:s', $time);
			$mail->year = date('Y', $time);
			$mail->date = $time;
		}
		return $mail;
	}

	protected function move_ids_to($ids, $folder){
		$ids = array_unique(MArray::AllwaysArray($ids));
		if(!empty($ids)){
			$this->reader->move_to($this->box, $folder, $ids);
		}
	}

	protected function checkBoxes($folders){
		$folder = '';
		try{
			$folders = MArray::AllwaysArray($folders);
			if(!empty($folders)){
				$box_folders = $this->reader->list_folders($this->box);

				foreach($folders as $folder){

					if(!in_array($folder, $box_folders)){
						$this->reader->create_folder($this->box, $folder);
					}
				}
			}
			return true;
		}catch(MailboxReaderException $e){
			$this->addError('Při vytváření schránky `' . $this->box . '.' . $folder . '` došlo k chybě: ' . $e->getMessage());
			return false;
		}
	}

	public function getReader(){
		return $this->reader;
	}

	public function getBox(){
		return $this->box;
	}

	public function getMail(){
		return $this->email;
	}

	protected function addError($err){
		$this->log($err);
		$this->errors->add($err);
	}

	public function hasErrors(){
		return $this->errors->has();
	}

	public function getErrors(){
		return $this->errors->get();
	}

	protected function addFail($fail, $message_uid = null){
		$fail = MArray::AllwaysArray($fail);
		$this->log($fail);
		foreach($fail as $f){
			$this->fails->add($message_uid . ' // ' . $f);
		}
	}

	public function hasFails(){
		return $this->fails->has();
	}

	public function getFails(){
		return $this->fails->get();
	}

	protected function log($strs){
		$this->log->log($strs);
	}
}