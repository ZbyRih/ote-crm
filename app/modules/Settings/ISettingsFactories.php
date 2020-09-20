<?php

namespace App\Modules\Settings;

use App\Modules\Settings\Components\SettingsEditForm;

interface IComponentEditForm{

	/**
	 *
	 * @return SettingsEditForm
	 */
	public function create();
}