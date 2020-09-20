<?php
namespace App\Modules\User\Components;

use App\Extensions\App\User\Authorizator;
use App\Extensions\Components\BaseComponent;
use App\Models\Repositories\ParametersRepository;
use App\Models\Tables\RoleTable;
use App\Models\Tables\UserTable;
use Kdyby\Translation\Translator;
use Nette\Database\Table\ActiveRow;
use App\Modules\Role\Components\RoleControl;

class EditPrava extends BaseComponent{

	/** @var Translator */
	private $trans;

	/** @var Authorizator */
	private $acl;

	/** @var UserTable */
	private $userTbl;

	/** @var RoleTable */
	private $roleTbl;

	/** @var array */
	private $privilege;

	/** @var ActiveRow */
	private $usr;

	public function __construct(
		ParametersRepository $options,
		Translator $trans,
		RoleTable $roleTbl,
		Authorizator $acl,
		UserTable $user)
	{
		$this->acl = $acl;
		$this->trans = $trans;
		$this->roleTbl = $roleTbl;
		$this->userTbl = $user;
		$this->privilege = $options->privilege;
	}

	public function setUser(
		$user)
	{
		$this->usr = $user;
	}

	public function createComponentEditForm()
	{
		$f = $this->createForm();

		$gm = $f->addGroup();
		$gm->setOption('container', \Nette\Utils\Html::el('div')->class(''));

		$f->addHidden('id');

		$f->addComponent((new RoleControl())->build($this->acl->getResources(), $this->privilege, [
			'Sign'
		], $this->user->isInRole('super')), 'priviledge');

		$f->addSubmit('save', 'Uložit');
		$f->addSubmit('cancel', 'Zrušit')->setValidationScope(false)->onClick[] = function (){
			$this->presenter->redirect('Default:');
		};

		if($this->usr){
			$rl = $this->roleTbl->findOne('role', $this->usr->role);
			$rp = RoleTable::permsUnpack($rl['perms']);

			$a = [];
			$r = [];
			$p = empty($this->usr->perms) ? [] : RoleTable::permsUnpack($this->usr->perms);

			foreach($rp as $k => $s){
				if(is_array($s)){
					foreach($s as $kk => $t){
						$r[$k][$kk] = isset($p[$k][$kk]) ? $p[$k][$kk] : $t;
					}
				}else{
					$r[$k] = isset($p[$k]) ? $p[$k] : $s;
				}
			}

			$a['id'] = $this->usr->id;
			$a['priviledge'] = $r;

			$f->setDefaults($a);
		}

		$f->onSuccess[] = [
			$this,
			'onSuccessEditPrava'
		];

		return $f;
	}

	public function onSuccessEditPrava(
		$form,
		$v)
	{
		$id = $v['id'];
		$p = $v['priviledge'];

		if(!$u = $this->userTbl->find($id)){
			$this->presenter->flashWarning('Uživatel nenalezen.');
			$this->presenter->redirect('this');
		}

		if(!$rl = $this->roleTbl->findOne('role', $u->role)){
			$this->presenter->flashWarning('Role nenalezena.');
			$this->presenter->redirect('this');
		}

		$rp = RoleTable::permsUnpack($rl['perms']);

		if(!is_array($rp)){
			$rp = [];
		}

		$r = [];

		foreach($rp as $k => $s){
			if(is_array($s)){
				foreach($s as $kk => $t){
					if(array_key_exists($k, $p) && $p[$k][$kk] != $t){
						$r[$k][$kk] = $p[$k][$kk];
					}
				}
			}else if(array_key_exists($k, $p) && $p[$k] != $s){
				$r[$k] = $p[$k];
			}
		}

		$u->update([
			'perms' => RoleTable::permsPack($r)
		]);

		$this->presenter->flashSuccess('Práva uložena.');
		$this->presenter->redirect('this');
	}
}