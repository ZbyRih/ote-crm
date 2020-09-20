<?php

namespace App\Extensions\Components;

use App\Extensions\Components\Renderer\BaseFormRenderer;
use App\Extensions\Components\Renderer\IFormRendererExtension;
use App\Extensions\Utils\Helpers\ClassNames;
use App\Extensions\Utils\Strings;
use Nette\Forms\Controls;
use Nette\Utils\Html;
use App\Extensions\Components\Renderer\IFormButtonsRendererExtension;
use App\Extensions\Components\Renderer\ButtonsBaseFormRenderer;

class BaseForm extends \Nette\Application\UI\Form{

	use TContainerControls;

	/** @var IFormRendererExtension[] */
	private $rendererExtensions = [];

	/** @var IFormButtonsRendererExtension */
	private $buttonsRendereExtension;

	/** @var ClassNames */
	private $classes;

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL){
		parent::__construct($parent, $name);

		$this->setRenderer(new BaseFormRenderer());
		$this->classes = new ClassNames([
			'form-horizontal' => true,
			'form-inline' => false,
			'form-small' => false,
			'ajax' => false
		]);

		$this->buttonsRendereExtension = new ButtonsBaseFormRenderer();
	}

	public function addRendererExtension(IFormRendererExtension $extension){
		$this->rendererExtensions[] = $extension;
	}

	public function setButtonRendererExtension(IFormButtonsRendererExtension $extension){
		$this->buttonsRendereExtension = $extension;
	}

	protected function beforeRender(){
		parent::beforeRender();

		// setup form rendering
		$renderer = $this->getRenderer();
		foreach($this->rendererExtensions as $ext){
			$ext->execute($renderer);
		}

		$this->getElementPrototype()->setAttribute('class', (string) $this->classes);

		if($this->buttonsRendereExtension){
			$this->extendButtonsStyles();
		}
		$this->extendControlsStyles();
		$this->attachErrors();
	}

	private function extendControlsStyles(){
		$small = $this->classes->has('form-small');
		foreach($this->getControls() as $control){
			if($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox){
				$cp = $control->getControlPrototype();
				$cp->addClass('form-control' . ($small ? ' input-sm' : ''));
			}
		}
	}

	private function extendButtonsStyles(){
		$usedPrimary = false;
		$buttons = $this->getComponents(false, Controls\Button::class);

		foreach($buttons as $b){
			$cp = $b->getControlPrototype();

			if($usedPrimary){
				$this->buttonsRendereExtension->styleDefaultButton($cp);
			}else{
				$this->buttonsRendereExtension->stylePrimaryButton($cp);
				$usedPrimary = true;
			}

			if(Strings::startsWith($b->getName(), 'save')){
				$this->buttonsRendereExtension->styleSaveButton($cp, $b->caption);
			}

			if(Strings::startsWith($b->getName(), 'cancel')){
				$this->buttonsRendereExtension->styleCancelButton($cp, $b->caption);
			}
		}
	}

	private function attachErrors(){
		if(!$this->hasErrors()){
			return;
		}

		$errors = Html::el('ul')->class('alert alert-danger');

		foreach($this->getErrors() as $e){
			$errors->addHtml(Html::el('li')->setText($e));
		}

		// musim nahradit element za vlastni a u nej pretizit getStartTag a za nej vygenerovat chybova hlaseni
		$this->getElementPrototype()->addHtml($errors);
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nette\Forms\Container::addContainer()
	 */
	public function addContainer($name){
		$control = new Container();
		$control->currentGroup = $this->currentGroup;
		if($this->currentGroup !== NULL){
			$this->currentGroup->add($control);
		}
		return $this[$name] = $control;
	}

	public function addAttributes($attrs){
		return $this->getElementPrototype()->addAttributes($attrs);
	}

	/**
	 * form inline
	 * @return BaseForm
	 */
	public function makeInline(){
		$this->classes->set('form-horizontal', false);
		$this->classes->set('form-inline', true);
		return $this;
	}

	/**
	 * malé controly
	 * @return BaseForm
	 */
	public function makeSmall(){
		$this->classes->set('form-small', true);
		return $this;
	}

	/**
	 * ajaxově zpracovávanej formulář
	 * @return BaseForm
	 */
	public function makeAjax(){
		$this->classes->set('ajax', true);
		return $this;
	}

	public function isSubmittedBy($key){
		if(!$this->isSubmitted()){
			return false;
		}
		if(!$this->offsetExists($key)){
			return false;
		}
		return $this[$key]->isSubmittedBy();
	}
}