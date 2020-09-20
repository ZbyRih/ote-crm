<?php


// CREATE TABLE `tx_pohyb` (
// 		`id` BIGINT(20) DEFAULT NULL,
// 		`when` DATETIME DEFAULT NULL,
//		`way` ENUM ('in', 'out')
//		`typ` ENUM ('prevod', 'hotove')
// 		`castka` FLOAT(10,2) DEFAULT '0.00',
// ) ENGINE=INNODB DEFAULT CHARSET=utf8

class MPohyb extends ModelClass{

	var $name = 'Pohyb';

	var $table = 'tx_pohyb';

	var $primaryKey = 'id';

	var $rows = [
		'id',
		'when',
		'way',
		'typ',
		'castka',
		'typ'
	];
}