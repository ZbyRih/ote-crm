<?php
use Phinx\Migration\AbstractMigration;

class DropTables extends AbstractMigration{

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
	public function change(){
		$this->execute('DELETE FROM obe_entitys WHERE entityid < 1819');
		$this->execute('SET FOREIGN_KEY_CHECKS=0');

		$this->table('obe_tagresvlink')
			->drop()
			->save();
		$this->table('es_usageunits')
			->drop()
			->save();
		$this->table('es_pricebooks')
			->drop()
			->save();
		$this->table('es_invoices')
			->drop()
			->save();
		$this->table('es_invoice_status')
			->drop()
			->save();
		$this->table('es_invoicenums')
			->drop()
			->save();
		$this->table('es_counters')
			->drop()
			->save();
		$this->table('es_currencys')
			->drop()
			->save();
		$this->table('obe_entity2entity')
			->drop()
			->save();
		$this->table('obe_entity_tags_2_entity')
			->drop()
			->save();
		$this->table('obe_documents')
			->drop()
			->save();
		$this->table('obe_functions')
			->drop()
			->save();
		$this->table('obe_galerys')
			->drop()
			->save();
		$this->table('obe_groups')
			->drop()
			->save();
		$this->table('obe_mutations')
			->drop()
			->save();
		$this->table('obe_settings')
			->drop()
			->save();
		$this->table('obe_tags')
			->drop()
			->save();
		$this->table('obe_system')
			->drop()
			->save();
		$this->table('cron_mails_head')
			->drop()
			->save();
		$this->table('cron_mails')
			->drop()
			->save();
		$this->table('obe_modules')
			->drop()
			->save();
		$this->table('obe_languages')
			->drop()
			->save();

		$this->table('user')
			->drop()
			->save();

		$this->execute('SET FOREIGN_KEY_CHECKS=1');
	}
}
