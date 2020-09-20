<?php

namespace App\Models\Tables;

use Nette\Database\Conventions\StaticConventions;

class AppConventions extends StaticConventions{

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nette\Database\Conventions\StaticConventions::getPrimary()
	 */
	public function getPrimary(
		$table)
	{
		if($table == 'tx_platby'){
			return 'platba_id';
		}

		return parent::getPrimary($table);
	}
}