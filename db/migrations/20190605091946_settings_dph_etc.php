<?php
use Phinx\Migration\AbstractMigration;

class SettingsDphEtc extends AbstractMigration{

	/**
	 * Change Method.
	 * Write your reversible migrations using this method.
	 * More information on writing migrations is available here:
	 * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
	 * The following commands can be used in this method and Phinx will
	 * automatically reverse them when rolling back:
	 * createTable
	 * renameTable
	 * addColumn
	 * addCustomColumn
	 * renameColumn
	 * addIndex
	 * addForeignKey
	 * Any other destructive changes will result in an error when trying to
	 * rollback the migration.
	 * Remember to call "create()" or "update()" and NOT "save()" when working
	 * with the Table class.
	 */
	public function change()
	{
		$t = $this->table('settings');

		$t->insert(
			[
				'key' => 'dph',
				'value' => 21,
				'type' => 'float',
				'require' => true,
				'description' => 'Sazba DPH',
				'group' => 'main'
			])->save();

		$t->insert(
			[
				'key' => 'dph_koef',
				'value' => 0.1736,
				'type' => 'float',
				'require' => true,
				'description' => 'Koeficient DPH',
				'group' => 'main'
			])->save();

		$t->insert(
			[
				'key' => 'dan_z_pln',
				'value' => 30.60,
				'type' => 'float',
				'require' => true,
				'description' => 'Daň z plynu (%)',
				'group' => 'main'
			])->save();

		$t->insert(
			[
				'key' => 'faktury_cisl',
				'value' => '21PPNNNN',
				'type' => 'string',
				'require' => true,
				'description' => 'Číselník faktur',
				'group' => 'cisl'
			])->save();
		$t->insert(
			[
				'key' => 'fakskups_cisl',
				'value' => '555NNNNNNN',
				'type' => 'string',
				'require' => true,
				'description' => 'Číselník fakturačních skupin',
				'group' => 'cisl'
			])->save();
		$t->insert(
			[
				'key' => 'doklady_cisl',
				'value' => '5YYYYNNNNN',
				'type' => 'string',
				'require' => true,
				'description' => 'Číselník dokladů',
				'group' => 'cisl'
			])->save();
	}
}
