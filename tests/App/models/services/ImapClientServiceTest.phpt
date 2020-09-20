<?php

namespace Tests\App\Models\Services;

require_once __DIR__ . '/../../../bootstrap.php';

use App\Models\DTO\ImapSettings;
use Tests\Utils\IntegrationTestCase;
use App\Models\Services\ImapClientService;
use Tester\Assert;

class ImapClientServiceTest extends IntegrationTestCase{

	/** @var ImapSettings */
	private $settings;

	const TEST_FOLDER = 'TestFolder';

	const TEST_FOLDER2 = 'TestFolder2';

	public function __construct()
	{
		$this->settings = new ImapSettings();
		$this->settings->server = '{localhost:143/notls/novalidate-cert}';
		$this->settings->folder = 'INBOX';
		$this->settings->login = 'traxell-banka@xb1.local';
		$this->settings->pass = 'Admin';
	}

	public function testCreateDir()
	{
		$imap = new ImapClientService();
		$imap->connect($this->settings);

		$imap->createFolder(self::TEST_FOLDER);
		$imap->createFolder($imap->getImap() . self::TEST_FOLDER2);

		$folders = collection($imap->getFolders())->extract('fullpath')->toList();

		Assert::true(in_array($imap->getImap() . self::TEST_FOLDER, $folders));
		Assert::true(in_array($imap->getImap() . self::TEST_FOLDER2, $folders));

		$imap->expunge();
		$imap->close();
	}

	public function testRemoveDir()
	{
		$imap = new ImapClientService();
		$imap->connect($this->settings);

		$imap->removeFolder(self::TEST_FOLDER);
		$imap->removeFolder($imap->getImap() . self::TEST_FOLDER2);
		$imap->expunge();

		$folders = collection($imap->getFolders())->extract('fullpath')->toList();

		Assert::false(in_array($imap->getImap() . self::TEST_FOLDER, $folders));
		Assert::false(in_array($imap->getImap() . self::TEST_FOLDER2, $folders));

		$imap->close();
	}
}
(new ImapClientServiceTest())->run();