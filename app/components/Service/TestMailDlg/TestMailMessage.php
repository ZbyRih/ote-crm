<?php

namespace App\Components\Service\Components;

use Nette\Mail\Message;

class TestMailMessage extends Message{

	public function __construct($from, $fromName, $to, $src){
		parent::__construct();

		$this->setFrom($from, $fromName)
			->setSubject('[' . $fromName . '] Testovací email')
			->setHtmlBody('
					<div>
						<h4>Toto je testovací email</h4>
						<p>Test odeslán z ' . $src . '</p>
					</div>
				')
			->addTo($to);
	}
}