<?php

class AppConfAdmin{

	static $config = [
		'name' => 'backend',
		'title' => 'OTE CRM',
		'debug' => false,
		'logFile' => 'log/backend-log.txt',
		'load_vars' => [
			'front' => 'front',
			'menu' => 'menu',
			'curr_rates' => 'curr_rates'
		],
		'dynamic_vars' => [
			'theme' => [
				'order' => [
					OBE_DynVar::GET,
					OBE_DynVar::SES
				],
				'req' => [
					'Standartní' => 'standart',
					'Růžová' => 'viol'
				]
			],
			'fckeditor' => [
				'key' => 'fck_oc',
				'order' => [
					OBE_DynVar::GET,
					OBE_DynVar::SES
				],
				'req' => [
					'on' => 'on',
					'off' => 'off'
				]
			]
		],
		'certs_dir' => '/config/certs',
		'ote-conf' => [
			'dir-root' => './app/data/ote',
			'dir-emails-backup' => './app/data/ote/emails',
			'dir-emails-undecrypted' => './app/data/ote/emails-undecrypted',
			'dir-emails-other' => './app/data/ote/others',
			'dir-ote-messages' => './app/data/ote/ote-xml',
			'dir-xml-unknown' => './app/data/ote/ukn-xml',
			'box-readed' => 'Precteno',
			'box-others' => 'Ostatni'
		],

		'banka-conf' => [
			'dir-root' => './app/data/banka',
			'dir-emails-platby' => './app/data/banka/platby',
			'dir-emails-platby-ostatni' => './app/data/banka/platby-ostatni',
			'dir-emails-ostatni' => './app/data/banka/ostatni',
			'box-incomes' => 'Platby',
			'box-incomes-others' => 'PlatbyJine',
			'box-others' => 'Ostatni'
		],
		'faktury' => [
			'save-dir' => './data/faktury'
		],
		'coolUrl' => false,
		'user_admin' => true,
		'images_dont_upscale' => true,
		'prices' => [
			'decimals' => 2,
			'without-decs' => '00'
		],
		'price-decimals' => 2,
		'price-without-decs' => '00',
		'img-preview' => [
			'list' => [
				100,
				100
			],
			'form' => [
				200,
				200
			]
		],
		'list_preview_size' => [
			100,
			100
		],
		'form_preview_size' => [
			200,
			200
		],
		'themes' => [
			0 => 'modrá',
			1 => 'růžová'
		],
		'style_imgs_path' => 'img/',
		'less' => [
			'cache' => '/less',
			'files' => []
		]
	];
}