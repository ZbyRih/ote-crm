<?php

namespace App\Modules\Role\Components;

use App\Extensions\Components\BaseControl;
use App\Models\Repositories\RoleRepository;
use Cake\Utility\Hash;
use Nette\Application\UI\Control;
use Nette\Forms\IControl;
use Nette\Forms\Controls\RadioList;
use App\Extensions\Components\Container;

/**
 *
 * @property \Nette\Bridges\ApplicationLatte\Template $template
 *
 */
class RoleControl extends Control implements IControl{
	use BaseControl;

	private $super;

	public function __construct(){
		$this->BaseControl();
	}

	public function build($resources, $priviledges, $except = [], $super = false){
		$this->super = $super;

		foreach($resources as $r){
			if(in_array($r, $except)){
				continue;
			}

			$rl = new RadioList('app.menu.' . $r, RoleRepository::$modulPravaOpt);
			$rl->setDefaultValue('0');
			$this->styleRadioList($rl);

			$this->addComponent($rl, $r);

			if(array_key_exists($r, $priviledges)){

				$privs = Hash::filter($priviledges[$r],
					function ($v) use ($r, $super){
						if($super){
							return true;
						}
						if($r == 'User' && $v == 'relog'){
							return false;
						}
						if($r == 'Role' && $v == 'change'){
							return false;
						}
					});

				if(!$privs){
					continue;
				}

				$this->addComponent($s = new Container(), $r . '_priv');

				$this->addPrivs($s, $r, $privs);
			}
		}

		return $this;
	}

	private function addPrivs($s, $resource, $privs){
		foreach($privs as $p){
			$rl = $s->addRadioList($p, 'app.priviledge.' . $resource . '.' . $p, RoleRepository::$privilegiaOpts)->setDefaultValue('0');
			$this->styleRadioList($rl);
		}
	}

	private function styleRadioList($rl){
		$rl->getSeparatorPrototype()->setName(null);
		$rl->getItemLabelPrototype()->setAttribute('class', 'radio-inline radio-styled');
		$rl->getContainerPrototype()
			->setName('div')
			->setAttribute('class', '');
	}

	public function getValue(){
		$r = [];
		foreach($this->getComponents() as $c){
			$cName = $c->getName();
			if($c instanceof Container){
				$r[$cName] = $c->getValues();
				continue;
			}
			$r[$cName] = $c->getValue();
		}
		return $r;
	}

	public function setValue($value){
		if(!is_array($value)){
			return;
		}

		foreach($this->getComponents() as $c){
			$cName = $c->getName();
			if(!array_key_exists($cName, $value)){
				continue;
			}

			if($c instanceof Container){
				$c->setValues($value[$cName]);
				continue;
			}
			$c->setValue($value[$cName]);
		}
	}

	public function getLabel(){
		return null;
	}

	public function getControl(){
		return $this->render();
	}

	public function render(){
		$this->template->items = $this->getComponents();
		$this->template->super = $this->super;

		return $this->template->setFile(__DIR__ . '/default.latte')->renderToString();
	}
}