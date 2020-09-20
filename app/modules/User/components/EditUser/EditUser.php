<?php
namespace App\Modules\User\Components;

use App\Extensions\Components\BaseComponent;
use App\Extensions\Components\BaseForm;
use App\Extensions\Helpers\Helpers;
use App\Models\Tables\UserTable;
use App\Models\Repositories\RoleRepository;
use App\Models\Repositories\UserRepository;
use Nette\Database\Table\ActiveRow;
use App\Models\Tables\RoleTable;
use App\Models\Repositories\ParametersRepository;
use App\Extensions\Utils\Arrays;
use App\Extensions\Utils\ValidatePassword;

class EditUser extends BaseComponent{

	/** @var RoleRepository */
	private $roleRep;

	/** @var UserTable */
	private $userTbl;

	/** @var UserRepository */
	private $userRep;

	/** @var ActiveRow */
	private $usr;

	/** @var ParametersRepository */
	private $options;

	public function __construct(
		UserTable $userTpl,
		UserRepository $userRep,
		RoleRepository $roleRep,
		ParametersRepository $options)
	{
		$this->userTbl = $userTpl;
		$this->userRep = $userRep;
		$this->roleRep = $roleRep;
		$this->options = $options;
	}

	public function setUser(
		$usr)
	{
		$this->usr = $usr;
	}

	public function createComponentEditForm()
	{
		$roles = $this->roleRep->getNames([
			'guest'
		] + ($this->user->isInRole('super') ? [] : [
			'super'
		]));

		$f = $this->createForm();
		$f->addHidden('id');
		$f->addText('login', 'Login')
			->addCondition(\Nette\Forms\Form::EMAIL)
			->setRequired();
		$f->addText('jmeno', 'Jméno')->setRequired();
		$f->addSelect('role', 'Role')
			->setItems($roles)
			->setRequired();
		$f->addPassword('n_pass', 'Heslo')->setAttribute('data-pasword-strength', '8');
		$f->addPassword('n_pass2', 'Heslo znovu');

		$f->addSubmit('save', 'Uložit');
		$f->addSubmit('cancel', 'Zrušit')->setValidationScope([])->onClick[] = function (){
			$this->presenter->redirect('Default:');
		};

		if($this->usr){
			$f->setDefaults($this->usr);
		}

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
		$vp = new ValidatePassword();
		$vp->setField1('n_pass', 'Heslo');
		$vp->setField2('n_pass2', 'heslo znovu');
		$vp->setForm($f);
		$vp->setRequire(false);
		$vp->setVals($vals);
		$vp->setWeakPass(array_key_exists('povolit_hlupacka_hesla', $this->options) ? $this->options->povolit_hlupacka_hesla : false);
		$vp->execute();

		$id = (int) $vals['id'];
		if(!$this->userRep->checkLogin($vals['login'], $id)){
			$f->addError('Login je již použit, zvolte jiný.');
		}
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
		$id = Arrays::remove('id', $vals);

		if($pass = Helpers::passwordHash($vals['n_pass'])){
			$vals['pass'] = $pass;
		}

		unset($vals['n_pass']);
		unset($vals['n_pass2']);

		if($id){
			$this->userTbl->update($id, $vals);
			$this->presenter->flashSuccess('Uživatel uložen.');
		}else{
			$vals['perms'] = RoleTable::permsPack([]);
			$vals = $this->userTbl->insert($vals);
			$this->presenter->flashSuccess('Uživatel přidán.');
			$this->presenter->redirect('Default:');
		}

		$this->presenter->redirect('Edit:', $id);
	}
}