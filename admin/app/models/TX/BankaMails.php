<?php


// CREATE TABLE `tx_mails_banka` (
// 		`mail_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
// 		`message_id` VARCHAR(255) DEFAULT NULL,
// 		`mail_dt` DATETIME DEFAULT NULL,
// 		`mail` TEXT,
// 		PRIMARY KEY  (`mail_id`)
// ) ENGINE=INNODB DEFAULT CHARSET=utf8

class MBankaMails extends ModelClass{

	var $name = 'BMails';

	var $table = 'tx_mails_banka';

	var $primaryKey = 'mail_id';

	var $rows = [
		'mail_id',
		'message_id',
		'mail_dt',
		'mail'
	];
}
