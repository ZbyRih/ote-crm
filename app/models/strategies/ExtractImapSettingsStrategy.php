<?php

namespace App\Models\Strategies;

use App\Models\DTO\ImapSettings;
use App\Models\Repositories\SettingsRepository;
use App\Models\Values\MailBoxValue;

class ExtractImapSettingsStrategy{

	public function get(
		SettingsRepository $settings,
		$box)
	{
		$s = new ImapSettings();

		$server = new MailboxValue($settings->{$box . '_server'});
		$folder = new MailBoxValue($settings->{$box . '_folder'});

		$s->server = $server->getServer();
		$s->folder = $folder->getFolder();
		$s->login = $settings->{$box . '_login'};
		$s->pass = $settings->{$box . '_pass'};

		return $s;
	}
}