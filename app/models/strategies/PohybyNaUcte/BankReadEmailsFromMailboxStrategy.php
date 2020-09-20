<?php

namespace App\Models\Strategies;

use App\Extensions\Utils\Helpers\ArrayHash;
use App\Models\DTO\ImapSettings;
use App\Models\DTO\InfoData;
use App\Models\Repositories\BankBoxesParameters;
use App\Models\Repositories\BankDirsParameters;
use App\Models\Repositories\ParametersRepository;
use App\Models\Services\ImapClientService;
use App\Models\Utils\EmailMessageIdExtract;
use Nette\IOException;
use Tracy\Debugger;

class BankReadEmailsFromMailboxStrategy{

	/** @var ImapSettings */
	private $settings;

	/** @var ParametersRepository */
	private $params;

	/** @var InfoData */
	private $info;

	/** @var BankDirsParameters */
	private $dirs;

	/** @var BankBoxesParameters */
	private $boxes;

	/** @var ImapClientService */
	private $imap;

	/**
	 * @param ImapSettings $settings
	 */
	public function setSettings(
		$settings)
	{
		$this->settings = $settings;
	}

	/**
	 * @param ParametersRepository $params
	 */
	public function setParams(
		ParametersRepository $params)
	{
		$this->params = $params;
	}

	/**
	 * @param InfoData $info
	 */
	public function setInfo(
		InfoData $info)
	{
		$this->info = $info;
	}

	/**
	 * @return string[]
	 */
	public function read()
	{
		$year = date('Y');

		$str = new BankDirsStrategy();
		$str->setParams($this->params);
		try{
			$this->dirs = $str->get($year);
		}catch(IOException $e){
			$this->info->addError($e->getMessage());
			return [];
		}

		$this->imap = new ImapClientService();
		$this->imap->connect($this->settings);

		$root = $this->settings->server . $this->settings->folder;

		$this->imap->switchFolder($root);

		$str = new BankBoxesStrategy();
		$str->setImap($this->imap);
		$str->setParams($this->params);

		try{
			$this->boxes = $str->get($root, $year);
		}catch(\Exception $e){
			$this->info->addError($e->getMessage());
			return [];
		}

		$stats = ArrayHash::from([
			'emails' => 0,
			'platby' => 0,
			'ostatni' => 0
		]);

		$parts = new \ArrayObject([]);

		foreach($this->imap->getMailIds() as $id){

			$stats->emails = $stats->emails + 1;

			$m = $this->imap->getMail($id);
			$messageid = new EmailMessageIdExtract((string) $m->messageId);

			Debugger::log($messageid, 'bank-emails');

			if($this->exists($messageid)){
				$this->info->addInfo($messageid . ' - již stažen a zpracován');
				continue;
			}else{
				$this->info->addInfo($messageid);
			}

			$str = new BankPlatbyParseEmailPlainStrategy();
			$promise = $str->parse($m->subject, $m->textPlain);

			$promise = $promise->then(
				function (
					$result) use (
				$id,
				$messageid,
				$parts,
				$stats)
				{
					foreach($result as $p){
						$parts->append($p);
					}

					$stats->platby = $stats->platby + 1;
					$this->imap->save($id, $this->dirs->incomes . '/' . $messageid . '.eml');
					$this->imap->move($id, $this->boxes->incomes);
				});

			$promise = $promise->otherwise(
				function (
					$message) use (
				$id,
				$messageid,
				$stats)
				{
					$this->info->addError($message . ' ' . $messageid);
					$stats->ostatni = $stats->ostatni + 1;
					$this->imap->save($id, $this->dirs->others . '/' . $messageid . '.eml');
					$this->imap->move($id, $this->boxes->others);
				});

			$promise->done();
		}

		$this->info->addInfo(sprintf('Ze schránky se stáhlo a zpracovalo %d e-mailů', $stats->emails));
		$this->info->addInfo(sprintf('Z toho %d e-mailů obsahovalo platby', $stats->platby));
		$this->info->addInfo(sprintf('Z toho %d e-mailů platby neobsahovalo', $stats->ostatni));

		return $parts->getIterator();
	}

	private function exists(
		$msgId)
	{
		if(!$msgId){
			return false;
		}

		foreach($this->dirs as $d){
			if(file_exists(sprintf('%s/%s.eml', $d, (string) $msgId))){
				return true;
			}
		}

		return false;
	}
}