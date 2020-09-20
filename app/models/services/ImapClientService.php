<?php

namespace App\Models\Services;

use App\Models\DTO\ImapSettings;
use PhpImap\Mailbox;
use PhpImap\Exceptions\InvalidParameterException;

class ImapClientService{

	/** @var Mailbox */
	private $client;

	/** @var ImapSettings */
	private $settings;

	public function __construct()
	{
	}

	/**
	 * @param ImapSettings $box
	 * @throws InvalidParameterException
	 */
	public function connect(
		ImapSettings $settings)
	{
		if($this->client){
			$this->close();
		}

		$this->settings = $settings;

		$this->client = new Mailbox($settings->server, $settings->login, $settings->pass);
	}

	public function close()
	{
		$this->client->disconnect();
	}

	public function getImap()
	{
		return $this->settings->server;
	}

	public function getMailIds(
		$offset = null,
		$limit = null)
	{
		$ids = $this->client->searchMailbox();
		return array_slice($ids, $offset, $limit);
	}

	public function getHeaders(
		$mailsIds)
	{
		return $this->client->getMailsInfo($mailsIds);
	}

	public function getFolders()
	{
		return $this->client->getMailboxes('*');
	}

	public function switchFolder(
		$fullPath)
	{
		$this->client->switchMailbox($fullPath);
	}

	public function createFolder(
		$folder)
	{
		$path = $this->client->getImapPath();
		if($path === substr($folder, 0, strlen($path))){
			$folder = ltrim(substr($folder, strlen($path)), '.');
		}

		$this->client->createMailbox($folder);
	}

	public function removeFolder(
		$folder)
	{
		$path = $this->client->getImapPath();
		if($path === substr($folder, 0, strlen($path))){
			$folder = ltrim(substr($folder, strlen($path)), '.');
		}

		$this->client->deleteMailbox($folder);
	}

	/**
	 * @param int $id
	 * @return \PhpImap\IncomingMail
	 */
	public function getMail(
		$id)
	{
		return $this->client->getMail($id, false);
	}

	public function getRaw(
		$id)
	{
		return $this->client->getRawMail($id);
	}

	public function move(
		$id,
		$folder)
	{
		if(strpos($folder, '}')){
			$folder = substr($folder, strpos($folder, '}') + 1);
		}

		$this->client->moveMail($id, $folder);
	}

	public function save(
		$id,
		$file)
	{
		$this->client->saveMail($id, $file);
	}

	public function delete(
		$id)
	{
		$this->client->deleteMail($id);
	}

	public function expunge()
	{
		$this->client->expungeDeletedMails();
	}
}