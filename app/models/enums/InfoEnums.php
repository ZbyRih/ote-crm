<?php

namespace App\Models\Enums;

class InfoEnums{

	const SUCCESS = 's';

	const INFO = 'i';

	const WARNING = 'w';

	const ERROR = 'e';

	const TYPE_OTE = 'o';

	const TYPE_BANK = 'b';

	static $MSG_CLASSES = [
		self::SUCCESS => 'alert-success',
		self::INFO => 'alert-info',
		self::WARNING => 'alert-warning',
		self::ERROR => 'alert-error'
	];

	static $TYPE_LABELS = [
		self::TYPE_BANK => 'platby',
		self::TYPE_OTE => 'ote zprávy'
	];
}