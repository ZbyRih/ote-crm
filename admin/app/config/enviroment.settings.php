<?php

class EnviromentConfig{

	static $config = [
		'subdomainlocalhost' => [
			'url' => 'http://subdomain.localhost/',
			'host' => 'localhost',
			'db' => [
				'name' => '',
				'pass' => '',
				'user' => '',
				'server' => '127.0.0.1:3306'
			],
			'demo' => false,
			'debug' => true,
			'mailSimple' => true,
			'mail-error' => false,
			'less' => true,
			'debug-bar' => true,
			'move-mail-server' => false,
			'reset-mails-settings' => true
		]
	];

	static $alias = [
		'localhost' => 'subdomainlocalhost'
	];

	static $global = [
		'obe' => [
			'srcPath' => IMG_REL . 'upload/attachments/',
			'thumbnailsPath' => IMG_REL . 'miniatures/',
			'remote' => false
		],
		'thumbnailQuality' => 95,
		'icons' => 'exticons/',
		'varloader_configuration' => [
			'table' => 'obe_vars',
			'data_row' => 'data',
			'id_row' => 'name'
		],
		'crmPPriceWDPH' => true,
		'crmUpdateStock' => true,
		'vc_replace' => false,
		'dphDeprecate' => false,
		'mailLimit' => true,
		'mailHourLimit' => 100,
		'mailReserve' => 20,
		'wholesaler' => false,
		'autoGenerateShortProductDesc' => false
	];
}