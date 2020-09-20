<?php
namespace App\Modules\Role\Components;

use App\Extensions\App\User\Authorizator;
use App\Extensions\Components\BaseComponent;
use App\Extensions\Utils\Arrays;
use App\Extensions\Utils\Strings;
use App\Models\Repositories\ParametersRepository;
use App\Models\Storages\RolesCacheStorage;
use App\Models\Tables\RoleTable;
use Kdyby\Translation\Translator;
use Nette\Database\Table\ActiveRow;
use Cake\Utility\Hash;

class EditRole extends BaseComponent{

	/** @var Translator */
	private $trans;

	/** @var RoleTable */
	private $roleTbl;

	/** @var RolesCacheStorage */
	private $roleStorage;

	/** @var Authorizator */
	private $acl;

	/** @var ActiveRow */
	private $role;

	private $privilege;

	private $defaultViews;

	public function __construct(ParametersRepository $options, Translator $trans, RoleTable $roleTbl, RolesCacheStorage $roleStorage, Authorizator $acl){
		$this->acl = $acl;
		$this->trans = $trans;
		$this->roleTbl = $roleTbl;
		$this->roleStorage = $roleStorage;

		$this->privilege = $options->privilege;
		$this->defaultViews = $options->defaultViews;
	}

	public function setRole($role){
		$this->role = $role;
		return $this;
	}

	public function createComponentEditForm(){
		$f = $this->createForm();

		$gm = $f->addGroup();
		$gm->setOption('container', \Nette\Utils\Html::el('div')->class(''));

		$f->addHidden('id');
		$f->addText('nazev', 'Název role')->setRequired();

		$resources = $this->acl->getResources();

		$avaible = Hash::filter($resources, function ($v){
			return !($v == 'Sign' || Strings::contains($v, '_'));
		});

		$mods = [
			null => '- nevybráno -'
		];

		foreach($avaible as $r){
			$dv = $r;
			if(array_key_exists($r, $this->defaultViews)){
				$dv = $this->defaultViews[$r];
			}
			$mods[$dv] = $this->trans->translate('app.menu.' . $r);
		}

		$f->addSelect('home', 'Výchozí modul', $mods)->checkDefaultValue(false);

		$f->addComponent((new RoleControl())->build($resources, $this->privilege, [
			'Sign'
		], $this->user->isInRole('super')), 'priviledge');

		$f->addSubmit('save', 'Uložit');
		$f->addSubmit('cancel', 'Zrušit')->setValidationScope([])->onClick[] = function (){
			$this->presenter->redirect('Default:');
		};

		if($this->role){
			$f->setDefaults($this->role->toArray() + [
				'priviledge' => RoleTable::permsUnpack($this->role->perms)
			]);
		}

		$f->onSuccess[] = [
			$this,
			'onSuccess'
		];

		return $f;
	}

	public function onSuccess($form, $v){
		$id = Arrays::remove('id', $v);
		$p = Arrays::remove('priviledge', $v);

		$v['perms'] = RoleTable::permsPack((array) $p);

		if($id){
			$this->roleTbl->update($id, $v);
			$this->roleStorage->invalidate();
			$this->presenter->flashSuccess('Role uložena.');
		}else{
			$this->roleTbl->insert($v);
			$this->roleStorage->invalidate();
			$this->presenter->flashSuccess('Role přidána.');
		}
		$this->presenter->redirect('Default:');
	}
}