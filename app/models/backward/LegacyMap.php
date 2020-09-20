<?php

namespace App\Models\Backward;

use App\Models\Repositories\SettingsRepository;

class LegacyMap{

	public static function getKomunikace(
		SettingsRepository $s)
	{
		return [
			'cert_priv_ote' => '/' . $s->ote_cert_file,
			'cert_priv_valid_to' => $s->ote_cert_valid,
			'cert_priv_pass' => $s->ote_cert_pass,
			'cert_ote' => '/' . $s->ote_cert_smime, //config/certs/ote-smime.pem
			'mail_ote_server' => $s->ote_mail_server,
			'mail_ote_user' => $s->ote_mail_login,
			'mail_ote_folder' => $s->ote_mail_folder,
			'mail_ote_pass' => $s->ote_mail_pass,
			'mail_banka_server' => $s->platby_mail_server,
			'mail_banka_user' => $s->platby_mail_login,
			'mail_banka_folder' => $s->platby_mail_folder,
			'mail_banka_pass' => $s->platby_mail_pass
		];
	}

	public static function getFront(
		SettingsRepository $s)
	{
		return [
			'CISELNIK_FAK_SKUP' => $s->fakskups_cisl, // 555NNNNNNN
			'CISELNIK_FAKTUR' => $s->faktury_cisl, // 21PPNNNN
			'CISELNIK_DOKLAD_PRIJEM' => $s->doklady_cisl, // 5YYYYNNNNN
			'DEN_SPLATNOSTI' => $s->splatnost, // 12
			'DPH' => $s->dph, // 21
			'DAN_Z_PLN' => $s->dan_z_pln, // 30.60
			'DPH_KOEF' => $s->dph_koef, // 0.1736
			'UHRADA' => $s->uhrada, // bankovním převodem
			'CU_OBCHODNIKA' => $s->cislo_uctu, //
			'OBCHODNIK_IDENT_A' => $s->ident_a, //
			'OBCHODNIK_IDENT_B' => $s->ident_b //
		];
	}

	public static function getResource()
	{
		return [
			80 => 'Cron',
			22 => 'Klients',
			54 => 'FakSkups',
			50 => 'OdberMist',
			51 => 'Zalohy',
			56 => 'Faktury',
			52 => 'OteGP6',
			65 => 'Pohyby',
			53 => 'Platby',
			70 => 'OteZpravy',
			55 => 'Activity',
			75 => 'Info',
			13 => 'Nastaveni',
			18 => 'User',
			40 => 'Tags',
			41 => 'Helper'
		];
	}
}