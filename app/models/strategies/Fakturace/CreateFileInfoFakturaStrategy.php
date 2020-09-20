<?php

namespace App\Models\Strategies\Fakturace;

use App\Extensions\Utils\Helpers\ArrayHash;
use App\Models\Orm\Orm;
use App\Models\Orm\Faktury\FakturaEntity;
use App\Models\Repositories\ParametersRepository;
use App\Models\Strategies\KlientDetail\KlientDetailIdentityNameStrategy;
use App\Extensions\Utils\Strings;
use OBE_Strings;

/**
 * @property string $klient
 * @property string $cislo
 * @property string $eic
 * @property string $name
 * @property string $file
 * @property \DateTime $from
 *
 */
class FileInfoFaktura extends ArrayHash{
}

class CreateFileInfoFakturaStrategy{

	/** @var Orm */
	private $orm;

	/** @var ParametersRepository  */
	private $params;

	public function __construct(
		Orm $orm,
		ParametersRepository $params)
	{
		$this->orm = $orm;
		$this->params = $params;
	}

	public function create(
		FakturaEntity $fa)
	{
		$kl = $this->orm->klients->getById($fa->klientId);
		$om = $this->orm->odberMist->getById($fa->omId);

		$str = new KlientDetailIdentityNameStrategy();

		$fif = new FileInfoFaktura();
		$fif->klient = $this->clear($str->get($kl->klientDetailId));
		$fif->cislo = $fa->cis;
		$fif->eic = $om->eic;
		$fif->from = $fa->od;

		$strDir = new DirectoryFakturaStrategy($this->params);
		$strName = new FileNameFakturaStrategy();
		$dir = $strDir->get($fif);
		$fif->name = $strName->get($dir, $fif);
		$fif->file = $dir . $fif->name;

		return $fif;
	}

	private function clear(
		$name)
	{
		return str_replace([
			'/',
			'\\'
		], [
			'-',
			'-'
		], OBE_Strings::remove_diacritics(trim($name)));
	}
}