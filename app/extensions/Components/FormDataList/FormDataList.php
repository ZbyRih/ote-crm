<?php

namespace App\Extensions\Components;

use App\Extensions\Components\Controls\DateInput;
use App\Extensions\Utils\Html;
use Cake\Collection\Collection;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\ChoiceControl;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Controls\TextArea;
use Nette\Utils\ArrayHash;

class FormDataList extends BaseComponent{

	/** @var BaseForm */
	private $form;

	private $fname;

	public function __construct(BaseForm $form){
		$this->form = $form;
	}

	public function render(){
		$c = $this->convert($this->reject(collection($this->form->getComponents(true))));

		$this->template->items = $c->toArray();

		parent::render();
	}

	private function reject(Collection $c){
		return $c->reject(
			function ($v, $k){
				if($v instanceof HiddenField){
					return true;
				}

				if($v instanceof Button){
					return true;
				}

				if(array_key_exists('view-hidden', $v->controlPrototype->attrs)){
					return true;
				}
				return false;
			});
	}

	private function convert(Collection $c){
		return $c->map(
			function ($c, $k){
				$val = '';
				$value = $c->getValue();

				if($c instanceof IControlView){
					$val = $c->viewFormat($value);
				}else if($c instanceof DateInput){
					$val = $value ? $value->format('j.n. Y') : '';
				}else if($c instanceof ChoiceControl){
					$items = $c->getItems();
					if(array_key_exists($value, $items)){
						$val = $items[$value];
					}
				}else if($c instanceof Checkbox){
					$val = $value ? 'ano' : 'ne';
				}else if($c instanceof TextArea){
					$val = Html::el('p')->style('white-space: pre;')
						->setText($value);
				}else{
					$val = $value;
				}

				return ArrayHash::from([
					'caption' => trim($c->caption, '*'),
					'value' => $val
				], false);
			});
	}
}