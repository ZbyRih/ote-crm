<?php
namespace App\Models\Storages;

use App\Extensions\Abstracts\CacheStorage;
use App\Extensions\App\Cache;
use App\Models\Tables\SettingsTable;

/**
 *
 * @property-read array $indexed
 */
class SettingsCacheStorage extends CacheStorage{

	/** @var SettingsTable */
	private $tbl;

	static $dependencies = 'settings';

	public function __construct(
		SettingsTable $tbl,
		Cache $cache)
	{
		$this->tbl = $tbl;
		parent::__construct($cache, self::$dependencies);
	}

	protected function fallback()
	{
		return [
			'indexed' => collection($this->tbl->all())->indexBy('key')
				->extract('value')
				->toArray()
		];
	}
}