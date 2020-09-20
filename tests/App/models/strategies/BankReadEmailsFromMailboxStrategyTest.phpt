<?php

namespace Tests\App\Models\Strategies;

require_once __DIR__ . '/../../../bootstrap.php';

use App\Models\Orm\Orm;
use App\Models\Strategies\BankReadEmailsFromMailboxStrategy;
use App\Models\Services\InfoService;
use App\Models\Repositories\ParametersRepository;
use App\Models\DTO\ImapSettings;
use App\Models\Enums\InfoEnums;
use App\Models\Services\ImapClientService;
use App\Models\Repositories\BankBoxesParameters;
use Nette\Mail\IMailer;
use Tester\Assert;
use Tests\Utils\IntegrationTestCase;

class BankReadEmailsFromMailboxStrategyTest extends IntegrationTestCase{

	/** @var IMailer */
	private $mailer;

	/** @var InfoService */
	private $info;

	/** @var ParametersRepository */
	private $params;

	/** @var ImapSettings */
	private $settings;

	public function __construct()
	{
		$this->getContainer()->getByType(Orm::class);
		$this->mailer = $this->getContainer()->getByType(IMailer::class);
		$this->info = $this->getContainer()->getByType(InfoService::class);
		$this->params = $this->getContainer()->getByType(ParametersRepository::class);

		$this->settings = new ImapSettings();
		$this->settings->server = '{localhost:143/notls/novalidate-cert}';
		$this->settings->folder = 'INBOX';
		$this->settings->login = 'traxell-banka@xb1.local';
		$this->settings->pass = 'Admin';
	}

	public function setUp()
	{
		for($i = 1; $i < 5; $i++){

			$cnt = file_get_contents(__DIR__ . '/data/seed-platby-' . $i . '.eml');
			$elm = explode("\r\n\r\n\r\n\r\n", $cnt);
			$head = array_shift($elm);
			$matches = [];
			$match = preg_match('/Subject: .*/i', $head, $matches);
			$subject = imap_mime_header_decode(trim($matches[0]));
			$body = $elm[0];

			mail($this->settings->login, trim($matches[0]), $body, $head);
		}
	}

	public function tearDown()
	{
		$imap = new ImapClientService();
		$imap->connect($this->settings);

		$year = date('Y');
		$root = $this->settings->server . $this->settings->folder;

		$boxes = new BankBoxesParameters(
			[
				'others' => $root . '.' . $this->params->bankBoxes->others . '.' . $year,
				'incomes' => $root . '.' . $this->params->bankBoxes->incomes . '.' . $year
			]);

		$imap->switchFolder($root);

		$imap->removeFolder($boxes->incomes);
		$imap->removeFolder($boxes->others);

		$mails = $imap->getMailIds();
		foreach($mails as $id){
			$imap->delete($id);
		}

		$imap->expunge();
		$imap->close();
	}

	public function testMailboxRead()
	{
		$info = $this->info->createObj(InfoEnums::TYPE_BANK);

		$str = new BankReadEmailsFromMailboxStrategy();
		$str->setInfo($info);
		$str->setParams($this->params);
		$str->setSettings($this->settings);

		$result = $str->read();

		Assert::true(!empty($result));
		Assert::true(true);
	}
}

(new BankReadEmailsFromMailboxStrategyTest())->run();