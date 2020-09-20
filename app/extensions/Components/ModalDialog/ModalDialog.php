<?php

namespace App\Extensions\Components;

class ModalDialog extends BaseComponent{

	protected $id;

	protected $title;

	protected $hidden;

	public function __construct($id, $title, $hidden = true){
		$this->id = $id;
		$this->title = $title;
		$this->hidden = $hidden;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \App\Extensions\Components\BaseComponent::createTemplate()
	 */
	public function createTemplate(){
		$t = parent::createTemplate();

		$t->getLatte()->addProvider('coreParentFinder', function ($t){
			if(!$t->getReferenceType()){
				return __DIR__ . '/default.latte';
			}
		});

		$t->modalId = $this->id;
		$t->modalTitle = $this->title;
		$t->modalHidden = $this->hidden;

		return $t;
	}
}