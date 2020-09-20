<?php
use Phinx\Migration\AbstractMigration;

class CiselnikyTypyPlateb extends AbstractMigration{

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
		$GROUP = 'typy_pohybu';

		$this->table('ciselniky_skupiny')
			->insert([
			[
				'nazev' => $GROUP
			]
		])
			->save();

		$this->table('ciselniky')
			->insert(
			[
				[
					'group' => $GROUP,
					'nazev' => 'Úvěr',
					'value' => 'u',
					'value2' => 'Splátka úvěru;Čerpání úvěru'
				],
				[
					'group' => $GROUP,
					'nazev' => 'Hotovost',
					'value' => 'h',
					'value2' => 'Výběr hotovosti'
				],
				[
					'group' => $GROUP,
					'nazev' => 'Inkaso',
					'value' => 'i',
					'value2' => 'Odchozí inkasní platba;Za vedení účtu, výpisy a transakce'
				],
				[
					'group' => $GROUP,
					'nazev' => 'Ostatní',
					'value' => 'o',
					'value2' => ''
				],
				[
					'group' => $GROUP,
					'nazev' => 'Bilance',
					'value' => 'b',
					'value2' => ''
				],
				[
					'group' => $GROUP,
					'nazev' => 'Faktura',
					'value' => 'f',
					'value2' => ''
				],
				[
					'group' => $GROUP,
					'nazev' => 'Záloha',
					'value' => 'z',
					'value2' => ''
				]
			])
			->save();
	}
}
