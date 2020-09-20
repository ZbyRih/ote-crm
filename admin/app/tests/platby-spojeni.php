<?php

require_once 'bootstrap.php';

require_once 'mocks/platby-spojeni-mock.php';

OBE_Cli::writeBegin();

OBE_App::$db->startTransaction();

MockVS::$vs = 123456789;

MockModel::addLoader(
	'MZalohy',
	function (){
		return [
			[
				'Zalohy' => [
					'zaloha_id' => 1,
					'uhr' => 0,
					'preplatek' => -100,
					'vyse' => 900,
					'uhrazeno' => 0,
					'vs' => MockVS::$vs,
					'od' => '2017-10-01',
					'klient_id' => 1
				]
			],
			[
				'Zalohy' => [
					'zaloha_id' => 2,
					'uhr' => 0,
					'preplatek' => 0,
					'vyse' => 500,
					'uhrazeno' => 0,
					'vs' => MockVS::$vs,
					'od' => '2017-11-01',
					'klient_id' => 1
				]
			],
			[
				'Zalohy' => [
					'zaloha_id' => 3,
					'uhr' => 0,
					'preplatek' => 0,
					'vyse' => 700,
					'uhrazeno' => 0,
					'vs' => MockVS::$vs,
					'od' => '2017-12-01',
					'klient_id' => 1
				]
			],
			[
				'Zalohy' => [
					'zaloha_id' => 4,
					'uhr' => 0,
					'preplatek' => 0,
					'vyse' => 700,
					'uhrazeno' => 0,
					'vs' => MockVS::$vs,
					'od' => '2018-01-01',
					'klient_id' => 1
				]
			],
			[
				'Zalohy' => [
					'zaloha_id' => 5,
					'uhr' => 0,
					'preplatek' => 0,
					'vyse' => 700,
					'uhrazeno' => 0,
					'vs' => MockVS::$vs,
					'od' => '2018-02-01',
					'klient_id' => 1
				]
			]
		];
	});

MockModel::addLoader(
	'MPlatby',
	function (){
		return [
			[
				'Platba' => [
					'platba_id' => 1,
					'platba' => 700,
					'when' => '2017-10-11',
					'vs' => MockVS::$vs
				]
			],
			[
				'Platba' => [
					'platba_id' => 2,
					'platba' => 700,
					'when' => '2017-11-09',
					'vs' => MockVS::$vs
				]
			],
			[
				'Platba' => [
					'platba_id' => 3,
					'platba' => 800,
					'when' => '2017-12-13',
					'vs' => MockVS::$vs
				]
			],
			[
				'Platba' => [
					'platba_id' => 4,
					'platba' => 800,
					'when' => '2018-01-11',
					'vs' => MockVS::$vs
				]
			],
			[
				'Platba' => [
					'platba_id' => 5,
					'platba' => 800,
					'when' => '2018-02-11',
					'vs' => MockVS::$vs
				]
			]
		];
	});

MockModel::addLoader(
	'MContacts',
	function (){
		return [
			[
				1 => [
					'Client' => [
						'klient_id' => 1
					],
					'ContactDetails' => [
						'firstname' => 'Krestni',
						'lastname' => 'Zkouska'
					]
				]
			]
		];
	});

$y = 2018;
$ppz = new PlatbyParZalohy($y);

$vss = [
	MockVS::$vs
];

foreach($vss as $vs){
	$ppz->load($vs);
	$ppz->projit();
	$ppz->getMidResult();
	$ppz->save();
}

OBE_Cli::writeEnd();

OBE_App::$db->finishTransaction(false);