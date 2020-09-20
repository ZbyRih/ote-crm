<?php
namespace App\Models\Repositories;

use App\Models\Storages\SettingsCacheStorage;

/**
 * @property-read float $dph
 * @property-read float $dph_koef
 * @property-read float $dan_z_pln
 * @property-read string $faktury_cisl
 * @property-read string $fakskups_cisl
 * @property-read string $doklady_cisl
 * @property-read string $splatnost
 * @property-read string $uhrada
 * @property-read string $cislo_uctu
 * @property-read string $ident_a
 * @property-read string $ident_b
 * @property-read string $platby_mail_server
 * @property-read string $platby_mail_login
 * @property-read string $platby_mail_pass
 * @property-read string $platby_mail_folder
 * @property-read string $ote_mail_server
 * @property-read string $ote_mail_login
 * @property-read string $ote_mail_pass
 * @property-read string $ote_mail_folder
 * @property-read string $ote_cert_file
 * @property-read string $ote_cert_pass
 * @property-read string $ote_cert_valid
 * @property-read string $ote_cert_smime
 */
class SettingsRepository extends \Schematic\Entry{

	const BOX_OTE = 'ote_mail';

	const BOX_PLATBY = 'platby_mail';

	public function __construct(
		SettingsCacheStorage $stoSettings,
		ParametersRepository $repParameters)
	{
		$items = $stoSettings->indexed;

		if(isset($repParameters->devBankBox) && !getenv('PLATBY')){
			$items['platby_mail_server'] = $repParameters->devBankBox->server;
			$items['platby_mail_login'] = $repParameters->devBankBox->login;
			$items['platby_mail_pass'] = $repParameters->devBankBox->pass;
			$items['platby_mail_folder'] = $repParameters->devBankBox->folder;
		}

		if(isset($repParameters->devOteBox) && !getenv('OTE')){
			$items['ote_mail_server'] = $repParameters->devOteBox->server;
			$items['ote_mail_login'] = $repParameters->devOteBox->login;
			$items['ote_mail_pass'] = $repParameters->devOteBox->pass;
			$items['ote_mail_folder'] = $repParameters->devOteBox->folder;
		}

		parent::__construct($items);
	}
}