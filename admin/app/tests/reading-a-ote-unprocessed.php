<?php

require_once 'bootstrap.php';

OBE_Cli::writeBegin();

OBE_Cli::writeLn('	********************************************************************');
OBE_Cli::writeLn('	* Check all GP6 emails and try proccess them if xml file not exist *');
OBE_Cli::writeLn('	********************************************************************');
OBE_Cli::writeLn('');

$OM = new MOTEMails();
$ms = $OM->FindAll([
	'processed' => 0,
	'ote_kod' => 'GP6'
]);

foreach($ms as $m){
	dd($m);
// 	$OM = new MOTEMails();
// 	$m = $OM->FindOneBy('ote_id', $fa['GP6Head']['ote_id']);

	if(!$m[$OM->name]['file_xml']){
		OBE_Cli::writeLn('OTE.: ' . $m['OTEMails']['ote_id'] . ' - file not saved');
		continue;
	}

	$hfile = '../../' . $m[$OM->name]['file_xml'];

	if(!file_exists($hfile)){
		OBE_Cli::writeLn('OTE.: ' . $m['OTEMails']['ote_id'] . ' - file not exist');
		continue;
	}else{
		$rawXml = file_get_contents($hfile);
		$xml = simplexml_load_string($rawXml);

		$OTE = new OTEGasDataPofGP6();
		list($h, $b) = $OTE->process($xml, $m['OTEMails']['ote_id']);

		dd($b);

		OBE_Cli::writeLn('OTE.: ' . $m['OTEMails']['ote_id'] . ' - type: ' . $b['GP6Body']['type'] . ' - body create');

// 		$gp6Head = new GP6Head();
		// 		$gp6Body = new GP6Body();

// 		$gp6Head->Save($h);
		// 		$b[$gp6Body->name]['head_id'] = $h[$gp6Head->name]['id'];
		// 		$gp6Body->Save($b);

// 		try{
		// 			$h['GP6Head']['id'] = 1;
		// 			$b['GP6Body']['id'] = 1;

// 			$fak = new OTEFaktura();
		// 			$fak->setCislo('pokus');
		// 			$fak->loadArr([
		// 				array_merge($h, $b)
		// 			], false);

// 			$fak->build();
		// 			$fak->render();
		// 		}catch(FakturaException $e){
		// 			OBE_Cli::writeLn('OTE.: ' . $m['OTEMails']['ote_id'] . ' - fail: ' . $e->getMessage());
		// 			continue;
		// 		}
	}

// 	OTEGasDataPofGP6::cliOutBody($dt);

	OBE_Cli::writeLn('OTE.: ' . $m['OTEMails']['ote_id'] . ' - ok');
}

OBE_Cli::writeEnd();