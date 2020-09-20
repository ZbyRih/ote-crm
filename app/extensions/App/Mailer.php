<?php
namespace App\Extensions\App;

use App\Models\Repositories\ParametersRepository;
use Nette\Mail\IMailer;
use Nette\Mail\SendException;
use Tracy\Debugger;
use Tracy\ILogger;

class Mailer{

	/** @var IMailer */
	private $mailer;

	/** @var LoggerSection */
	private $logger;

	private $mailerFrom;

	private $mailerName;

	function __construct(IMailer $mailer, ParametersRepository $options, ILogger $logger){
		$from = $options->mail['from'];
		$this->mailerFrom = $from['email'];
		$this->mailerName = $from['name'];
		$this->mailer = $mailer;
		$this->logger = $logger->getSection('emails');
	}

	/**
	 *
	 * @param \Nette\Bridges\ApplicationLatte\Template $tpl
	 * @param string $toMail
	 * @param string $toName
	 * @param string $subject
	 */
	public function send($tpl, $toMail, $toName, $subject){
		$log = function ($state, $message = '', $msgId = null) use ($subject, $toMail){
			$this->logger->log([
				$state,
				$message,
				$subject,
				$toMail
			]);
		};

		if(!\Nette\Utils\Validators::isEmail($toMail)){
			$log('FALSE', 'invalid email');
			return false;
		}
		try{

// 			$m = new \App\Extensions\Utils\Helpers\Message();

			$m->addTo(trim($toMail), $toName)
				->setFrom($this->mailerFrom, $this->mailerName)
				->setSubject($subject)
				->setHtmlBody((string) $tpl);

			$this->mailer->send($m);

			$log('TRUE', 'send');
			return true;
		}catch(SendException $e){
			$log('ERROR', 'SendException: ' . $e->getMessage());
			Debugger::log($e, Debugger::ERROR);
		}catch(\Exception $e){
			$log('ERROR', 'Exception: ' . $e->getMessage());
			Debugger::log($e, Debugger::ERROR);
		}
		return false;
	}
}