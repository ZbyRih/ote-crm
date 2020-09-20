<?php
namespace App\Extensions\App\User\Mails;

use App\Extensions\App\Mailer;
use App\Models\Repositories\ParametersRepository;
use Nette\Application\UI\ITemplateFactory;
use Nette\Application\LinkGenerator;
use Nette\Localization\ITranslator;
use Nette\Application\IPresenterFactory;

class BaseMail{

	/** @var Mailer */
	private $mailer;

	/** @var ITemplateFactory */
	private $tplFac;

	/** @var LinkGenerator  */
	private $link;

	/** @var ITranslator */
	private $translator;

	/** @var ParametersRepository */
	private $options;

	/** @var IPresenterFactory */
	private $pf;

	public function __construct(Mailer $mailer, ITemplateFactory $tplFac, LinkGenerator $link, ITranslator $translator, ParametersRepository $options,
		IPresenterFactory $pf){
		$this->mailer = $mailer;
		$this->tplFac = $tplFac;
		$this->link = $link;
		$this->translator = $translator;
		$this->options = $options;
		$this->pf = $pf;
	}

	public function createTemplate($file, $presenter){
		$t = $this->tplFac->createTemplate();
		$t->getLatte()->addProvider('uiControl', $this->link);
		$t->getLatte()->addProvider('uiPresenter', $presenter);
		$t->setTranslator($this->translator);
		$t->setFile(__DIR__ . '/templates/' . $file);
		$t->options = $this->options;
		return $t;
	}

	protected function sendMail($tpl, $toMail, $toName, $subject){
		return $this->mailer->send($tpl, $toMail, $toName, $this->translator->translate($subject));
	}
}