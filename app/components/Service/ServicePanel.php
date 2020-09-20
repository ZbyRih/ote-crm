<?php

namespace App\Components\Service;

use App\Extensions\Components\BaseComponent;
use Nette\Utils\Html;

class ServicePanel extends BaseComponent{

	/** @var string */
	private $name;

	/** @var array */
	private $items;

	/** @var array */
	private $buttons;

	/** @var \Closure */
	private $customRender;

	private $enable = true;

	private $getters;

	public function __construct($name, array $items, array $buttons = [], $customRender = null){
		$this->name = $name;
		$this->items = $items;
		$this->buttons = $buttons;
		$this->customRender = $customRender;
	}

	public function setEnable($enable = true){
		$this->enable = $enable;
		return $this;
	}

	public function getEnable(){
		return $this->enable;
	}

	public function render(){
		$panel = $this->createPanel($body = Html::el('div')->class('panel-body'));

		if($this->customRender){
			$body->addHtml($this->customRender->__invoke());
		}

		if($this->items){
			$body->addHtml($content = Html::el('dl')->class('dl-horizontal transform-none text-left text-muted'));

			foreach($this->items as $n => $v){
				$content->addHtml(Html::el('dt')->addText($n))
					->addHtml(Html::el('dd')->addText($v));
			}
		}

		$btnsGroup = null;
		if($this->buttons){
			$body->addHtml($btnsGroup = Html::el('div')->class('util-btn-margin-bottom-5'));
		}

		foreach($this->buttons as $a => $b){
			$btnsGroup->addHtml(
				Html::el('a')->class('btn mr-5 btn-sm btn-' . $b['kind'] . ' ' . $b['color'])
					->addAttributes([
					'role' => 'button',
					'href' => $this->link($a . '!'),
					'title' => $b['title']
				])
					->addText($b['title']))
				->addHtml('&nbsp;');
		}

		echo $panel;
	}

	private function createPanel($body){
		return Html::el('div')->class('col-md-4 col-xs-12')->addHtml(
			Html::el('div')->class('panel panel-default')
				->addHtml(Html::el('div')->class('panel-heading')
				->addText($this->name))
				->addHtml($body));
	}

	public function setGetters($getters){
		$this->getters = $getters;
		return $this;
	}

	public function getGetter($key){
		if(is_array($this->getters) && array_key_exists($key, $this->getters)){
			return call_user_func($this->getters[$key]);
		}
		return '';
	}
}