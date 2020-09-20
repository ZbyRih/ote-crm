<?php

namespace App\Modules\Sign\Presenters;

use App\Extensions\Components\BaseForm;
use App\Models\Repositories\UserRepository;
use App\Models\Repositories\WrongToken;
use Nette\Application\ApplicationException;
use App\Extensions\Utils\ValidatePassword;

class ForgotPresenter extends BasePresenter{

	/** @var UserRepository @inject */
	public $users;

	public function actionDefault(
		$token = null)
	{
		try{
			$this->mUsers->checkToken($token);
			$this['forgotForm']->setValues([
				'token' => $token
			]);
		}catch(ApplicationException $e){
			$this->flashDanger($e->getMessage());
			$this->goToIn();
		}
	}

	public function renderDefault()
	{
		$this->setLayout('sign');
	}

	public function createComponentForgotForm()
	{
		$f = $this->createForm();
		$f->addProtection();
		$f->addHidden('token');
		$f->addPassword('n_pass', 'Heslo')
			->setRequired('Heslo musí být uvedeno.')
			->getControlPrototype()
			->setAttribute('data-pasword-strength', '8');
		$f->addPassword('n_pass2', 'Heslo znovu')->setRequired('Heslo musí být uvedeno.');
		$f->addSubmit('send', 'Změnit');
		$f->addSubmit('cancel', 'Zrušit')->setValidationScope([]);

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

	public function onValidate(
		BaseForm $f,
		$vals)
	{
		if($f->isSubmittedBy('cancel')){
			return;
		}

		$vp = new ValidatePassword();
		$vp->setField1('n_pass', 'Heslo');
		$vp->setField2('n_pass2', 'heslo znovu');
		$vp->setForm($f);
		$vp->setRequire(true);
		$vp->setVals($vals);
		$vp->setWeakPass(array_keys_exist('povolit_hlupacka_hesla', $this->options) ? $this->options->povolit_hlupacka_hesla : false);
		$vp->execute();
	}

	/**
	 *
	 * @param BaseForm $form
	 * @param array $vals
	 */
	public function onSuccess(
		BaseForm $form,
		$vals)
	{
		if($form->isSubmittedBy('cancel')){
			$this->goToIn();
		}

		try{
			$u = $this->users->checkToken($vals['token']);
			$this->users->changePass($u, $vals['n_pass']);
			$this->flashSuccess('Heslo bylo změněno.');
		}catch(WrongToken $e){
			$this->flashDanger($e->getMessage());
		}

		$this->goToIn();
	}
}