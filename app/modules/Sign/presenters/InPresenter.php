<?php

namespace App\Modules\Sign\Presenters;

use App\Extensions\App\User\Mails\ForgotPassword;
use App\Extensions\Components\BaseForm;
use App\Models\Repositories\UserRepository;
use App\Models\Tables\UserTable;
use Nette\Security\AuthenticationException;

class InPresenter extends BasePresenter{

	/** @var UserRepository @inject */
	public $userRep;

	/** @var UserTable @inject */
	public $users;

	/** @var ForgotPassword @inject */
	public $forgot;

	/** @var string @persistent */
	public $backlink;

	public function actionDefault($backlink = null){
		if($this->getUser()->isLoggedIn()){
			$this->goToHomePage();
		}
	}

	public function renderDefault(){
		$this->setLayout('sign');
	}

	public function createComponentLoginForm(){
		$f = $this->createForm();
		$f->addProtection();
		$f->addText('login', 'Login')->setRequired('Login musí být uveden.');
		$f->addPassword('pass', 'Heslo')->setRequired('Heslo musí být uvedeno.');
		$f->addSubmit('send', 'Přihlásit');
		$f->addSubmit('forgot', 'Zapomenuté heslo')->setValidationScope([
			$f['login']
		]);

		$f->onValidate[] = [
			$this,
			'onValidate'
		];

		$f->onSuccess[] = [
			$this,
			'onSuccess'
		];

		return $f;
	}

	public function onValidate(BaseForm $form, $vals){
		if(empty($vals['login'])){
			$form->addError('Login musí bý uveden.');
		}
	}

	public function onSuccess($form, $vals){
		if($form['forgot']->isSubmittedBy()){
			if($u = $this->userRep->findByLogin($vals['login'])){
				$this->forgot->send($this->userRep->createToken($u), $u->jmeno, $u->login, $this);
				$this->flashInfo('Na váš email byli zaslány instrukce pro změnu hesla.');
			}else{
				$this->flashWarning('Uživatel nenalezen.');
			}
			$this->goToIn();
		}

		try{
			$this->getUser()->login($vals['login'], $vals['pass']);

			$this->restoreRequest($this->backlink);
			$this->goToHomePage();
		}catch(AuthenticationException $e){
			$this->flashDanger($e->getMessage());
		}

		$this->goToIn();
	}
}