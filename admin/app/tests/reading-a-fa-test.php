<?php

require_once 'bootstrap.php';

OBE_Cli::writeBegin();

OBE_Cli::writeLn('	****************************************************************');
OBE_Cli::writeLn('	* Test all type = A GP6 data by process them and make invoices *');
OBE_Cli::writeLn('	****************************************************************');
OBE_Cli::writeLn('');

$F = new GP6Full();
$fas = $F->FindAll([
	'type' => 'A'
]);

foreach($fas as $fa){
	$OM = new MOTEMails();
	$m = $OM->FindOneBy('ote_id', $fa['GP6Head']['ote_id']);

	$rawXml = file_get_contents('../../' . $m[$OM->name]['file_xml']);
	$xml = simplexml_load_string($rawXml);

	$OTE = new OTEGasDataPofGP6();
	list($h, $b) = $OTE->process($xml, $fa['GP6Head']['ote_id']);

	$dt = unserialize($b['GP6Body']['data']);

	$h['GP6Head']['id'] = 1;
	$b['GP6Body']['id'] = 1;

	try{
		$fak = new OTEFaktura();
		$fak->setCislo('pokus');
		$fak->loadArr([
			array_merge($h, $b)
		], false);

		$fak->build();
		$fak->render();
	}catch(FakturaException $e){
		OBE_Cli::writeLn('OTE.: ' . $m['OTEMails']['ote_id'] . ' - ' . $fa['GP6Head']['pofId'] . ' - fail: ' . $e->getMessage());
		continue;
	}
	OBE_Cli::writeLn('OTE.: ' . $m['OTEMails']['ote_id'] . ' - ' . $fa['GP6Head']['pofId'] . '  - ok');

// 	break;
}

OBE_Cli::writeEnd();