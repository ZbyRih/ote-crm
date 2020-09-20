<?php

require_once 'bootstrap.php';

OBE_Cli::writeBegin();

OBE_Cli::writeLn('	********************************************************************');
OBE_Cli::writeLn('	* Process and update all TYPE = A OTE GP6 body and make update.sql *');
OBE_Cli::writeLn('	********************************************************************');
OBE_Cli::writeLn('');

$F = new GP6FullWMailAndOM();
$fas = $F->FindAll([
	'type' => 'A'
]);

$hfile = fopen('update.sql', 'w+');

foreach($fas as $fa){

	$rawXml = file_get_contents('../../' . $fa['OTEMails']['file_xml']);

	list($h, $b) = (new OTEGasDataPofGP6())->process(simplexml_load_string($rawXml), $fa['GP6Head']['ote_id']);

	$gp6b = new GP6Body();

	$s = [
		'GP6Body' => [
			'id' => $fa['GP6Body']['id'],
			'data' => $b['GP6Body']['data']
		]
	];

	$gp6b->Save($s);

	fwrite(
		$hfile,
		'UPDATE tx_ote_invoice_body SET `data` = \'' . OBE_App::$db->escape_string($b['GP6Body']['data']) . '\' WHERE id = ' . $fa['GP6Body']['id'] . ";\r");

	OBE_Cli::writeLn('OTE.: ' . $fa['OTEMails']['ote_id'] . ' - update');
}

fclose($hfile);

OBE_Cli::writeEnd();