<?php

namespace App\Modules\Ciselniky\Presenters;

use App\Extensions\Components\ButtonsSwitch;
use App\Extensions\Components\BSHtmlButton;

class BasePresenter extends \App\Presenters\BasePresenter{

	public function getResource()
	{
		return 'Ciselniky';
	}

	public function createComponentSwitch()
	{
		$bs = new ButtonsSwitch();
		$bs->addButton(new BSHtmlButton('Položky číselníků', 'btn btn-primary btn-raised', $this->link('Default:'), 'md md-list'));
		$bs->addButton(new BSHtmlButton('Skupiny', 'btn btn-primary btn-raised', $this->link('Groups:'), 'md md-group-work'));
		return $bs;
	}
}