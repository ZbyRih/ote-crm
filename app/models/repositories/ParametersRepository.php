<?php

namespace App\Models\Repositories;

use Schematic\Entry;

/**
 * @property-read string $root
 * @property-read string $backup
 * @property-read string $undecrypted
 * @property-read string $others
 * @property-read string $xmlMessages
 * @property-read string $xmlUnknown
 */
class OTEDirsParameters extends Entry{
}

/**
 * @property-read string $readed
 * @property-read string $others
 */
class OTEBoxesParameters extends Entry{
}

/**
 * @property-read string $root
 * @property-read string $incomes
 * @property-read string $others
 */
class BankDirsParameters extends Entry{
}

/**
 * @property-read string $incomes
 * @property-read string $others
 */
class BankBoxesParameters extends Entry{
}

/**
 * @property-read string $server
 * @property-read string $login
 * @property-read string $pass
 * @property-read string $folder
 *
 */
class MailBoxSettings extends Entry{
}

/**
 * @property-read string $autor
 * @property-read string $title
 * @property-read string $namespace
 * @property-read string $basePath
 * @property-read array $mail
 * @property-read array $service
 * @property-read array $privilege
 * @property-read array $defaultViews
 * @property-read boolean $povolit_hlupacka_hesla
 * @property-read boolean $statsLog
 * @property-read OTEDirsParameters $oteDirs
 * @property-read OTEBoxesParameters $oteBoxes
 * @property-read BankDirsParameters $bankDirs
 * @property-read BankBoxesParameters $bankBoxes
 * @property-read MailBoxSettings $devOteBox
 * @property-read MailBoxSettings $devBankBox
 * @property-read string $fakturyDir
 * @property-read boolean $mailManipulate
 */
class ParametersRepository extends Entry{

	protected static $associations = [
		'oteDirs' => OTEDirsParameters::class,
		'oteBoxes' => OTEBoxesParameters::class,
		'bankDirs' => BankDirsParameters::class,
		'bankBoxes' => BankBoxesParameters::class,
		'devOteBox' => MailBoxSettings::class,
		'devBankBox' => MailBoxSettings::class
	];
}