<?php
use Cake\Collection\Collection;

require_once 'bootstrap.php';

// require_once 'mocks/platby-spojeni-mock.php';

OBE_Cli::writeBr('');
OBE_Cli::writeBr('	begin ->>');
OBE_Cli::writeBr('');

$a = [
	[
		'H' => [
			'name' => 'prd',
			'id' => 'flus'
		]
	],
	[
		'H' => [
			'name' => 'haf',
			'id' => 'kus'
		]
	]
];

$a = [
	[
		'name' => 'prd',
		'id' => 'flus'
	],
	[
		'name' => 'haf',
		'id' => 'kus'
	]
];

$c = new Collection($a);

var_dump($c->firstMatch([
	'name' => 'haf'
]));
// var_dump($c->indexBy('H.id')->toArray());

OBE_Cli::writeBr('	<<- end');