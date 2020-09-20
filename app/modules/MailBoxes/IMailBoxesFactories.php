<?php

namespace App\Modules\MailBoxes\Factories;

use App\Modules\MailBoxes\Components\BoxContentComponent;

interface IBoxContentComponent{

	/**
	 *
	 * @return BoxContentComponent
	 */
	public function create();
}