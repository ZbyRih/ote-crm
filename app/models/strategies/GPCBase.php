<?php

namespace App\Models\ABO;

use App\Extensions\Utils\Helpers\ArrayHash;

/**
 * @property string $Type
 *
 */
class GPCBase extends ArrayHash{

	const TYPE_REPORT = '074';

	const TYPE_ITEM = '075';

	const CODE_DEBET = 1;

	const CODE_KREDIT = 2;

	const CODE_DEBET_STRONO = 4;

	const CODE_KREDIT_STORNO = 5;
}
