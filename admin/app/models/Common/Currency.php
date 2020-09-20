<?php

class MCurrency extends ModelClass{
	var $name = 'Currency';
	var $table = 'es_currencys';
	var $primaryKey = 'currencyid';
	var $rows = ['currencyid', 'shortcut', 'name', 'crowns_per_cur', 'amount', 'bot_lim', 'top_lim', 'active', 'lang_codes', 'price_postfix', 'num_clear_decs', 'num_calcs_decs', 'currency', 'billing'];
	var $defaultVals = ['active' => 1];
	var $associatedModels = [
		  'MEntity' => [
			  'type' => 'belongsTo'
		]
	];
};