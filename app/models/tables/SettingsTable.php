<?php

namespace App\Models\Tables;

use App\Extensions\Abstracts\Table;

class SettingsTable extends Table{

	protected $table = 'settings';

	protected $pk = 'key';
}