<?php

namespace App\Modules\MailBoxes\Components;

use App\Extensions\Components\BaseComponent;
use App\Models\Services\ImapClientService;
use App\Models\Strategies\ExtractImapSettingsStrategy;
use App\Models\Repositories\SettingsRepository;

class BoxContentComponent extends BaseComponent{

	/** @var ImapClientService */
	private $imap;

	/** @var SettingsRepository */
	private $repSettings;

	/** @var string */
	private $box;

	/** @var string @persistent */
	public $folder;

	const DEFAULT_FOLDER = 'INBOX';

	public function __construct(
		ImapClientService $imap,
		SettingsRepository $repSettings)
	{
		$this->imap = $imap;
		$this->repSettings = $repSettings;

		$this->onAnchor[] = function ()
		{
			$this->folder = !$this->folder ? self::DEFAULT_FOLDER : $this->folder;
		};
	}

	/**
	 * @param string $box
	 */
	public function setBox(
		$box)
	{
		$this->box = $box;
	}

	public function render()
	{
		$extr = new ExtractImapSettingsStrategy();
		$settings = $extr->get($this->repSettings, $this->box);

		$this->imap->connect($settings);

		$folders = collection($this->imap->getFolders())->indexBy('fullpath')
			->extract('shortpath')
			->toArray();

		$this->imap->switchFolder($this->folder);

		$headers = $this->imap->getHeaders($this->imap->getMailIds(0, 20));

		$this->template->setParameters([
			'server' => $settings->server,
			'folder' => $this->folder,
			'folders' => $folders,
			'mails' => $headers
		]);

		parent::render();
	}

	public function handleFolder(
		$folder)
	{
		$this->folder = $folder;

		if(!$this->isAjax()){
			$this->redirect('this');
		}

		$this->redrawControl('box');
	}
}