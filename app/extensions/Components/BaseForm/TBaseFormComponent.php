<?php

namespace App\Extensions\Components;

trait TBaseFormComponent{

	/** @var callable[] function($obj, bool $new) */
	public $onSave = [];

	/** @var callable[] function($obj) */
	public $onCancel = [];
}