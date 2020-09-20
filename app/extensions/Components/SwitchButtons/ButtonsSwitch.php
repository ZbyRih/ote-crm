<?php

namespace App\Extensions\Components;

use App\Extensions\Utils\Html;

class ButtonsSwitch extends BaseComponent{

	/** @var BSHtmlButton[] */
	private $buttons;

	public function addButton(BSHtmlButton $button){
		$this->buttons[] = $button;
	}

	public function getButton($index){
		return $this->buttons[$index];
	}

	public function setActive($button, $active = true){
		foreach($this->buttons as $bi){
			if($bi == $button){
				$bi->setClass('active', $active);
			}
		}
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \App\Extensions\Components\BaseComponent::render()
	 */
	public function render(){
		$wrap = Html::el('span')->class('buttons-group');
		foreach($this->buttons as $bi){
			$wrap->addHtml($bi);
		}
		echo $wrap;
	}
}