<?php

namespace App\Components\Tisk\Zaloha;

use Schematic\Entry;
use App\Extensions\Abstracts\TArrayAccess;

/**
 * @property-read int $month
 * @property-read \DateTimeInterface $splatno
 * @property-read string $name
 * @property-read string $com
 * @property-read float $zakl
 * @property-read float $dph
 * @property-read float $vyse
 * @property-read float $uhrazeno
 * @property-read int $omId
 *
 */
class ZalohaItemEntry extends Entry implements \ArrayAccess{
	use TArrayAccess;
}