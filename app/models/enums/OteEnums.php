<?php

namespace App\Models\Enums;

class OteEnums{

	public static $SEGMENT = [
		'INV' => 'Pravidelná', // 'Pravidelná fakturace',
		'COR' => 'Opravná', // 'Opravná fakturace (od 1.7.2011 nahrazeno hodnotou atributu corReason)',
		'EXI' => 'Mimořádná', // 'Mimořádná fakturace',
		'CAN' => 'Storno', // 'Storno fakturace',
		'EOC' => 'Ukončení sml.', // 'Ukončení smlouvy',
		'HST' => 'Historické' // 'Historické hodnoty fakturace'
	];

	public static $COR_REASON = [
		'01' => 'Oprava na základě chyby zjištěné provozovatelem distribuční soustavy',
		'02' => 'Oprava na základě reklamace'
	];
}