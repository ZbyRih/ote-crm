<?php

namespace App\Components\Service\Components;

use App\Extensions\Components\ModalDialog;
use App\Extensions\Utils\Arrays;
use Nette\DI\Container;
use Nette\Mail\IMailer;
use Nette\Utils\Validators;
use Nette\Application\ApplicationException;

class TestMailDlg extends ModalDialog{

	/** @var IMailer */
	private $mailer;

	private $backlink;

	private $from;

	private $name;

	public function __construct(Container $container, IMailer $mailer){
		parent::__construct('test_mail', 'Test email');

		$opts = $container->parameters['mail']['from'];

		$this->from = $opts['email'];
		$this->name = $opts['name'];

		$this->mailer = $mailer;
	}

	public function setBacklink($backlink){
		$this->backlink = $backlink;
		return $this;
	}

	public function createComponentForm(){
		$f = $this->createForm();

		$f->addHidden('backlink');
		$f->addEmail('email', 'Email')->setRequired('email musí být uveden');
		$f->addSubmit('save', 'Odeslat');

		$f->onSuccess[] = [
			$this,
			'onSuccess'
		];

		$f->onAnchor[] = function ($f){
			$f->setDefaults([
				'backlink' => $this->backlink
			]);
		};

		return $f;
	}

	public function onSuccess($f, $v){
		$mail = Arrays::remove('email', $v);
		$backlink = Arrays::remove('backlink', $v);

		if(!$presenter = $this->getPresenter()){
			throw new ApplicationException('Presenter is not set');
		}

		if(!$mail){
			$presenter->flashWarning('Email nebyl uveden.');
			$presenter->redirect('Default:');
			return;
		}

		if(!Validators::isEmail($mail)){
			$presenter->flashWarning('Email není validní.');
			$presenter->redirect('Default:');
			return;
		}

		$srcUrl = $presenter->getHttpRequest()->getUrl();

		$m = new TestMailMessage($this->from, $this->name, $mail, $srcUrl);

		$this->mailer->send($m);

		$presenter->flashSuccess('Email odeslán.');

		if($backlink){
			$presenter->restoreRequest($backlink);
		}

		$presenter->redirect('Default:');
	}
}